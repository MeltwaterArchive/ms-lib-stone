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

/**
 * Exception for when unhandled messages occur
 *
 * @category Libraries
 * @package  Stone1/ConfigLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ConfigLib;

use DataSift\Stone1\ExceptionsLib\Exxx_Exception;

class E5xx_CannotCreateRuntimeConfigFolder extends Exxx_Exception
{
    public function __construct($configDir)
    {
    	$msg = "Cannot create folder '{$configDir}' to hold cached data";
        parent::__construct(500, $msg, $msg);
    }
}