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
 * Base class for all comparison classes
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\ComparisonLib;

abstract class ComparitorBase
{
	/**
	 * the value that we will be comparing against
	 * @var [type]
	 */
	protected $value = null;

	/**
	 * constructor
	 *
	 * @param mixed $value the value that we will run comparisons against
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	// ==================================================================
	//
	// Helper methods
	//
	// ------------------------------------------------------------------

	/**
	 * what is the value that we are testing here?
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * is our test value really what we expect it to be?
	 *
	 * @return ComparisonResult
	 */
	abstract public function isExpectedType();

	/**
	 * Provide a comparible comparitor object for any type of variable
	 *
	 * @param  mixed $value the data we need a comparitor for
	 * @return mixed        the appropriate comparitor object for $value
	 */
	public function getComparitorFor($value)
	{
		// what do we have?
		$type = gettype($value);

		// work out the classname we need
		$className = __NAMESPACE__ . '\\' . ucfirst($type) . 'Comparitor';

		// do we have one?
		if (!class_exists($className)) {
			throw new E5xx_TypeNotSupported($type);
		}

		// yes we do!
		$comparitor = new $className($value);

		// all done
		return $comparitor;
	}

	/**
	 * Return a copy of the value, with anything like member sorting
	 * done
	 *
	 * Override this if the value needs any sort of tweaking before it
	 * can be compared
	 *
	 * @return mixed
	 */
	public function getValueForComparison()
	{
		return $this->getValue();
	}

	// ==================================================================
	//
	// Comparison methods
	//
	// ------------------------------------------------------------------

	/**
	 * is the value under test what we expect it to be?
	 *
	 * @param  mixed            $expected  what we expect our value to be
	 * @return ComparisonResult            the results of the comparison
	 */
	public function equals($expected)
	{
		// keep track of how we do
		$result = new ComparisonResult();

		// let's do this the dirty way, by dumping both variables,
		// and then using the operating system's diff program to test
		// the results

		$expectedComparitor = $this->getComparitorFor($expected);

		// normalise the values that we are comparing
		$sourceA = var_export($expectedComparitor->getValueForComparison(), true);
		$sourceB = var_export($this->getValueForComparison(), true);

		// write both normalised values out to temporary files
		$tmpNam1 = tempnam("/tmp", "storyteller-diffA-");
		$tmpNam2 = tempnam("/tmp", "storyteller-diffB-");
		file_put_contents($tmpNam1, $sourceA);
		file_put_contents($tmpNam2, $sourceB);

		// diff the two files to see what has changed
		$differences = trim(`diff -u $tmpNam1 $tmpNam2`);

		// how did we do?
		if (empty($differences)) {
			$result->setHasPassed();
		}
		else {
			$result->setHasFailed($differences, '');
		}

		// tidy up after ourselves
		unlink($tmpNam1);
		unlink($tmpNam2);

		// all done
		return $result;
	}

	/**
	 * is the value under test different to what we expect it to be?
	 *
	 * @param  mixed  $expected what we do NOT expect our value to be
	 * @return ComparisonResult
	 */
	public function doesNotEqual($expected)
	{
		// are the two values equal?
		$result = $this->equals($expected);

		// negate the result
		if ($result->hasPassed()) {
			$result->setHasFailed("not the same", "values were the same");
		}
		else {
			$result->setHasPassed();
		}

		// all done
		return $result;
	}

	/**
	 * is the value under test 'empty', according to PHP?
	 *
	 * @return ComparisonResult
	 */
	public function isEmpty()
	{
		// do we have valid data to test against?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our data 'empty'?
		if (!empty($this->value)) {
			// no, it is not
			$result->setHasFailed("empty value", "value is not empty");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test NOT 'empty', according to PHP?
	 *
	 * @return ComparisonResult
	 */
	public function isNotEmpty()
	{
		// do we have valid data to test against?
		$result = $this->isExpectedType();
		if ($result->hasFailed()) {
			return $result;
		}

		// is our data 'empty'?
		if (empty($this->value)) {
			// yes, it is
			$result->setHasFailed("value not empty", "empty value");
			return $result;
		}

		// success
		return $result;
	}

	/**
	 * is the value under test actually a NULL?
	 *
	 * @return ComparisonResult
	 */
	public function isNull()
	{
		// our return value
		$result = new ComparisonResult();

		// what is the value that we are testing?
		$value = $this->getValue();

		// is our value a NULL?
		if (!is_null($value)) {
			$result->setHasFailed("null", gettype($value));
			return $result;
		}

		// our value really is a NULL
		$result->setHasPassed();
		return $result;
	}

	/**
	 * is the boolean not NULL?
	 *
	 * this comparison will also fail if the boolean is actually a different
	 * data type
	 *
	 * @return ComparisonResult
	 */
	public function isNotNull()
	{
		// make sure it is a valid boolean
		// a NULL will fail this test
		return $this->isExpectedType();
	}

	/**
	 * is our value under test the same variable that $expected is?
	 *
	 * this test might only be reliable for objects!
	 *
	 * @param  mixed  $expected  the variable to compare against
	 * @return ComparisonResult
	 */
	public function isSameAs(&$expected)
	{
		// our return value
		$result = new ComparisonResult();

		// test for absolute equivalence
		if ($this->value === $expected) {
			$result->setHasPassed();
		}
		else {
			$result->setHasFailed("same variable", "not same variable");
		}

		// all done
		return $result;
	}

	/**
	 * is our value under test NOT the same variable that $expected is?
	 *
	 * this test might only be reliable for objects!
	 *
	 * @param  mixed  $expected  the variable to compare against
	 * @return ComparisonResult
	 */
	public function isNotSameAs(&$expected)
	{
		// our return value
		$result = new ComparisonResult();

		// test for absolute equivalence
		if ($this->value !== $expected) {
			$result->setHasPassed();
		}
		else {
			$result->setHasFailed("different variable", "same variable");
		}

		// all done
		return $result;
	}
}