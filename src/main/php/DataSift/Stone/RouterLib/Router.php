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
 * @category  Tools
 * @package   Hornet
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\RouterLib;

use DataSift\Stone\LogLib\Log;

/**
 * A very simple router to help work out where to send a request
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class Router
{
    private $appDir = null;

    public function __construct($basedir)
    {
        $this->appDir = realpath($basedir);
    }

    public function determineScriptForRequest()
    {
        // what is the current HTML verb?
        $verb = basename($_SERVER['REQUEST_METHOD']);

        // what page did the customer try to access?
        $script = dirname($_SERVER['SCRIPT_NAME']);
        if ($script !== '/')
        {
            $script .= '/';
        }
        $script .= basename($_SERVER['SCRIPT_NAME'], '.php');

        // note what is happening
        Log::write(Log::LOG_DEBUG, "Received " . $verb. " request for " . $script);

        // delegate this work to a specialised helper method
        $delegate = 'determineScriptFor' . ucfirst($verb) . 'Request';
        $requireFile = $this->$delegate($script);

        // does the file exist?
        if (!file_exists($requireFile))
        {
            Log::write(Log::LOG_WARNING, "Missing front-end controller: " . $requireFile);
            $requireFile = $this->appDir . '/404.php';
        }

        // return the file we've decided on
        Log::write(Log::LOG_DEBUG, "Routing to script: " . $requireFile);
        return $requireFile;
    }

    protected function determineScriptForGetRequest($script)
    {
        // if there are no parameters, call the index script
        if (count($_GET) == 0)
        {
            return $this->appDir . $script . '_get_index.php';
        }

        return $this->appDir . $script . '_get_params.php';
    }

    protected function determineScriptForPostRequest($script)
    {
        // if there are no parameters, call the index script
        return $this->appDir . $script . '_post.php';
    }
}