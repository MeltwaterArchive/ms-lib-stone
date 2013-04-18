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

use Exception;
use DataSift\Stone1\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone1\ObjectLib\BaseObject;

/**
 * The interface all ConfigLoaders must support
 *
 * @category Libraries
 * @package  Stone1/ConfigLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

interface ConfigLoader
{
    /**
     * load your app's default config file
     *
     * @return BaseObject
     */
    public function loadDefaultConfig();
    public function loadUserConfig(BaseObject $config);
    public function loadAdditionalConfig(BaseObject $config, $basename);
}