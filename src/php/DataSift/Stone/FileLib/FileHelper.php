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

use DataSift\Stone\FileLib\E5xx_CouldNotCreateFolder;
use DataSift\Stone\FileLib\E5xx_CouldNotDeleteFolder;
use DataSift\Stone\FileLib\E5xx_FolderNotFound;
use DataSift\Stone\FileLib\E5xx_CouldNotDeleteFile;
use DataSift\Stone\FileLib\E5xx_CouldNotRenameFile;
use DataSift\Stone\FileLib\E5xx_FileNotFound;

/**
 * A helper class used to manage files on disk
 *
 * @category  Libraries
 * @package   Stone/FileLib
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class FileHelper
{

    /**
     * constructor.
     */
    private function __construct()
    {
    }

    /**
     * create the destination folder if it does not exist
     *
     * @var string
     */
    public static function mkdir($path)
    {
        if (file_exists($path)){
            return true;
        }

        if (!mkdir($path, 0755, true)){
            throw new E5xx_CouldNotCreateFolder($path);
        }

        return true;
    }

    /**
     * remove the target folder
     *
     * @var string
     */
    public static function rmdir($path)
    {
        if (!file_exists($path)){
            throw new E5xx_FolderNotFound($path);
        }

        if (!rmdir($path)){
            throw new E5xx_CouldNotDeleteFolder($path);
        }

        return true;
    }

    /**
     * remove the target folder
     *
     * @var string
     */
    public static function unlink($path)
    {
        if (!file_exists($path)){
            throw new E5xx_FileNotFound($path);
        }

        if (!unlink($path)){
            throw new E5xx_CouldNotDeleteFile($path);
        }

        return true;
    }

    /**
     * rename a file
     *
     * @var string
     */
    public static function rename($from, $to)
    {
        if (!file_exists($from)){
            throw new E5xx_FileNotFound($from);
        }

        if (!rename($from, $to)){
            throw new E5xx_CouldNotRenameFile($from, $to);
        }

        return true;
    }

}
