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

namespace DataSift\Stone\LogLib;

/**
 * A static proxy around the underlying logger
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class Log
{
    private static $processName;
    private static $mask;
    private static $writer;

    // the list of logging levels
    const LOG_EMERGENCY = 1;
    const LOG_ALERT = 2;
    const LOG_CRITICAL = 3;
    const LOG_ERROR = 4;
    const LOG_WARNING = 5;
    const LOG_NOTICE = 6;
    const LOG_INFO = 7;
    const LOG_DEBUG = 8;
    const LOG_TRACE = 9;

    static public function init($processName, $config)
    {
        // what will we call ourselves?
        self::setProcessName($processName);

        // what log levels are allowed?
        self::setLogMaskFromConfig($config->levels);

        // setup our writer
        self::initWriter($config->writer);

        // tell the world that we're alive
        self::write(self::LOG_DEBUG, "logger initialised");

        // all done
    }

    /**
     * write a log message of some kind
     *
     * @param  int $logLevel
     *         what kind of message is this? (one of the LOG_* constants)
     * @param  string $errMessage
     *         the text to log
     * @param  Exception $cause
     *         the underlying cause / exception that caused this log event
     * @return void
     */
    static public function write($logLevel, $errMessage = null, $cause = null)
    {
        // do we really want to log this?
        if (!isset(self::$mask[$logLevel]) || !self::$mask[$logLevel])
        {
            // no - so let's save some CPU
            return;
        }

        // yes we do
        self::$writer->write($logLevel, $errMessage, $cause);
    }

    static public function trace($file, $line)
    {
        // is tracing enabled?
        if (!isset(self::$mask[self::LOG_TRACE]) || !self::$mask[self::LOG_TRACE])
        {
            // no, it is not
            return;
        }

        self::write(self::LOG_TRACE, "reached $file:$line");
    }
    /**
     * Set the list of log messages that we want to allow through
     * @param array(logLevel => boolean) $mask
     *        a list of the supported logLevels, and whether they are
     *        allowed or not
     */
    static protected function setLogMask($mask)
    {
        self::$mask = $mask;
    }

    /**
     * set our global logging mask using the values loaded from ConfigLib
     *
     * @param stdClass $config a list of the logging levels, and whether
     *        they are enabled or not
     */
    static protected function setLogMaskFromConfig($config)
    {
        $possibleMasks = array (
            "LOG_EMERGENCY" => self::LOG_EMERGENCY,
            "LOG_ALERT"     => self::LOG_ALERT,
            "LOG_CRITICAL"  => self::LOG_CRITICAL,
            "LOG_ERROR"     => self::LOG_ERROR,
            "LOG_WARNING"   => self::LOG_WARNING,
            "LOG_NOTICE"    => self::LOG_NOTICE,
            "LOG_INFO"      => self::LOG_INFO,
            "LOG_DEBUG"     => self::LOG_DEBUG,
            "LOG_TRACE"     => self::LOG_TRACE,
        );

        // convert the JSON-encoded object into an array
        $mask = array();
        foreach ($possibleMasks as $levelName => $logLevel)
        {
            $mask[$logLevel] = $config->$levelName;
        }

        // set our mask as the live mask
        self::setLogMask($mask);
    }

    /**
     * set the name of our process, which we'll use in (some of) our
     * log writers
     *
     * @param string $processName the name of the process
     */
    static protected function setProcessName($processName)
    {
        self::$processName = $processName;
    }

    /**
     * create our actual writer, and initialise it
     *
     * the writer is the class that we're acting as a proxy for
     *
     * @param string $writerName the name of the writer to load
     */
    static protected function initWriter($writerName)
    {
        // create the writer
        $writerClass = __NAMESPACE__ . '\\' . $writerName;
        if (!class_exists($writerClass))
        {
            throw new E5xx_BadLogWriter($writerClass);
        }

        self::$writer = new $writerClass;
        self::$writer->init(self::$processName, posix_getpid());
    }
}
