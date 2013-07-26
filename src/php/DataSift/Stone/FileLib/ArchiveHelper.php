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
 * @package   Stone/FileLib
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\FileLib;

use DataSift\Stone\FileLib\E5xx_InvalidArchive;
use finfo;
use ZipArchive;

/**
 * A helper class used to manage archives
 *
 * @category  Libraries
 * @package   Stone/FileLib
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class ArchiveHelper
{
    protected static $supportedArchives = array(
        "application/zip" => "unzip"
    );

    /**
     * constructor.
     */
    private function __construct()
    {
    }

    /**
     * Check if a path is an archive
     *
     * @var string $path The path to the archive
     * @var string $type The type of archive we're expecting
     */
    public static function isArchive($path, $type=null)
    {
        if (!file_exists($path)){
            throw new E5xx_FileNotFound($path);
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($path);

        if ($type){
            return $mimeType == $type;
        }

        $supported = array_keys(static::$supportedArchives);
        return in_array($mimeType, $supported);
    }

    /**
     * extract an archive
     *
     * @var string
     */
    public static function extract($archive, $target)
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($archive);

        if (!static::isArchive($archive)){
            throw new E5xx_InvalidArchive($archive, $mimeType);
        }

        $extractFunction = static::$supportedArchives[$mimeType];
        static::$extractFunction($archive, $target);

        return true;
    }

    /**
     * unzip the archive
     *
     * @var string
     */
    private static function unzip($file, $target)
    {
        $zipArchive = new ZipArchive();
        $result = $zipArchive->open($file);
        if ($result === TRUE) {
            $zipArchive ->extractTo($target);
            $zipArchive ->close();
            return true;
        } else {
            throw new E5xx_CouldNotUnzipFile($file);
        }

        return true;
    }



}
