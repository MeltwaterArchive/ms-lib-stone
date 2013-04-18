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
 * Compares arrays against values of many types
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ComparisonLib;

class ArrayComparitor extends ComparitorBase
{
	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	/**
	 * return a normalised version of the data, suitable for comparison
	 * purposes
	 *
	 * @return array
	 */
	public function getValueForComparison()
	{
		$return = $this->value;
		ksort($return);

		// sort any sub-arrays
		foreach ($return as $key => $value)
		{
			$comparitor   = $this->getComparitorFor($value);
			$return[$key] = $comparitor->getValueForComparison();
		}

		// all done
		return $return;
	}

	/**
	 * is the value we are testing the right type?
	 * @return boolean [description]
	 */
	public function isExpectedType()
	{
		// our return object
		$result = new ComparisonResult();

		// is it _really_ an array?
		if (!is_array($this->value)) {
			$result->setHasFailed("array", gettype($this->value));
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
	 * does this array contain the given value?
	 *
	 * @param  mixed $value the value to test for
	 * @return ComparisonResult
	 */
	public function containsValue($value)
	{
		// do we have a valid array to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is the value present?
		if (!in_array($value, $this->value)) {
			$result->setHasFailed($value, "value not found");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * does this array NOT contain the given value?
	 *
	 * @param  mixed $value the value to test for
	 * @return ComparisonResult
	 */
	public function doesNotContainValue($value)
	{
		// do we have a valid array to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is the value present?
		if (in_array($value, $this->value)) {
			$result->setHasFailed("value not found", $value);
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * does this array contain the given key?
	 *
	 * @param  mixed $key the key to test for
	 * @return ComparisonResult
	 */
	public function hasKey($key)
	{
		// do we have a valid array to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is the key present?
		if (!array_key_exists($key, $this->value)) {
			$result->setHasFailed("key '{$key}' set", "key does not exist");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the given key NOT in our array?
	 *
	 * @param  mixed $key the key to search for
	 * @return ComparisonResult
	 */
	public function doesNotHaveKey($key)
	{
		// do we have a valid array to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is the key present?
		if (array_key_exists($key, $this->value)) {
			$result->setHasFailed("key not set", "key '{$key}' is set");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is this array the length we expect it to be?
	 *
	 * @param  integer  $expected  the expected length of our array
	 * @return ComparisonResult
	 */
	public function hasLength($expected)
	{
		// are we looking at an array?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// how long is it?
		$actualLen = count($this->value);

		// is it the right length?
		if ($actualLen != $expected) {
			$result->setHasFailed("length of '{$expected}'", "length of '{$actualLen}'");
			return $result;
		}

		// if we get here, then it is the right length
		return $result;
	}

	/**
	 * is the value under test really an array?
	 *
	 * @return ComparisonResult
	 */
	public function isArray()
	{
		return $this->isExpectedType();
	}

	/**
	 * is our array the same length as another array?
	 *
	 * @param  array $expected
	 *         the array to compare against
	 * @return ComparisonResult
	 */
	public function isSameLengthAs($expected)
	{
		// are we looking at an array?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// are we comparing against an array?
		if (!is_array($expected)) {
			$type = gettype($expected);
			$result->setHasFailed("\$expected is an array", "\$expected is a '{$type}'");
			return $result;
		}

		// how long is our array?
		$actualLen = count($this->value);

		// how long is it supposed to be?
		$expectedLen = count($expected);

		// are they the same?
		if ($expectedLen != $actualLen) {
			$result->setHasFailed("length is '{$expectedLen}'", "length is '{$actualLen}'");
			return $result;
		}

		// success!
		return $result;
	}
}