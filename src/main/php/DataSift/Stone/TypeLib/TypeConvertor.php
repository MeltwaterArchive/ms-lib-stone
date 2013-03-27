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

namespace DataSift\Stone\TypeLib;

/**
 * Converts one type into another
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class TypeConvertor
{
	/**
	 * a list of the convertors that we've already created, to save
	 * having to make more of them
	 *
	 * @var array
	 */
	protected $convertors = array();

	/**
	 * get an object that knows how to convert any given type
	 *
	 * @param  string $type   the 'type' of the data that needs converting
	 * @return TypeConvertor  an object that can do type conversions
	 */
	public function getConvertorForType($type)
	{
		// do we have one of these already?
		if (isset($this->convertors[$type])) {
			// yes we do!
			return $this->convertors[$type];
		}

		// work out the classname that we need
		$className = __NAMESPACE__ . "\\" . ucfirst($type) . "Convertor";

		// do we have this class?
		if (!class_exists($className)) {
			throw new E5xx_TypeNotSupported($type);
		}

		// create a convertor
		$convertor = new $className();

		// remember it for next time
		$this->convertors[$type] = $convertor;

		// return it
		return $convertor;
	}
}
