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
 * Converts objects into other types
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class ObjectConvertor extends TypeConvertor
{
	public function toArray($obj)
	{
		// the array we will return
		$return = array();

		// do we have an object to convert?
		if (!is_object($obj)) {
			throw new E5xx_NotAnObject($obj);
		}

		// take advantage of object iteration
		foreach ($obj as $key => $value)
		{
			// how are we going to convert this value?
			$convertor = $this->getConvertorForType(gettype($value));

			// make the conversion
			$return[$key] = $convertor->toArrayValue($value);
		}

		// all done
		return $return;
	}

	public function toArrayValue($obj)
	{
		return $this->toArray($obj);
	}
}