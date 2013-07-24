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
 * @category  Libraries
 * @package   Stone/LogLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\LogLib;

use stdClass;

/**
 * A static proxy around the underlying logger
 *
 * @category  Libraries
 * @package   Stone/LogLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class Log
{
    /**
     * a list of enabled log levels
     *
     * this allows the user to disable any log levels that they wish, for
     * example disabling debugging or trace messages to make the logs
     * easier to read
     *
     * @var array
     */
    private static $mask;

    /**
     * our log writer
     * @var LogWriter
     */
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

    private static $namesToLevels = array(
        "EMERGENCY" => self::LOG_EMERGENCY,
        "ALERT"     => self::LOG_ALERT,
        "CRITICAL"  => self::LOG_CRITICAL,
        "ERROR"     => self::LOG_ERROR,
        "WARNING"   => self::LOG_WARNING,
        "NOTICE"    => self::LOG_NOTICE,
        "INFO"      => self::LOG_INFO,
        "DEBUG"     => self::LOG_DEBUG,
        "TRACE"     => self::LOG_TRACE,
    );

    private static $levelsToNames = array(
        self::LOG_EMERGENCY => "EMERGENCY",
        self::LOG_ALERT     => "ALERT",
        self::LOG_CRITICAL  => "CRITICAL",
        self::LOG_ERROR     => "ERROR",
        self::LOG_WARNING   => "WARNING",
        self::LOG_NOTICE    => "NOTICE",
        self::LOG_INFO      => "INFO",
        self::LOG_DEBUG     => "DEBUG",
        self::LOG_TRACE     => "TRACE",
    );

    /**
     * initialise the logging engine
     *
     * @param  string $processName
     *         the name of the process that is writing log messages
     * @param  stdClass $config
     *         the config for our logger
     * @return void
     */
    static public function init($processName, stdClass $config)
    {
        // what log levels are allowed?
        self::setLogMaskFromConfig($config->levels);

        // setup our writer
        self::initWriter($config->writer, $processName);

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
     * @param  array $context
     *         key/value pairs to substitute in the log message
     * @param  Exception $cause
     *         the underlying cause / exception that caused this log event
     * @return void
     */
    static public function write($logLevel, $errMessage = null, $context = array(), $cause = null)
    {
        // do we really want to log this?
        if (!isset(self::$mask[$logLevel]) || !self::$mask[$logLevel])
        {
            // no - so let's save some CPU
            return;
        }

        // yes we do
        //
        // expand the message to include the context details
        foreach ($context as $key => $value)
        {
            $errMessage = str_replace('{' . $key . '}', $value, $errMessage);
        }

        // send the final message to the log writer
        self::$writer->write($logLevel, $errMessage, $cause);
    }

    /**
     * shorthand for writing out a trace method
     *
     * @param  string $file
     *         normally the __FILE__ pseudo-constant
     * @param  int $line
     *         normally the __LINE__ pseudo-constant
     * @return void
     */
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
        // convert the JSON-encoded object into an array
        $mask = array();
        foreach (self::$namesToLevels as $levelName => $logLevel)
        {
            // try it without the LOG_ prefix first
            if (isset($config->$levelName)) {
                $mask[$logLevel] = $config->$levelName;
            }
            // fall back to the older LOG_ prefixed names
            else if (isset($config->{'LOG_' . $levelName})) {
                $mask[$logLevel] = $config->{'LOG_' . $levelName};
            }
            // if no match, assume the level is disabled
            else {
                $mask[$logLevel] = false;
            }
        }

        // set our mask as the live mask
        self::setLogMask($mask);
    }

    /**
     * create our actual writer, and initialise it
     *
     * the writer is the class that we're acting as a proxy for
     *
     * @param string $writerName
     *        the name of the writer to load
     * @param string $processName
     *        the name of the process writing out log messages
     */
    static protected function initWriter($writerName, $processName)
    {
        // create the writer
        $writerClass = __NAMESPACE__ . '\\' . $writerName;
        if (!class_exists($writerClass))
        {
            throw new E5xx_BadLogWriter($writerClass);
        }

        self::$writer = new $writerClass;
        self::$writer->init($processName, posix_getpid());
    }

    /**
     * convert a human-readable name into an internal log level
     *
     * @param  string $name
     *         the human-readable name of the log level
     * @return int
     *         the log-level to use for Log::Log
     */
    static public function getLevelFromName($name)
    {
        if (isset(self::$namesToLevels[$name]))
        {
            return self::$namesToLevels[$name];
        }

        return null;
    }

    /**
     * generate a logging mask given a minimum level to log against
     *
     * @param  string|int $level
     *         the minimum log level to enable
     * @return stdClass
     *         the logging mask to use with Log::init()
     */
    static public function getMaskForMinLevel($level)
    {
        // our return value
        $mask = array();

        // convert from string to int if required
        if (is_string($level))
        {
            $level = static::getLevelFromName($level);
        }

        // a simple way to fill out the mask
        for ($i = self::LOG_EMERGENCY; $i <= self::LOG_TRACE; $i++)
        {
            if ($i <= $level)
            {
                $mask[self::$levelsToNames[$i]] = true;
            }
            else
            {
                $mask[self::$levelsToNames[$i]] = false;
            }
        }

        // all done
        return (object)$mask;
    }
}
