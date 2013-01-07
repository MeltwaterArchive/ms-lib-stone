<?php

/**
 * Stone - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone\TimeLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\TimeLib;

use DateTimeZone;

/**
 * Helper class for working with dates and times
 *
 * @category Libraries
 * @package  Stone\TimeLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class DateTime extends \DateTime
{
    /**
     * constructor
     *
     * Takes an optional date/time offset to apply
     *
     * @param int $startTime
     *        the UNIX timestamp to apply (default is time())
     * @param string $offsetString
     *        any date/time offset to apply (must be valid DateInterval string)
     * @param string $timezone
     *        what timezone is $startTime in (default is UTC)
     */
    public function __construct($startTime = null, $offsetString = "P0D", $timezone = 'UTC')
    {
        // call the parent constructor
        parent::__construct();

        // do we have a time to apply?
        if ($startTime === null)
        {
            $startTime = time();
        }

        // apply the starting time
        $this->setTimezone(new DateTimeZone($timezone));
        $this->setTimestamp($startTime);

        // apply the offset
        $this->applyOffset($offsetString);

        // all done
    }

    /**
     * apply a date/time interval to this datetime
     *
     * @param  string $offsetString
     *         The dateinterval format string to apply.
     *         Can start with '-' if you want to subtract time.
     * @return void
     */
    public function applyOffset($offsetString)
    {
        // are we adding or subtracting?
        $sub = false;
        if ($offsetString{0} == '-') {
            // we are subtracting
            $sub = true;

            // remove the '-' from the front of the string, otherwise
            // Derick's DateTime::__construct() will complain
            $offsetString = substr($offsetString, 1);
        }

        // calculate the date/time interval
        $interval = new DateInterval($offsetString);

        // apply the offset string
        if ($sub) {
            $this->sub($interval);
        } else {
            $this->add($interval);
        }

        // all done
    }

    /**
     * get the date in the 'Y-m-d' format
     *
     * @return string
     */
    public function getDate()
    {
        return date('Y-m-d', $this->getTimestamp());
    }

    /**
     * return the date/time in the common 'Y-m-d H:i:s' format
     *
     * @return string
     */
    public function getDateTime()
    {
        return date('Y-m-d H:i:s', $this->getTimestamp());
    }

    /**
     * return a valid date/time string with the time set to midnight
     *
     * @return string
     */
    public function getDateTimeAtMidnight()
    {
        $return = $this->getDateTime();
        $return = substr($return, 0, 10) . " 00:00:00 " . $this->getTimezoneName();

        return $return;
    }

    /**
     * returns the number of seconds from this DateTime and the start of the month
     *
     * @return int
     */
    public function getSecondsSinceStartOfMonth()
    {
        $monthStartString = date('Y-m-01 00:00:00', $this->getTimestamp());
        $monthStartTime   = strtotime($monthStartString);
        $now = $this->getTimestamp();

        return $now - $monthStartTime;
    }

    /**
     * what timezone is this DateTime in?
     *
     * @return string
     */
    public function getTimezoneName()
    {
        return $this->getTimezone()->getName();
    }
}