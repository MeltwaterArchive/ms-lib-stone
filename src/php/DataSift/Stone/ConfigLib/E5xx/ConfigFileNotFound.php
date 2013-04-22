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

use DataSift\Stone\ExceptionsLib\Exxx_Exception;

/**
 * Exception for when no config file can be found
 *
 * @category Libraries
 * @package  Stone/ConfigLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class E5xx_ConfigFileNotFound extends Exxx_Exception
{
    public function __construct($filename, $searchPaths)
    {
        $msg = "Config file '{$filename}' not found; search path: " . join(PATH_SEPARATOR, $searchPaths);
        parent::__construct(500, $msg, $msg);
    }
}