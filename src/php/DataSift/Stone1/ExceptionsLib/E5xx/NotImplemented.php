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
 * @package   Stone1\ExceptionsLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\ExceptionsLib;

/**
 * Exception for when unhandled messages occur
 *
 * @category Libraries
 * @package  Stone1\ExceptionsLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */
class E5xx_NotImplemented extends Exxx_Exception
{
    /**
     * constructor
     *
     * @param string $method
     *        name of the PHP method that has not been implemented.
     *        Use the built-in __METHOD__ constant.
     */
    public function __construct($method)
    {
        $msg = "Not implemented: " . $method;
        parent::__construct(500, $msg, $msg);
    }
}