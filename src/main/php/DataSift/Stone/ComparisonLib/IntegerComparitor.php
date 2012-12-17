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
 * Base class for all comparison classes
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ComparisonLib;

class IntegerComparitor extends ComparitorBase
{
	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	public function isExpectedType()
	{
		// the result that we will return
		$result = new ComparisonResult();

		// is this really an integer?
		if (!is_integer($this->value)) {
			$result->setHasFailed("integer", gettype($this->value));
		}
		else {
			$result->setHasPassed();
		}

		// all done
		return $result;
	}

	// ==================================================================
	//
	// The comparisons that this data type supports
	//
	// ------------------------------------------------------------------

	public function equals($expected)
	{
		// do we really have an integer to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value the expected value?
		if ($this->value != $expected) {
			$result->setHasFailed($expected, $this->value);
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test greater than what we expect?
	 *
	 * @param  integer $expected  the value we expect to be greater than
	 * @return ComparisonResult
	 */
	public function isGreaterThan($expected)
	{
		// do we really have an integer to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value greater than the expected value?
		if ($this->value <= $expected) {
			$result->setHasFailed("> {$expected}", $this->value);
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test greater than or equal to what we expect?
	 *
	 * @param  integer $expected
	 *         the value we expect to be greater than or equal to
	 * @return ComparisonResult
	 */
	public function isGreaterThanOrEqualTo($expected)
	{
		// do we really have an integer to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value greater than the expected value?
		if ($this->value < $expected) {
			$result->setHasFailed(">= {$expected}", $this->value);
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test really an integer?
	 * @return ComparisonResult
	 */
	public function isInteger()
	{
		return $this->isExpectedType();
	}

	/**
	 * is the value under test less than what we expect?
	 *
	 * @param  integer $expected  the value we expect to be less than
	 * @return ComparisonResult
	 */
	public function isLessThan($expected)
	{
		// do we really have an integer to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value less than the expected value?
		if ($this->value >= $expected) {
			$result->setHasFailed("< {$expected}", $this->value);
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test less than or equal to what we expect?
	 *
	 * @param  integer $expected
	 *         the value we expect to be less than or equal to
	 * @return ComparisonResult
	 */
	public function isLessThanOrEqualTo($expected)
	{
		// do we really have an integer to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our value less than or equal to the expected value?
		if ($this->value > $expected) {
			$result->setHasFailed("<= {$expected}", $this->value);
			return $result;
		}

		// success
		return $result;
	}
}