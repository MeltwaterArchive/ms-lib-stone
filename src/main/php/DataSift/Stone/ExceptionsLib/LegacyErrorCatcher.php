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

namespace DataSift\Stone\ExceptionsLib;

use Exception;

/**
 * A simple wrapper to catch legacy PHP errors and turn them into exceptions
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class LegacyErrorCatcher
{
    /**
     * The exception that we need to throw when we catch a legacy error
     *
     * @var Exception
     */
    protected $exceptionToThrow = null;

    /**
     * Call a method, function, or closure, and return the results. Any legacy
     * (ie non-exception) errors that happen during the call are converted into
     * an exception
     *
     * @param callback $callback
     * @param array $params
     * @return mixed the return value from $callback
     */
    public function callUserFuncArray($callback, $params = array())
    {
        $this->exceptionToThrow = null;

        set_error_handler(array($this, 'handleLegacyError'));
        $return = call_user_func_array($callback, $params);
        restore_error_handler();

        if ($this->exceptionToThrow !== null)
        {
            throw $this->exceptionToThrow;
        }

        return $return;
    }

    /**
     * Called when PHP detects a legacy error.  What do we want to do about it?
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     */
    public function handleLegacyError($errno, $errstr, $errfile, $errline = 0, $errcontext = null)
    {
        if ($this->exceptionToThrow && $this->exceptionToThrow->getCode() < $errno)
        {
            return;
        }

        switch($errno)
        {
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                // no need to throw an exception
                break;

            case E_ERROR:
            case E_PARSE:
            case E_WARNING:
            case E_NOTICE:
            case E_USER_ERROR:
            case E_USER_NOTICE:
            case E_USER_WARNING:
            case E_RECOVERABLE_ERROR:
            default:
                $this->exceptionToThrow = new Exception($errstr, $errno);
        }
    }
}