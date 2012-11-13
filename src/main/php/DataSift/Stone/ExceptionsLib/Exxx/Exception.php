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
 * Base class for all exceptions
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ExceptionsLib;

use Exception;

class Exxx_Exception extends Exception
{
    protected $devMessage;

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
    public function setDevMessage($newDevMessage)
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