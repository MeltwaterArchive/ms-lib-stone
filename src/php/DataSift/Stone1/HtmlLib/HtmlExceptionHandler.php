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

/**
 * Encapsulates the IPC mechanisms for a client process, such as hornet-queen
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\HtmlLib;

use Exception;
use stdClass;
use DataSift\Stone1\ExceptionsLib\Exxx_Exception;
use DataSift\Stone1\LogLib\Log;

class HtmlExceptionHandler
{
    public function __construct()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    public function handleException(Exception $e)
    {
        // yes, this is evil and untestable - get over it!
        global $app;

        // what kind of exception are we dealing with?
        if ($e instanceof Exxx_Exception)
        {
            $code = $e->getCode();
            Log::write(Log::LOG_CRITICAL, "uncaught exception: " . get_class($e) . " with public message: " . $e->getMessage() . "; and developer message: " . $e->getDevMessage());

            // better add a flash message for the user too
            $app->messages->addMessage('error', $e->getMessage());
        }
        else
        {
            $code = 500;
            Log::write(Log::LOG_CRITICAL, "uncaught exception: " . get_class($e) . " with message: " . $e->getMessage());

            // better add a flash message for the user too
            $app->messages->addMessage('error', 'An unexpected error occurred');
        }

        // now make sure we load the '500' page
        require APP_DIR . 'controllers/Error500.php';
        exit(0);
    }
}