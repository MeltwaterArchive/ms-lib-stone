<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Libraries
 * @package  Stone/RateLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://datasift.github.io/stone
 */

namespace DataSift\Stone\RateLib;

/**
 * Common base class for all types of limiter
 *
 * @category Libraries
 * @package  Stone/RateLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://datasift.github.io/stone
 */

abstract class Limiter
{
    /**
     * The rate limit that we want to apply
     * @var RateLimit
     */
    protected $rateLimit = null;

    /**
     * When was the last time this limiter was called?
     *
     * @var int UNIX timestamp
     */
    protected $lastSeenSeconds = 0;

    /**
     * how many units a second are we allowed to see before we need to throttle
     * whatever we are doing?
     *
     * this may be the number of requests or responses (if we are a ConsumerLimit),
     * the number of data units created (if we are aGeneratorLimit) or the number
     * of bytes read or written (if we are a ThroughputLimit)
     *
     * @var int
     */
    protected $targetUnitsPerSecond = 0;

    /**
     * how many units have we achieved so far this second?
     *
     * this may be the number of requests or responses (if we are a ConsumerLimit),
     * the number of data units created (if we are aGeneratorLimit) or the number
     * of bytes read or written (if we are a ThroughputLimit)
     *
     * @var int
     */
    protected $actualUnitsPerSecond = 0;

    /**
     * what is the human-readable name of the units we are measuring / limiting?
     * @var string
     */
    protected $unitsName = null;

    /**
     * Constructor. Do not override in child classes, please, without good cause
     */
    final public function __construct()
    {
        $this->setUnlimited();
    }

    /**
     * Set the rate limit that will apply to this limiter
     * @param RateLimit $rateLimit
     */
    public function setRateLimit(RateLimit $rateLimit)
    {
        $this->rateLimit = $rateLimit;
        $this->calculateUnitsPerSecond();
    }

    /**
     * Are we actually unlimited?
     * @return boolean
     */
    public function isUnlimited()
    {
        return $this->rateLimit->isUnlimited();
    }

    /**
     * set our rate to be unlimited
     */
    public function setUnlimited()
    {
        $this->setRateLimit(new UnlimitedRate());
    }

    /**
     * How many units a second are we limiting to?
     *
     * @return int
     */
    public function getTargetUnitsPerSecond()
    {
        return $this->targetUnitsPerSecond;
    }

    /**
     * Wait until the start of the next second, if we have reached or exceeded
     * our rate limit.
     *
     * Returns immediately if we have not yet exceeded our rate limit
     *
     * @param int $payload
     * @param int $limitFactor
     * @return boolean true if we have gone over the 1 second boundary, false
     *         otherwise
     */
    public function wait($payload)
    {
        // do we need to wait at all?
        if ($this->rateLimit->isUnlimited())
        {
            // no we do not
            return false;
        }

        // update the counters to consider the latest payload
        $this->incrementUnitsSeen($payload);

        // how are we doing, ratelimit-wise?
        $nowParts = explode(' ', microtime());
        if ($this->lastSeenSeconds < $nowParts[1])
        {
            // we have gone over the 1 second boundary
            $this->lastSeenSeconds = $nowParts[1];
            $this->actualUnitsPerSecond = 0;

            return true;
        }
        else if ($this->actualUnitsPerSecond >= $this->targetUnitsPerSecond)
        {
            // we have reached our per-second limit
            //
            // sleep until the start of the next second
            $microtime = $nowParts[0] * 1000;

            $timeToSleep = 1001 - ($microtime % 1000);

            // echo "{$this->actualUnitsPerSecond}u/sec reached at $microtime; sleeping for $timeToSleep microseconds\n";
            usleep($timeToSleep * 1000);

            $nowParts = explode(' ', microtime());
            $this->lastSeenSeconds = $nowParts[1];
            $this->actualUnitsPerSecond = 0;

            return true;
        }

        return false;
    }

    /**
     * Work out what our target units per second will be
     */
    protected function calculateUnitsPerSecond()
    {
        // special case
        if ($this->rateLimit->isUnlimited())
        {
            $this->targetUnitsPerSecond = 0;
            return;
        }

        // this is the amount of bytes or messages in the required time period
        $unitLimit = (float)$this->rateLimit->getUnitAmountLimit();
        // this is the time period in seconds
        $timePeriod = (float)$this->rateLimit->getTimePeriodLimit();

        $this->targetUnitsPerSecond = $unitLimit / $timePeriod;
        $this->unitsName = $this->rateLimit->getUnitName();
        $this->unitsMultiplier = $this->rateLimit->getRateMultiplier();
    }

    /**
     * Keep track of how many units we have seen so far this second
     */
    abstract protected function incrementUnitsSeen($payload);

    /**
     * Helper method - output how many units we have seen so far
     *
     * @param string $unitsName
     * @param int $unitsMultiplier
     */
    public function reportProgress($unitsName, $unitsMultiplier)
    {
        $percentageRate = (float)$this->actualUnitsPerSecond / (float)$this->targetUnitsPerSecond;
        $reportedUnits = round($this->actualUnitsPerSecond / $unitsMultiplier);

        // report back on how we are doing
        echo '[' . date('Y-m-d H:i:s') . "] currently generating " . $reportedUnits . " $unitsName / sec, which is " . round($percentageRate * 100) . "% of target rate\n";
    }
}