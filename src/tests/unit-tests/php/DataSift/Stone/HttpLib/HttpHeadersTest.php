<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\HttpLib;

use PHPUnit_Framework_Testcase;
use stdClass;

/**
 * Test class for HttpHeaders trait
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpHeadersTest extends PHPUnit_Framework_Testcase
{
	public function testCanUseTrait()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedTraits = ['DataSift\Stone\HttpLib\HttpHeaders' => 'DataSift\Stone\HttpLib\HttpHeaders'];

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new HttpHeadersWrapper();

	    // ----------------------------------------------------------------
	    // test the results

	    $actualTraits = class_uses(get_class($obj));
	    $this->assertEquals($expectedTraits, $actualTraits);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeaders
	 */
	public function testStartsWithNoHeaders()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals([], $obj->getHeaders());
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::setHeader
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::hasHeaderCalled
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeaderCalled
	 */
	public function testCanSetAHeader()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $expectedValue = 'value1';

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->setHeader('Test1', $expectedValue);

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj->hasHeaderCalled('Test1'));
	    $actualValue = $obj->getHeaderCalled('Test1');
	    $this->assertEquals($expectedValue, $actualValue);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeaders
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::setHeaders
	 */
	public function testCanSetHeaders()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->setHeaders($expectedHeaders);

	    // ----------------------------------------------------------------
	    // test the results

	    $actualHeaders = $obj->getHeaders();
	    $this->assertEquals($expectedHeaders, $actualHeaders);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeadersString
	 */
	public function testCanGetHeadersAsStringForTransmission()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();

	    $headers = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $expectedHeaders = '';
	    foreach ($headers as $key => $value) {
	    	$expectedHeaders .= $key . ': ' . $value . "\r\n";
	    }

	    $obj->setHeaders($headers);

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualHeaders = $obj->getHeadersString();

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedHeaders, $actualHeaders);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::hasHeaderCalled
	 */
	public function testCanTestForHeader()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $obj->setHeaders($expectedHeaders);
	    $this->assertEquals($expectedHeaders, $obj->getHeaders());

	    // ----------------------------------------------------------------
	    // test the results
	    //
	    // header names are case-insensitive!

	    $this->assertTrue($obj->hasHeaderCalled('Content-Length'));
	    $this->assertTrue($obj->hasHeadercalled('CONTENT-LENGTH'));
	    $this->assertTrue($obj->hasHeaderCalled('content-length'));

	    // these headers do not exist
	    $this->assertFalse($obj->hasHeaderCalled('Accept'));
	    $this->assertFalse($obj->hasHeaderCalled('Content-Type'));
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::hasHeaderWithValue
	 */
	public function testCanTestForHeaderAndValue()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $obj->setHeaders($expectedHeaders);
	    $this->assertEquals($expectedHeaders, $obj->getHeaders());

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj->hasHeaderWithValue('Content-Length', 500));
	    $this->assertTrue($obj->hasHeaderWithValue('CONTENT-LENGTH', 500));
	    $this->assertTrue($obj->hasHeaderWithValue('content-length', 500));

	    // these headers do not have this value
	    $this->assertFalse($obj->hasHeaderWithValue('Content-Length', 501));
	    $this->assertFalse($obj->hasHeaderWithValue('CONTENT-LENGTH', 501));
	    $this->assertFalse($obj->hasHeaderWithValue('content-length', 501));

	    // these headers do not exist
	    $this->assertFalse($obj->hasHeaderWithValue('Accept', 'application/json'));
	    $this->assertFalse($obj->hasHeaderWithValue('Content-Type', 'application/json'));
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeaderCalled
	 */
	public function testCanGetHeader()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $obj->setHeaders($expectedHeaders);
	    $this->assertEquals($expectedHeaders, $obj->getHeaders());

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals(500, $obj->getHeaderCalled('Content-Length'));
	    $this->assertEquals(500, $obj->getHeaderCalled('CONTENT-LENGTH'));
	    $this->assertEquals(500, $obj->getHeaderCalled('content-length'));
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::getHeaderCalled
	 */
	public function testReturnsNullWhenHeaderDoesNotExist()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $obj->setHeaders($expectedHeaders);
	    $this->assertEquals($expectedHeaders, $obj->getHeaders());

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertNull($obj->getHeaderCalled('ThisIsNotARealHeader'));
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpHeaders::resetHeaders
	 */
	public function testCanResetListOfHeaders()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpHeadersWrapper();
	    $this->assertEquals([], $obj->getHeaders());

	    $expectedHeaders = [
	    	"Content-Length" => 500,
	    	"Transfer-Encoding" => "chunked"
	    ];
	    $obj->setHeaders($expectedHeaders);
	    $this->assertEquals($expectedHeaders, $obj->getHeaders());

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->resetHeaders();

	    // ----------------------------------------------------------------
	    // test the results

	    // prove that the main store of headers is empty
	    $this->assertEquals([], $obj->getHeaders());

	    // prove that the 'hidden' case-insensitive list of headers
	    // is also empty
	    $this->assertFalse($obj->hasHeaderCalled('Content-Length'));
	    $this->assertFalse($obj->hasHeaderCalled('CONTENT-LENGTH'));
	    $this->assertFalse($obj->hasHeaderCalled('content-length'));
	}

}