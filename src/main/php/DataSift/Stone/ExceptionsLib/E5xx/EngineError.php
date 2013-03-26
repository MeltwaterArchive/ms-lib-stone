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
 * @package   Stone\ExceptionsLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

/**
 * Exception for when LegacyErrorCatcher caught a PHP4-style runtime
 * error, and needs to convert it into an exception
 *
 * @category Libraries
 * @package  Stone\ExceptionsLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ExceptionsLib;

class E5xx_EngineError extends Exxx_Exception
{
	static protected $engineErrors = array(
        E_ERROR => "E_ERROR: ",
        E_PARSE => "E_PARSE: ",
        E_WARNING => "E_WARNING: ",
        E_NOTICE => "E_NOTICE: ",
        E_USER_ERROR => "E_USER_ERROR: ",
        E_USER_NOTICE => "E_USER_NOTICE: ",
        E_USER_WARNING => "E_USER_WARNING: ",
        E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR: "
	);

    public function __construct($errstr, $errno)
    {
    	// is this an error we know about?
    	if (isset(self::$engineErrors[$errno])) {
    		// yes, it is
    		$msg = self::$engineErrors[$errno] . $errstr;
    	}
    	else {
    		// we do not know what this is
    		$msg = "PHP engine error #{$errno}: {$errstr}";
    	}
        parent::__construct(500, $msg, $msg);
    }
}