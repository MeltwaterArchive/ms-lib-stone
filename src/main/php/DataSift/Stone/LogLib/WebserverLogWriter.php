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

/**
 * A static proxy around the underlying logger
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\LogLib;

class LogWebserverLog extends BaseLogger
{
    protected $prefixes = array (
        Log::LOG_EMERGENCY => "1: EMERGENCY: ",
        Log::LOG_ALERT     => "2: ALERT: ",
        Log::LOG_CRITICAL  => "3: CRITICAL: ",
        Log::LOG_ERROR     => "4: ERROR: ",
        Log::LOG_WARNING   => "5: WARNING: ",
        Log::LOG_NOTICE    => "6: NOTICE: ",
        Log::LOG_INFO      => "7: INFO: ",
        Log::LOG_DEBUG     => "8: DEBUG: ",
        Log::LOG_TRACE     => "9: TRACE: ",
    );

    public function write($logLevel, $message, $cause = null)
    {
        // error_log('[' . $this->processName . ':' . $this->pid . '] ' . $this->prefixes[$logLevel] . $message);
        error_log($this->prefixes[$logLevel] . $message);
    }
}