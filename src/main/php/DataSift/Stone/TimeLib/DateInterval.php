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

/**
 * Helper class for working with date/time intervals
 *
 * @category Libraries
 * @package  Stone\TimeLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class DateInterval extends \DateInterval
{
	/**
	 * convert the date/time interval into minutes
	 *
	 * @return int
	 *         the number of minutes that this date/time interval covers
	 */
	public function getTotalMinutes()
	{
		return $this->getTotalSeconds() / 60;
	}

	/**
	 * convert the date/time interval into seconds
	 *
	 * if the date interval contains months and years, the following
	 * (unreliable!) assumptions are applied:
	 *
	 * 1. there are 30 days in a month
	 * 2. there are 365 days in a year
	 *
	 * you are perfectly safe as long as your date interval only contains
	 * days, hours, minutes and seconds
	 *
	 * @return int
	 *         the number of seconds that this date/time interval covers
	 */
	public function getTotalSeconds()
	{
		// get total days
		return ($this->y * 365 * 24 * 60 * 60) +
               ($this->m * 30 * 24 * 60 * 60) +
               ($this->d * 24 * 60 * 60) +
               ($this->h * 60 *60) +
               ($this->i * 60) +
                $this->s;
	}
}
