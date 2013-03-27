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
 * @package   Stone/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\ConfigLib;

use Exception;
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\ObjectLib\BaseObject;

/**
 * Base class for our config file loaders
 *
 * @category Libraries
 * @package  Stone/ConfigLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

abstract class BaseConfigLoader
{
    private $appName = null;
    private $configSuffix = "";

    private $defaultConfigFilePaths = array();
    private $configFileBasename = null;

    /**
     * instantiate the config loader
     *
     * @param string $appName
     *        the name of your app
     *
     *        this is used to work out the name of the folder(s) to look
     *        inside for the default config file, and what the name of
     *        the config file itself should be
     *
     * @param string $topDir
     *        the INSTALL_PREFIX of your app, or the top of your source
     *        tree
     *
     *        the config loaders will look in the following places
     *        for your app's default config files (in this order):
     *
     *        {$topDir}/etc/
     *        /etc/{$appName}/
     *
     * @param string $configSuffix
     *        the filename suffix to use for config files
     *
     *        this will be appended onto any filenames that we search for,
     *        and also used when looking for the default config file
     *        like this:
     *
     *        {$appName}.{$configSuffix}
     */
    public function __construct($appName, $topDir, $configSuffix)
    {
        // remember the name of the app we're loading configs for
        $this->appName = $appName;

        // remember the suffix to append to our config files when
        // we're looking for them
        $this->configSuffix = $configSuffix;

        // setup our list of directories to search for the app's
        // default config file
        $this->initDefaultConfigFilePaths($topDir);

        // work out what the basename (the filename with no path) of
        // our default config file should be
        $this->initConfigFileBasename();
    }

    protected function initDefaultConfigFilePaths($topDir)
    {
        $this->defaultConfigFilePaths = array(
            $topDir . '/etc/',
            "/etc/{$this->appName}/"
        );
    }

    protected function initConfigFileBasename()
    {
        $this->configFileBasename = $this->appName . '.' . $this->configSuffix;
    }

    public function loadDefaultConfig()
    {
        // this will contain the loaded config when we are finished
        $config    = new BaseObject;

        // load the expected config from disk
        $newConfig = $this->loadFileFromDefaultPaths($this->configFileBasename);

        // copy it across to our container
        //
        // we do this because our container gives us features that
        // stdClass does not
        $config->mergeFrom($newConfig);

        // all done - return the container to the caller
        return $config;
    }

    public function loadUserConfig(BaseObject $config)
    {
        // where is the user's home directory?
        $home = getenv("HOME");
        if (empty($home)) {
            // we don't know ... we cannot continue
            return;
        }

        // where will the user's defaults file be?
        $filename = "{$home}/.{$this->appName}/{$this->configFileBasename}";

        // does it exist?
        if (!file_exists($filename)) {
            // no - nothing more to do
            return;
        }

        // we have a file to load
        $newConfig = $this->loadConfigFile($filename);

        // merge the user's defaults with the global defaults
        $config->mergeFrom($newConfig);

        // all done
    }

    public function loadAdditionalConfig(BaseObject $config, $basename)
    {
        // load the additional config from disk
        $newConfig = $this->loadFileFromDefaultPaths($basename . '.' . $this->configSuffix);

        // merge it into the existing config
        $config->mergeFrom($newConfig);

        // all done
    }

    protected function loadFileFromDefaultPaths($configName)
    {
        // load the first file that we find
        foreach ($this->defaultConfigFilePaths as $filename) {
            $filename .= $configName;
            if (file_exists($filename)) {
                $config = $this->loadConfigFile($filename);
                break;
            }
        }

        // did we find one?
        if (!isset($config)) {
            // no, we did not
            throw new RuntimeException("Unable to find config file called '{$configName}'");
        }

        // if we get here, then we successfully loaded some config
        return $config;
    }

    /**
     * safely load the contents of a config file
     *
     * @param  string $appName the name of the app
     * @return stdClass the loaded config
     */
    protected function loadConfigFile($filename)
    {
        // make sure the file exists
        $this->requireConfigFile($filename);

        // if we get here, we have a file that we can read

        // we could potentially get legacy errors here, so best to wrap
        // things up
        $wrapper = new LegacyErrorCatcher();
        return $wrapper->callUserFuncArray(function() use($filename) {
            // open the file
            $rawConfig = @file_get_contents($filename);
            if (!$rawConfig || !is_string($rawConfig) || empty($rawConfig))
            {
                throw new E5xx_InvalidConfigFile("Config file '$filename' is empty or unreadable");
            }

            // decode the contents
            $config = $this->decodeLoadedFile($rawConfig);

            // did it work?
            if (get_class($config) != "stdClass")
            {
                throw new E5xx_InvalidConfigFile("Config file '$filename' contains invalid JSON");
            }

            // if we get here, we've successfully loaded the config
            return $config;
        });
    }

    /**
     * make sure that the config file exists
     *
     * if there are any problems, we throw Exceptions
     *
     * @param  string $filename the config file to test
     * @return void
     */
    protected function requireConfigFile($filename)
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

    /**
     * decode the loaded config file into a tree of objects
     *
     * override this in your format-specific config file loader
     *
     * @param  string $rawConfig
     *         the raw contents of the config file that has been loaded
     *
     * @return stdClass
     *         the results of decoding the config file
     */
    abstract protected function decodeLoadedFile($rawConfig);
}