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

use Exception;
use stdClass;
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\ObjectLib\BaseObject;

/**
 * A single config file
 *
 * @category  Libraries
 * @package   Stone/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
abstract class ConfigFile
{
	protected $filename;

	public function __construct($filename)
	{
		$this->filename = $filename;
	}

	public function loadConfigFile()
	{
		// shorthand
		$filename = $this->filename;

        // make sure the file exists
        if (!file_exists($filename)) {
        	throw new E4xx_ConfigFileNotFound($filename);
        }

        // if we get here, we have a file that we can read

        // we could potentially get legacy errors here, so best to wrap
        // things up
        $wrapper   = new LegacyErrorCatcher();
        $rawConfig = $wrapper->callUserFuncArray(function() use($filename) {
            // open the file
            return file_get_contents($filename);
        });

        // what happened?
        if (!$rawConfig)
        {
            throw new E4xx_ConfigFileNotReadable($filename);
        }
        if (!is_string($rawConfig) || empty($rawConfig))
        {
            throw new E4xx_InvalidConfigFile($filename, "file is empty");
        }

        // decode the contents
        $config = $this->decodeConfig($rawConfig);

        // if we get here, we've successfully loaded the config
        return $config;
	}

	public function saveConfigFile($config)
	{
		// shorthand
		$filename = $this->filename;

		// do we have somewhere to save the file to?
		$configFolder = dirname($filename);
		if (!is_dir($configFolder)) {
			// hrm ... can we make it?
            $success = mkdir($filename, 0700, true);

            // did it work?
            if (!$success) {
                throw new E4xx_CannotCreateConfigFolder($filename);
            }
   		}

		// convert our config into something we can save
		$rawConfig = $this->encodeConfig($config);

		// we could potentially get legacy errors here, so best to
		// wrap things up
		$wrapper = new LegacyErrorCatcher();
        try {
        	$wrapper->callUserFuncArray(function() use($filename, $rawConfig) {
        		file_put_contents($filename, $rawConfig);
        	});
        }
        catch (Exception $e)
        {
            throw new E4xx_ConfigFileNotWriteable($filename);
        }

		// all done
	}

    /**
     * convert a config into the format to save to storage
     * @param  stdClass $config
     *         the config to be encoded
     * @return string
     *         the config that can be saved
     */
    abstract public function encodeConfig(stdClass $config);

    /**
     * convert stored config into plain old PHP objects
     *
     * @param  string $rawConfig
     *         the config that needs decoding
     * @return stdclass
     *         the decoded config, for use in your app
     */
    abstract public function decodeConfig($rawConfig);
}