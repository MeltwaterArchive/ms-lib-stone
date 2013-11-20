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
 * A rate limit
 *
 * @category Libraries
 * @package  Stone/RateLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://datasift.github.io/stone
 */

class RateLimit
{
    /**
     * this limit does not apply at all (ie, is unlimited)
     */
    const NO_LIMIT = 0;

    /**
     * this limit applies to data generators
     */
    const GENERATOR_LIMIT  = 1;

    /**
     * this limit applies to data consumers
     *
     * it is an alias for GENERATOR_LIMIT
     */
    const CONSUMER_LIMIT = 1;

    /**
     * this limit applies to data throughput
     */
    const THROUGHPUT_LIMIT = 2;

    /**
     * How many messages, or how much data (in bytes), per time interval?
     * @var integer
     */
    protected $unitAmountLimit = null;

    /**
     * What is the unit amount? messages? bytes? etc etc
     * @var string
     */
    protected $unitTypeLimit   = null;

    /**
     * How many seconds between rate limiting?
     * @var int
     */
    protected $timePeriodLimit = null;

    /**
     * What kind of rate are we? See _LIMIT constants
     * @var int
     */
    protected $rateType        = self::NO_LIMIT;

    /**
     * What do we need to divide by to convert the raw units into what the
     * user has asked for?
     * @var int
     */
    protected $rateMultiplier = 1;

    /**
     * Returns how much data we want to let through
     * @return int
     */
    public function getUnitAmountLimit()
    {
        return $this->unitAmountLimit;
    }

    /**
     * Sets how much data we want to let through
     * @param int $newLimit
     */
    public function setUnitAmountLimit($newLimit)
    {
        $this->unitAmountLimit = $newLimit;
    }

    /**
     * Returns what we are limiting, as a char
     * @return string
     */
    public function getUnitLimitType()
    {
        return $this->unitTypeLimit;
    }

    /**
     * Sets what we are limiting
     * @param string $newType
     */
    public function setUnitLimitType($newType)
    {
        $this->unitTypeLimit = $newType;
    }

    /**
     * Returns how frequently the limits apply, in seconds
     * @return int
     */
    public function getTimePeriodLimit()
    {
        return $this->timePeriodLimit;
    }

    /**
     * Sets how frequently the limits apply, in seconds
     * @param int $newLimt
     */
    public function setTimePeriodLimit($newLimit)
    {
        $this->timePeriodLimit = $newLimit;
    }

    /**
     * Returns the period we are limiting over
     * @return string
     */
    public function getTimePeriodType()
    {
        return $this->timePeriodType;
    }

    /**
     * Sets the period that we are limiting over
     * @param string $newType
     */
    public function setTimePeriodType($newType)
    {
        $this->timePeriodType = $newType;
    }

    /**
     * Returns what kind of rate we are. See self::*_LIMIT constants
     * @return int
     */
    public function getRateType()
    {
        return $this->rateType;
    }

    /**
     * Sets what kind of rate we are, as one of the self::*_LIMIT constants
     * @param int $newType
     */
    public function setRateType($newType)
    {
        $this->rateType = $newType;
    }

    /**
     * Are we a rate that applies to a data generator limiter?
     * @return boolean
     */
    public function isGeneratorRate()
    {
        if ($this->getRateType() == self::GENERATOR_LIMIT)
        {
            return true;
        }

        return false;
    }

    /**
     * Are we a rate that applies to a data consumer limiter?
     * @return boolean
     */
    public function isConsumerRate()
    {
        if ($this->getRateType() == self::CONSUMER_LIMIT)
        {
            return true;
        }

        return false;
    }

    /**
     * Are we a rate that applies to a throughput limiter?
     * @return type
     */
    public function isThroughputRate()
    {
        if ($this->getRateType() == self::THROUGHPUT_LIMIT)
        {
            return true;
        }

        return false;
    }

    /**
     * Returns the multiplier we used when working out the amount per second
     * that we want to see
     * @return int
     */
    public function getRateMultiplier()
    {
        return $this->rateMultiplier;
    }

    /**
     * Sets the multiplier we used when working out the amount per second
     * that we want to see
     * @param int $newMultipler
     */
    public function setRateMultiplier($newMultipler)
    {
        $this->rateMultiplier = $newMultipler;
    }

    /**
     * Returns the human-readable name of the unit we are rate-limiting by
     * @return string
     */
    public function getUnitName()
    {
        return $this->unitName;
    }

    /**
     * Sets the human-readable name of the unit we are rate-limiting by
     * @param string $newName
     */
    public function setUnitName($newName)
    {
        $this->unitName = $newName;
    }

