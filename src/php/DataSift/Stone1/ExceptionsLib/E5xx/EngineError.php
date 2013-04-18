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

/**
 * Exception for when LegacyErrorCatcher caught a PHP4-style runtime
 * error, and needs to convert it into an exception
 *
 * @category Libraries
 * @package  Stone1\ExceptionsLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ExceptionsLib;

class E5xx_EngineError extends Exxx_Exception
{
    /**
     * a simple table to translate PHP error constants into a
     * human-readable string
     *
     * this really should be built into the PHP engine itself, but
     * AFAIK it currently is not
     *
     * @var array
     */
	static protected $engineErrors = array(
        E_WARNING => "E_WARNING: ",
        E_NOTICE => "E_NOTICE: ",
        E_USER_ERROR => "E_USER_ERROR: ",
        E_USER_NOTICE => "E_USER_NOTICE: ",
        E_USER_WARNING => "E_USER_WARNING: ",
        E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR: ",
        E_DEPRECATED => "E_DEPRECATED: ",
        E_USER_DEPRECATED => "E_USER_DEPRECATED: "
	);

    /**
     * the PHP engine error that was passed to us
     *
     * @var integer
     */
    protected $engineError = 0;

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

        // remember the PHP engine error code we received
        $this->engineError = $errno;

        // call our parent's constructor
        parent::__construct(500, $msg, $msg);
    }

    public function getEngineError()
    {
        return $this->engineError;
    }
}