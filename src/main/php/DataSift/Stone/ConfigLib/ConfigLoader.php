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
 * A simple library to load a JSON-encoded config file from disk
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ConfigLib;

use Exception;

class ConfigLoader
{
    /**
     * load the contents of the app's config file
     *
     * @param  string $appName the name of the app
     * @return stdclass the loaded config
     */
    static public function load($filename)
    {
        // make sure the file exists
        self::requireConfigFile($filename);

        // if we get here, we have a file that we can read

        // open the file
        $rawConfig = @file_get_contents($filename);
        if (!$rawConfig || !is_string($rawConfig) || empty($rawConfig))
        {
            throw new E5xx_InvalidConfigFile("Config file '$filename' is empty or unreadable");
        }

        // decode the contents
        $config = json_decode($rawConfig);
        // did it work?
        if (get_class($config) != "stdClass")
        {
            throw new E5xx_InvalidConfigFile("Config file '$filename' contains invalid JSON");
        }

        // if we get here, we've successfully loaded the config
        return $config;
    }

    /**
     * make sure that the config file exists
     *
     * if there are any problems, we throw Exceptions
     *
     * @param  string $filename the config file to test
     * @return void
     */
    static public function requireConfigFile($filename)
    {
        if (!file_exists($filename))
        {
            throw new E5xx_InvalidConfigFile("Config file '$filename' is missing", 500);
        }
        if (!is_readable($filename))
        {
            throw new E5xx_InvalidConfigFile("Config file '$filename' cannot be opened for reading", 500);
        }
    }
}