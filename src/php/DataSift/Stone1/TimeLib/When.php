<?php

/**
 * Stone1 - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone1\TimeLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\TimeLib;

/**
 * Helper class for describing the age of a time
 *
 * @category Libraries
 * @package  Stone1\TimeLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class When
{
    /**
     * singleton - cannot construct
     * @codeCoverageIgnore
     */
    private function __construct()
    {

    }

    /**
     * create a description of how old a timestamp is
     *
     * @param  int $when
     *         the timestamp to examine
     * @return string
     *         a description of how old the timestamp is
     */
    static public function age_asString($when)
    {
        $ageTime = time() - $when;

        // for things that have just happened
        if ($ageTime < 60)
        {
            return 'less than one minute';
        }

        // general case time - how old is this?
        $minutes = (int)($ageTime / 60)%60;
        $hours   = (int)($ageTime / 3600)%24;
        $days    = (int)($ageTime / 86400);

        $ranges = array
        (
            array ($days,     'day',    'days'),
            array ($hours,    'hour',   'hours'),
            array ($minutes,  'minute', 'minutes'),
        );

        $return = array();
        foreach ($ranges as $range)
        {
            self::expandTimeAge($return, $range[0], $range[1], $range[2]);
        }

        return join($return, ', ');
    }

    /**
     * helper method for expanding a single part of a date/time into a
     * description
     *
     * @param  array $return
     *         the array we add our results to
     * @param  int $count
     *         the number (of one of: days, hours, minutes) that we are examining
     * @param  string $single
     *         the correct description if $count == 1
     * @param  string $many
     *         the correct description if $count > 1
     * @return void
     */
    private static function expandTimeAge(&$return, $count, $single, $many)
    {
        if (count($return) && $count == 0)
        {
            return;
        }

        if ($count == 1)
        {
            $return[] = '1 ' . $single;
            return;
        }

        if ($count > 1)
        {
            $return[] = $count . ' ' . $many;
        }
    }
}