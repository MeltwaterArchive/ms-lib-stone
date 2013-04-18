<?php

namespace DataSift\Stone1\ComparisonLib;

class ComparisonResult
{
	protected $passed = false;
	protected $expected = null;
	protected $actual = null;

	public function hasFailed()
	{
		return !$this->passed;
	}

	public function hasPassed()
	{
		return $this->passed;
	}

	public function setHasFailed($expected, $actual)
	{
		$this->passed   = false;
		$this->expected = $expected;
		$this->actual   = $actual;

		return $this;
	}

	public function setHasPassed()
	{
		$this->passed = true;

		return $this;
	}

	public function getExpected()
	{
		return $this->expected;
	}

	public function getActual()
	{
		return $this->actual;
	}
}