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
 * @package   Stone/DownloadLib
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\DownloadLib;

use DataSift\Stone\DownloadLib\E5xx_CouldNotCreateFolder;

/**
 * A helper class used to download files to disk
 *
 * @category  Libraries
 * @package   Stone/DownloadLib
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class FileDownloader
{

    /**
     * constructor.
     */
    public function __construct()
    {
    }

    /**
     * download a file to a specific location
     *
     * @var string $from The path to download from
     * @var string $to The path to save the file to
     */
    public function download($from, $to = null)
    {
        if (!$to) {
            $to = basename($from);
        }

        // we're assuming here that paths end in a /
        // and if it's not a /, it's the filename to 
        // write, so dirname it away
        $toPath = $to;
        if (substr($toPath, -1) != "/"){
            $toPath = dirname($toPath);
        }

        // create the path if it doesn't exist
        $this->createDestinationIfRequired($toPath);

        $file = file_get_contents($from);
        return file_put_contents($to, $file);
    }

    /**
     * create the destination folder if it does not exist
     *
     * @var string
     */
    private function createDestinationIfRequired($path)
    {
        if (file_exists($path)){
            return true;
        }

        if (!mkdir($path, 0755, true)){
            throw new E5xx_CouldNotCreateFolder($path);
        }

        return true;

    }

 }
