<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Stone/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\ConfigLib;

use DataSift\Stone\ObjectLib\BaseObject;

/**
 * The base class for config loaders and savers
 *
 * @category  Libraries
 * @package   Stone/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class ConfigHelper
{
    /**
     * load a config file from anywhere
     *
     * @param  BaseObject $config
     *         your app's built-in defaults, to merge into
     * @return void
     */
    public function loadConfigFile(BaseObject $config, $filename)
    {
        // get the config
        $configFile = $this->getConfigFileObjFor($filename);
        $newConfig  = $configFile->loadConfigFile();

        // merge it in
        $config->mergeFrom($newConfig);
    }

    /**
     * load a config file from your user's dotfile collection
     *
     * @param  BaseObject $config
     *         the existing config to merge into
     * @return void
     */
    public function loadDotfileConfig(BaseObject $config, $appName, $filename)
    {
        $fullFilename = $this->getDotFilesFilename($appName, $filename);
        return $this->loadConfigFile($config, $fullFilename);
    }

    public function getDotFilesFolder($appName)
    {
        // where is the user's home directory?
        $home = getenv("HOME");
        if (empty($home)) {
            // we don't know ... assume we want to put it right here
            return "./.{$appName}";
        }

        // which folder will we store the data in?
        return "{$home}/.{$appName}";
    }

    public function getDotFilesFilename($appName, $filename)
    {
        return $this->getDotFilesFolder($appName)
               . DIRECTORY_SEPARATOR
               . basename($filename);
    }

    /**
     * load any state that has been persisted since the last time
     * your app ran
     *
     * @param  string $appName
     *         the name of your app
     * @return BaseObject
     *         the config loaded from persistent store
     */
    public function loadRuntimeConfig($appName, $filename = 'runtime.json')
    {
        $fullFilename = $this->getDotFilesFilename($appName, $filename);

        $config = new BaseObject();
        $this->loadConfigFile($config, $fullFilename);
        return $config;
    }

    /**
     * save any state that you want to persist until the next time
     * your app runs
     *
     * @param  BaseObject $config
     *         the existing config to merge into
     * @param  string $appName
     *         the name of your app
     * @param  string $configBasename
     *         the basename (minus extension) for the runtime config's filename
     * @return void
     */
    public function saveRuntimeConfig(BaseObject $config, $appName, $filename = 'runtime.json')
    {
        $fullFilename = $this->getDotFilesFilename($appName, $filename);

        $configFile = $this->getConfigFileObjFor($filename);
        $configFile->saveConfigFile($config);
    }

    /**
     * use the filename extension to obtain the appropriate type of object
     * to handle the config file
     */
    protected function getConfigFileObjFor($filename)
    {
        $parts = explode('.', $filename);
        $ext   = strtolower(end($parts));

        switch ($ext) {
            case 'yaml':
                return new YamlConfigFile($filename);

            case 'json':
            default:
                return new JsonConfigFile($filename);
        }
    }
}