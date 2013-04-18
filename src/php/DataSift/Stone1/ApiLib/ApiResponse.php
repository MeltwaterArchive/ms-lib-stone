<?php

/**
 * Stone1- A PHP Library
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

/**
 * The object to return to API users
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ApiLib;

use Exception;
use stdClass;
use DataSift\Stone1\ExceptionsLib\Exxx_Exception;
use DataSift\Stone1\LogLib\Log;
use DataSift\Stone1\ObjectLib\JsonObject;

class ApiResponse extends JsonObject
{
    const MSG_ERROR = "error";
    const MSG_INFO = "info";
    const MSG_SUCCESS = "success";
    const MSG_WARNING = "warning";

    public $success = true;

    // NOTE:
    //
    // we do *not* define $messages here, because we do NOT want an
    // empty messages list converted to JSON
    //
    // Once we move to PHP 5.4, we can add a JsonSerializer to this class
    // to tidy things up.  But, until then ... $this->messages will not
    // exist until we add our first message
    //
    // *Make sure* that any code changes to this class take that into
    // account!

    // ==================================================================
    //
    // Handle success / failure
    //
    // ------------------------------------------------------------------

    public function setFailed()
    {
        $this->success = false;
    }

    public function setSucceeded()
    {
        $this->success = true;
    }

    // ==================================================================
    //
    // Support for dealing with exceptions
    //
    // ------------------------------------------------------------------

    public function initFromException(Exception $e)
    {
        // exceptions are always errors
        $this->setFailed();
        $this->addMessage('error', $e->getMessage());
    }

    // ==================================================================
    //
    // Support for flash messages to show the caller
    //
    // ------------------------------------------------------------------

    public function addMessage($type, $message)
    {
        $msg = new stdClass;
        $msg->alert   = $type;
        $msg->message = $message;

        if (!isset($this->messages))
        {
            $this->messages = array();
        }

        $this->messages[] = $msg;
    }

    public function hasMessages()
    {
        return (isset($this->messages) && count($this->messages) > 0);
    }

    public function getMessages()
    {
        if (!isset($this->messages))
        {
            return array();
        }

        return $this->messages;
    }

    public function addMessagesFromResponse(ApiResponse $response)
    {
        $messages = $response->getMessages();
        $this->messages = array_merge($this->messages, $messages);
    }

    // ==================================================================
    //
    // Convert to output format
    //
    // ------------------------------------------------------------------

    public function getOutput()
    {
        // special case - are we wanting to show the output in a
        // developer-friendly way?

        $prefix = $suffix = '';
        $jsonOptions = null;

        // @codeCoverageIgnoreStart
        if (isset($_GET['debug']) && $_GET['debug'] && defined ('JSON_PRETTY_PRINT'))
        {
            $prefix = '<pre>';
            $suffix = '</pre>';
            $jsonOptions = JSON_PRETTY_PRINT;
        }
        // @codeCoverageIgnoreEnd

        return $prefix . json_encode($this, $jsonOptions) . $suffix;
    }
}