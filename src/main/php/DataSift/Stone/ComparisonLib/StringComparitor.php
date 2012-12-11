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
 * Compares strings against various PHP data types
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */
namespace DataSift\Stone\ComparisonLib;

class StringComparitor extends ComparitorBase
{
	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	public function isExpectedType()
	{
		// keep track of what happened
		$result = new ComparisonResult();

		$string = $this->value;

		if (is_string($string)) {
			$result->setHasPassed();
			return $result;
		}

		// force the type conversion
		$string = (string)$string;

		// now, do we still have a string?
		if (empty($string)) {
			$result->setHasFailed("string", gettype($this->value));
			return $result;
		}

		// all done
		$result->setHasPassed();
		return $result;
	}

	// ==================================================================
	//
	// The comparisons that this data type supports
	//
	// ------------------------------------------------------------------

	/**
	 * Does our string end with a given string?
	 *
	 * @param  string $expected the expected ending
	 * @return ComparisonResult
	 */
	public function endsWith($expected)
	{
		// do we have a string to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our string at least as long as the expected ending?
		if (strlen($this->value) < strlen($expected)) {
			$result->setHasFailed("ends with '{$expected}'", "string too short");
			return $result;
		}

		// get the ending
		$ending = substr($this->value, 0-strlen($expected));

		// is it what we expected?
		if ($ending != $expected) {
			$result->setHasFailed("ends with '{$expected}'", "ends with '{$ending}'");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * does our string NOT end with a given string?
	 *
	 * @param  string $expected the ending we do not expect
	 * @return ComparisonResult
	 */
	public function doesNotEndWith($expected)
	{
		// do we have a string to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our string at least as long as the expected ending?
		if (strlen($this->value) < strlen($expected)) {
			// string is too short, cannot possibly end with the expected string
			return $result;
		}

		// get the ending
		$ending = substr($this->value, 0-strlen($expected));

		// is it what we expected?
		if ($ending == $expected) {
			$result->setHasFailed("does not end with '{$expected}'", "ends with '{$ending}'");
			return $result;
		}

		// success - the endings are different
		return $result;

	}

	/**
	 * does our string contain valid JSON?
	 *
	 * @return ComparisonResult
	 */
	public function isValidJson()
	{
		// do we have a string to start off with?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// convert from JSON
		$obj = @json_decode((string)$this->value);

		// what happened?
		if (!is_object($obj)) {
			$result->setHasFailed("valid JSON string", "string failed to decode");
			return $result;
		}

		// if we get here, then success!
		return $result;
	}

	public function isNotValidJson()
	{
		// does our string contain valid JSON?
		$result = $this->isValidJson();

		// negate the result
		if ($result->hasPassed()) {
			$result->setHasFailed("invalid JSON string", "valid JSON string");
		}
		else {
			$result->setHasPassed();
		}

		// all done
		return $result;
	}

	/**
	 * is our value under test really a string?
	 *
	 * @return ComparisonResult
	 */
	public function isString()
	{
		return $this->isExpectedType();
	}

	public function matchesRegex($regex)
	{

	}

	public function doesNotMatchRegex($regex)
	{

	}

	/**
	 * does our string start with an expected string?
	 *
	 * @param  string $expected the string we expect to start with
	 * @return ComparisonResult
	 */
	public function startsWith($expected)
	{
		// do we have a string to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our string at least as long as the expected ending?
		if (strlen($this->value) < strlen($expected)) {
			$result->setHasFailed("starts with '{$expected}'", "string too short");
			return $result;
		}

		// get the beginning
		$start = substr($this->value, 0, strlen($expected));

		// is it what we expected?
		if ($start != $expected) {
			$result->setHasFailed("starts with '{$expected}'", "starts with '{$start}'");
			return $result;
		}

		// success
		return $result;

	}

	/**
	 * does our string NOT start with an expected string?
	 *
	 * @param  string $expected the string we do NOT expect to start with
	 * @return ComparisonResult
	 */
	public function doesNotStartWith($expected)
	{
		// do we have a string to test?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our string at least as long as the expected ending?
		if (strlen($this->value) < strlen($expected)) {
			// string is too short, cannot possibly start with the expected string
			return $result;
		}

		// get the start
		$start = substr($this->value, 0, strlen($expected));

		// is it what we expected?
		if ($start == $expected) {
			$result->setHasFailed("does not start with '{$expected}'", "starts with '{$ending}'");
			return $result;
		}

		// success - the starts are different
		return $result;
	}
}