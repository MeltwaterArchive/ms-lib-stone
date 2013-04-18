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
 * @package   Stone1/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\ConfigLib;

use stdClass;

/**
 * A simple library to load a JSON-encoded config file from disk
 *
 * @category Libraries
 * @package  Stone1/ConfigLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class JsonConfigLoader extends BaseConfigLoader implements ConfigLoader
{
    public function __construct($appName, $topDir)
    {
        parent::__construct($appName, $topDir, 'json');
    }

    public function encodeConfig(stdClass $config)
    {
    	return json_encode($config);
    }

    public function decodeLoadedFile($rawConfig)
    {
        return json_decode($rawConfig);
    }
}