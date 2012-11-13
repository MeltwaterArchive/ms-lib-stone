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
 * @package   Stone
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\TimeLib;

use DateTimeZone;

/**
 * Helper class for working with times
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class DateTime extends \DateTime
{
	public function __construct($startTime, $offsetString = 0, $timezone = 'UTC') {
		// the time that we will (eventually) store
		parent::__construct();
		$this->setTimezone(new DateTimeZone('UTC'));
		$this->setTimestamp($startTime);

		// are we adding or subtracting?
		$sub = false;
		if ($offsetString{0} == '-') {
			$sub = true;
			$offsetString = substr($offsetString, 1);
		}

		// apply the offset string
		$interval = new DateInterval($offsetString);
		if ($sub) {
			$this->sub($interval);
		} else {
			$this->add($interval);
		}

		// all done
	}

	public function setOffset($offsetString)
	{
		// are we adding or subtracting?
	}

	public function getDateTime()
	{
		return date('Y-m-d H:i:s', $this->getTimestamp());
	}

	public function getDateTimeSinceMidnight()
	{
		$return = $this->getDateTime();
		$return = substr($return, 0, 10) . " 00:00:00 " . $this->getTimezoneName();

		return $return;
	}

	public function getDate()
	{
		return date('Y-m-d', $this->getTimestamp());
	}

	public function getSecondsSinceStartOfMonth()
	{
		$monthStart = strtotime(date('Y-m-01 00:00:00', $this->getTimestamp));
		$now = time();

		return $now - $monthStart;
	}

	public function getTimezoneName()
	{
		return $this->getTimezone()->getName();
	}
}