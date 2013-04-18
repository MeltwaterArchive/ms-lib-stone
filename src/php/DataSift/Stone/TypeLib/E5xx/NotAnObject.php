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
 * @package   Stone
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

/**
 * Exception for when TypeLib was expecting an object, but was given
 * something ... else
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\TypeLib;

use DataSift\Stone\ExceptionsLib\Exxx_Exception;

class E5xx_NotAnObject extends Exxx_Exception
{
    public function __construct($value)
    {
    	$type = gettype($value);
        $msg = "Was expecting an object; received value of type '{$type}' instead";
        parent::__construct(500, $msg, $msg);
    }
}