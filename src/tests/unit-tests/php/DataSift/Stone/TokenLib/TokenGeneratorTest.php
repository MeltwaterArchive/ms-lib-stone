<?php

namespace DataSift\Stone\TokenLib;

use PHPUnit_Framework_TestCase;

class TokenGeneratorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::__construct
	 */
	public function testCanInstantiate()
	{
	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new TokenGenerator('unit test');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj instanceof TokenGenerator);
	}

	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::__construct
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::setSecretKey
	 */
	public function testSetsSecretKeyOnConstruction()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedKey = 'unit-test';

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new TokenGenerator($expectedKey);

	    // ----------------------------------------------------------------
	    // test the results

	    $actualKey = $obj->getSecretKey();
	    $this->assertEquals($expectedKey, $actualKey);
	}

	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::getSecretKey
	 */
	public function testCanGetSecretKey()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		// how many keys do we want?
		$noOfKeys = 100;

		// generate some random keys
	    $expectedKeys = array();
	    for ($i = 0; $i < $noOfKeys; $i++)
	    {
	    	$expectedKeys[] = 'unit-test-' + rand(0, ($i + 1) * 20);
	    }

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualKeys = array();
	    for ($i = 0; $i < $noOfKeys; $i++) {
	    	$obj = new TokenGenerator($expectedKeys[$i]);
	    	$actualKeys[] = $obj->getSecretKey();
	    }

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedKeys, $actualKeys);
	}

	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::generateToken
	 */
	public function testCanGenerateTokensOfGivenLength()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    // our token generator
	    $obj = new TokenGenerator('unit-test');

	    // how many tokens do we want?
	    $noOfTokens = 10;

	    // how long do we want each token to be?
	    $expectedTokenLength = 16;

	    // ----------------------------------------------------------------
	    // perform the change

	    $tokens = array();
	    for ($i = 0; $i < $noOfTokens; $i++) {
	    	$tokens[] = $obj->generateToken($expectedTokenLength);
	    }

	    // ----------------------------------------------------------------
	    // test the results

	    // inspect our tokens
	    for ($i = 0; $i < $noOfTokens; $i++) {
	    	// get the token
	    	$token = $tokens[$i];

	    	// make sure it is a string
	    	$this->assertTrue(is_string($token));

	    	// make sure it is long enough
	    	$this->assertEquals($expectedTokenLength, strlen($token));
	    }
	}

	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::generateToken
	 */
	public function testCanGenerateRandomTokens()
	{
		// a suitable set of randomness tests is non-trivial, and will
		// never be definitive
		$this->markTestIncomplete('need to implement a suitable randomness test');
	}

	/**
	 * @covers DataSift\Stone\TokenLib\TokenGenerator::generateSaltedHash
	 */
	public function testCanGenerateSaltedHashes()
	{
	    // ----------------------------------------------------------------
	    // setup your test
	    //
	    // we're expecting to get the same output for each salt / token
	    // combination

		$expectedSalts = array(
			'1bd6a4f9',
			'8af9a92d',
		);
		$expectedTokens = array(
			'F/mFegh10U1PBMtm3Q7M0A==',
			'fypCOvGUhQgguLc2OB8+PQ==',
		);
	    $obj = new TokenGenerator('unit-test');

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualTokens = array();
	    for($i = 0; $i < count($expectedTokens); $i++) {
		    $actualTokens[] = $obj->generateSaltedHash('datasiftrocks', $expectedSalts[$i]);
	    }

	    // ----------------------------------------------------------------
	    // test the results

	    for($i = 0; $i < count($expectedTokens); $i++) {
	    	$token = $actualTokens[$i];
		    $parts = explode(TokenGenerator::TOKEN_DELIMITER, $token);
		    $this->assertEquals($expectedSalts[$i],  $parts[0]);
		    $this->assertEquals($expectedTokens[$i], $parts[1]);
	    }
	}

}
