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
 * @package   Stone1
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

/**
 * Support for comparing boolean values against other data
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ComparisonLib;

class BooleanComparitor extends ComparitorBase
{
	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	/**
	 * is our test value really a boolean?
	 *
	 * @return ComparisonResult
	 */
	public function isExpectedType()
	{
		// our return object
		$result = new ComparisonResult();

		// is it _really_ a boolean?
		if (!is_boolean($this->value)) {
			$result->setHasFailed("boolean", gettype($this->value));
			return $result;
		}

		// if we get here, all is good
		$result->setHasPassed();

		// all done
		return $result;
	}

	// ==================================================================
	//
	// The comparisons that this data type supports
	//
	// ------------------------------------------------------------------

	/**
	 * is this boolean 'true'?
	 *
	 * @return ComparisonResult
	 */
	public function isTrue()
	{
		// do we have a valid value to test?
		$result = $this->isNotNull();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value true?
		if (!$this->value) {
			$result->setHasFailed("true", "false");
			return $result;
		}

		// if we get here, then the value is true
		// an the comparison is successful
		return $result;
	}

	/**
	 * is the boolean 'false'?
	 *
	 * @return ComparisonResult
	 */
	public function isFalse()
	{
		// do we have a valid value to test?
		$result = $this->isNotNull();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value false?
		if ($this->value) {
			$result->setHasFailed("false", "true");
			return $result;
		}

		// if we get here, then the value is false,
		// and the comparison is successful
		return $result;
	}
}