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
 * Test class for HttpAddress class
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpAddressTest extends PHPUnit_Framework_Testcase
{
	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 */
	public function testCanInstantiate()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('http://localhost');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj instanceof HttpAddress);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\E4xx_InvalidUrl::__construct
	 *
	 * @expectedException DataSift\Stone\HttpLib\E4xx_InvalidUrl
	 */
	public function testMustInstantiateWithValidUrl()
	{
	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new HttpAddress('');
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\HttpAddress::postProcessSetAddressHttp
	 */
	public function testHttpUrlsDefaultToPort80()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('http://localhost');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals(80, $obj->port);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\HttpAddress::postProcessSetAddressHttps
	 */
	public function testHttpsUrlsDefaultToPort443()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('https://localhost');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals(443, $obj->port);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\HttpAddress::postProcessSetAddressWs
	 */
	public function testWsUrlsDefaultToPort80()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('ws://localhost');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals(80, $obj->port);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\HttpAddress::postProcessSetAddressWss
	 */
	public function testWssUrlsDefaultToPort443()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('wss://localhost');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals(443, $obj->port);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::getRequestLine
	 */
	public function testCanGetHttpRequestFirstLine()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpAddress('http://localhost:4000/index.html?page=1#alfred');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals('/index.html?page=1#alfred', $obj->getRequestLine());
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__construct
	 * @covers DataSift\Stone\HttpLib\HttpAddress::setAddress
	 * @covers DataSift\Stone\HttpLib\HttpAddress::__toString
	 */
	public function testCanCastAsString()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedUrl = 'http://localhost';

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new HttpAddress($expectedUrl);

	    // ----------------------------------------------------------------
	    // test the results

	    $actualUrl = (string)$obj;
	    $this->assertEquals($expectedUrl, $actualUrl);
	}

}