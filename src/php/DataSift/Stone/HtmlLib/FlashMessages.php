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

namespace DataSift\Stone\HtmlLib;

use Exception;
use Iterator;
use stdClass;
use DataSift\Stone\ApiLib\ApiResponse;

/**
 * A handy container for holding user-visible flash messages
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class FlashMessages implements Iterator
{
    private $messages = array();

    public function __construct($messages = array())
    {
        $this->messages = $messages;
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

        $this->messages[] = $msg;
    }

    public function addMessages($messages)
    {
        foreach ($messages as $message)
        {
            $this->messages[] = $message;
        }
    }

    public function hasMessages()
    {
        return (count($this->messages) > 0);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    // ==================================================================
    //
    // Support for dealing with exceptions
    //
    // ------------------------------------------------------------------

    public function addMessageFromException(Exception $e)
    {
        // exceptions are always errors
        $this->addMessage('error', $e->getMessage());
    }

    // ==================================================================
    //
    // Support for dealing with ApiResponses
    //
    // ------------------------------------------------------------------

    public function addMessagesFromResponse(ApiResponse $response)
    {
        $messages = $response->getMessages();
        $this->messages = array_merge($this->messages, $messages);
    }

    // ==================================================================
    //
    // Iterator support
    //
    // ------------------------------------------------------------------

    /**
     * Move the iterator pointer to the start of the array
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->messages);
    }

    /**
     * Get the value at the current array pointer
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->messages);
    }

    /**
     * Get the key at the current array poitner
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->messages);
    }

    /**
     * Move the array pointer to the next entry in the array
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->messages);
    }

    /**
     * Is the array pointer pointing at a valid place in the array?
     *
     * @return boolean
     */
    public function valid()
    {
        $key = key($this->messages);
        return ($key !== null && $key !== false);
    }
}