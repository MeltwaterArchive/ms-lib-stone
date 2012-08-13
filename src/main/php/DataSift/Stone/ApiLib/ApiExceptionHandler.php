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
 * Exception handler to load when dealing with API requests
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ApiLib;

use Exception;
use DataSift\Stone\ExceptionsLib\Exxx_Exception;
use DataSift\Stone\LogLib\Log;

class ApiExceptionHandler
{
    public function __construct()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    public function handleException(Exception $e)
    {
        // what kind of exception are we dealing with?
        if ($e instanceof Exxx_Exception)
        {
            $code = $e->getCode();
            Log::write(Log::LOG_CRITICAL, "uncaught exception: " . get_class($e) . " with public message: " . $e->getMessage() . "; and developer message: " . $e->getDevMessage());
        }
        else
        {
            $code = 500;
            Log::write(Log::LOG_CRITICAL, "uncaught exception: " . get_class($e) . " with message: " . $e->getMessage());
        }

        // create the response to return
        $response = new ApiResponse();
        $response->initFromException($e);

        // time to output what happened
        header("HTTP/1.1 " . $code . " API call failed");
        $output = $response->getOutput();
        echo $output;
        exit(0);
    }
}