    /**
     * handy little method to convert a human-readable time period into the
     * interval (in number of seconds) that we want to apply limits to
     *
     * @param string $period the time period (secs, mins, etc etc) being requested
     * @return int how frequently do we want to apply the limits? in seconds
     */
    protected function decodeTimePeriod($rateString, $periodString)
    {
        $timePeriods = array
        (
            'sec'   => 1,
            'min'   => 60,
            'qhour' => 60 * 15,
            'hour'  => 60 * 60,
            'day'   => 60 * 60 * 24,
            // anything longer than a day seems overkill for now?
        );

        // do we have a requested time period that we recognise?
        if (isset($timePeriods[$periodString]))
        {
            // yes we do
            return array($periodString, $timePeriods[$periodString]);
        }

        // if we get here, then we do not understand what has been required
        throw new E4xx_InvalidRateLimit($rateString, "unrecognised time period: '$periodString'", 400);
    }

    /**
     * handy little method to convert a human-readable size unit into the number
     * of units or bytes allowed per second
     *
     * @param string $unitString
     * @return int the number of units or bytes we want to limit to
     */
    protected function decodeUnit($rateString, $unitString)
    {
        $unitType     = substr($unitString, -1, 1);
        $unitAmount   = substr($unitString, 0, -1);

        // do we like what we have?
        switch ($unitType)
        {
            case 'u':
                // number of messages
                $rateType = self::GENERATOR_LIMIT;
                $unitMultiplier = 1;
                $unitName = 'messages';
                break;
            case 'k':
                // kiloBITs
                // convert to bytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 128;
                $unitName = 'kilobits';
                break;
            case 'K':
                // kilobytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 1024;
                $unitName = 'kilobytes';
                break;
            case 'm':
                // megaBITs
                // convert to bytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 128 * 1024;
                $unitName = 'megabits';
                break;
            case 'M':
                // megaBYTEs
                // convert to bytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 1024 * 1024;
                $unitName = 'megabytes';
                break;
            case 'g':
                // gigaBITs
                // convert to bytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 128 * 1024 * 1024;
                $unitName = 'gigabits';
                break;
            case 'G':
                // gigaBYTEs
                // convert to bytes
                $rateType = self::THROUGHPUT_LIMIT;
                $unitMultiplier = 1024 * 1024 * 1024;
                $unitName = 'gigabytes';
                break;

            default:
                // no we do not
                throw new E4xx_InvalidRateLimit($rateString, "the rate type '$unitType' is not recognised", 400);
        }

        return array($rateType, $unitMultiplier, $unitName, $unitType, $unitAmount);
    }

    /**
     * Set the rate we want to limit, from a human readable string:
     *
     * * Xu/<period> - X messages per time period
     * * Xk/<period> - X kilobits per time period
     * * Xm/<period> - X megabits per time period
     * * Xg/<period> - X gigabits per time period
     *
     * <period> is one of 'sec', 'min', 'hour', 'day'
     *
     * e.g.
     *
     * * 500u/sec - 500 messages a second
     * * 100m/sec - 100 megabits a second
     * * 8g/hour  - 8 gigabits (one gigabyte) an hour
     *
     * @param string $rateString
     */
    public function initFromString($rateString)
    {
        // special case - check for unlimited rate
        if ($rateString == 'unlimited') {
            $this->setRateType(self::NO_LIMIT);
            $this->setUnitAmountLimit(null);
            $this->setUnitName(null);
            $this->setTimePeriodLimit(null);
            $this->setTimePeriodType(null);

            // all done
            return;
        }

        // do we have something we can parse?
        $parts = explode('/', $rateString);
        if (count($parts) !== 2)
        {
            // no, we do not
            throw new E4xx_InvalidRateLimit($rateString, "must be in the format '<amount><unit>/<time>'");
        }

        // break it down
        $unitString   = $parts[0];
        $periodString = $parts[1];

        list($rateType, $unitMultiplier, $unitName, $unitType, $unitAmount) = $this->decodeUnit($rateString, $unitString);
        list($periodType, $periodSecs) = $this->decodeTimePeriod($rateString, $periodString);

        // if we get here, we like the rate
        $this->setUnitAmountLimit($unitAmount * $unitMultiplier);
        $this->setUnitLimitType($unitType);
        $this->setRateType($rateType);
        $this->setRateMultiplier($unitMultiplier);
        $this->setUnitName($unitName);
        $this->setTimePeriodLimit($periodSecs);
        $this->setTimePeriodType($periodType);
    }

    /**
     * Are we, in fact, meant to proceed as fast as possible?
     *
     * @return boolean
     */
    public function isUnlimited()
    {
        // are we a type of limit at all?
        if ($this->rateType == self::NO_LIMIT) {
            return true;
        }

        // do we have a unit to limit us by?
        if ($this->unitAmountLimit === null)
        {
            return true;
        }

        // if we get here, then we cannot be unlimited
        return false;
    }
}