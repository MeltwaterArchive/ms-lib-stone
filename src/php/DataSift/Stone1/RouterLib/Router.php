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
 * @category  Tools
 * @package   Hornet
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\RouterLib;

use DataSift\Stone1\HtmlLib\FormData;
use DataSift\Stone1\HttpLib\HttpData;
use DataSift\Stone1\LogLib\Log;

/**
 * A very simple router to help work out where to send a request
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class Router
{
    private $appDir = null;

    public function __construct($basedir)
    {
        $this->appDir = $basedir;
    }

    public function determineScriptForRequest(Routes $routes, HttpData $get, FormData $post)
    {
        // what is the current HTML verb?
        $verb = basename($_SERVER['REQUEST_METHOD']);

        // what are the possible routes to examine?
        $routesToCheck = $routes->getRoutesForVerb($verb);

        // what page did the customer try to access?
        $requestUri = $_SERVER['REQUEST_URI'];

        // note what is happening
        Log::write(Log::LOG_DEBUG, "Received " . $verb. " request for " . $requestUri);

        // find the controller to call
        foreach ($routesToCheck as $routeToCheck)
        {
            $matches = array();
            $pattern = '|^' . $routeToCheck['pattern'] . '$|';

            Log::write(Log::LOG_DEBUG, "Checking against route '{$pattern}'");

            if (!preg_match($pattern, $requestUri, $matches)) {
                // no match
                continue;
            }

            // if we get here, then we have found our route
            //
            // but does it exist?
            $requireFile = $this->appDir . '/controllers/' . $routeToCheck['controller'];

            // does the file exist?
            if (!file_exists($requireFile))
            {
                Log::write(Log::LOG_WARNING, "Missing front-end controller: " . $requireFile);
                $requireFile = $this->appDir . '/controllers/Error500.php';
            }

            // we need to add any matched URI parameters into our
            // data
            if ($verb == Routes::METHOD_GET) {
                foreach ($matches as $key => $value) {
                    $get->addData($key, $value);
                }
            }
            else if ($verb == Routes::METHOD_POST) {
                foreach ($matches as $key => $value) {
                    $post->addData($key, $value);
                }
            }

            // return the file we've decided on
            Log::write(Log::LOG_DEBUG, "Routing to script: " . $requireFile);
            return $requireFile;
        }

        // if we get here, then there's no matching route
        Log::write(Log::LOG_WARNING, "No matching route for request '{$requestUri}'");
        $requireFile = $this->appDir . '/controllers/Error404.php';

        // return the controller to run
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