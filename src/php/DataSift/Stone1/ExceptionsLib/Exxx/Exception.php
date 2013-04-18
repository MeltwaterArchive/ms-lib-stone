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
 * @package   Stone1\ExceptionsLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\ExceptionsLib;

use Exception;

/**
 * Base class for all exceptions thrown by Stone1
 *
 * @category Libraries
 * @package  Stone1\ExceptionsLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */
class Exxx_Exception extends Exception
{
    /**
     * the developer-friendly message
     *
     * @var string
     */
    protected $devMessage;

    /**
     * constructor
     *
     * @param int $code
     *        the error code to report back (e.g. a HTTP status code)
     * @param string $publicMessage
     *        the message to show the public (e.g. in an 'error' field in an API call response)
     * @param string $devMessage
     *        the message to show your fellow developers (e.g. in a log file)
     * @param Exception $cause
     *        the original exception that caused this exception
     */
    public function __construct($code, $publicMessage, $devMessage, $cause = null)
    {
        parent::__construct($publicMessage, $code, $cause);
        $this->setDevMessage($devMessage);
    }

    /**
     * what is the developer-friendly message?
     *
     * @return string
     */
    public function getDevMessage()
    {
        return $this->devMessage;
    }

    /**
     * set the developer-friendly message
     *
     * @param  string         $newDevMessage
     * @return Exxx_Exception $this
     */
    protected function setDevMessage($newDevMessage)
    {
        $this->devMessage = $newDevMessage;
        return $this;
    }

    /**
     * convert this object into a printable string
     * @return string
     */
    public function __toString()
    {
        $msg = $this->getMessage() . ' in ' . $this->getFile() . ' at line ' . $this->getLine();
        return $msg;
    }
}