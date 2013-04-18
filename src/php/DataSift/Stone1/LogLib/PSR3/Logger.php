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
 * @package   Stone1
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\LogLib;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * A static proxy around the underlying logger
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class PSR3_Logger implements LoggerInterface
{
	/**
	 * map PSR-3 log levels to ours
	 */
	private $logLevelMap = array(
		LogLevel::EMERGENCY => Log::LOG_EMERGENCY,
		LogLevel::ALERT 	=> Log::LOG_ALERT,
		LogLevel::CRITICAL  => Log::LOG_CRITICAL,
		LogLevel::ERROR     => Log::LOG_ERROR,
		LogLevel::WARNING   => Log::LOG_WARNING,
		LogLevel::NOTICE    => Log::LOG_NOTICE,
		LogLevel::INFO      => Log::LOG_INFO,
		LogLevel::DEBUG     => Log::LOG_DEBUG
	);

	/**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_EMERGENCY, $message, $context, $cause);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_ALERT, $message, $context, $cause);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_CRITICAL, $message, $context, $cause);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_ERROR, $message, $context, $cause);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_WARNING, $message, $context, $cause);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_NOTICE, $message, $context, $cause);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_INFO, $message, $context, $cause);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// log the message
    	Log::write(Log::LOG_DEBUG, $message, $context, $cause);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
    	// by default, there's no exception that caused this message
    	$cause = null;

    	// deal with the 'exception' special case ... grrr
    	if (isset($context['exception']))
    	{
    		$cause = $context['exception'];
    		unset($context['exception']);
    	}

    	// convert the log level to one we understand
    	if (!isset($this->logLevelMap[$level]))
    	{
    		throw new E5xx_UnknownLogLevel($level);
    	}
    	$level = $this->logLevelMap[$level];

    	// log the message
    	Log::write($level, $message, $context, $cause);
    }
}