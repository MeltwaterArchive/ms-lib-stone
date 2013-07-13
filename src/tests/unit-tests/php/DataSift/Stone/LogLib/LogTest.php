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
 * @package   Stone/LogLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\LogLib;

use PHPUnit_Framework_Testcase;
use stdClass;

/**
 * A static proxy around the underlying logger
 *
 * @category  Libraries
 * @package   Stone/LogLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class LogTest extends PHPUnit_Framework_Testcase
{
	/**
	 * @covers DataSift\Stone\LogLib\Log::getLevelFromName
	 */
	public function testCanConvertLevelToName()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedLevels = array(
		    "EMERGENCY" => Log::LOG_EMERGENCY,
		    "ALERT"     => Log::LOG_ALERT,
		    "CRITICAL"  => Log::LOG_CRITICAL,
		    "ERROR"     => Log::LOG_ERROR,
		    "WARNING"   => Log::LOG_WARNING,
		    "NOTICE"    => Log::LOG_NOTICE,
		    "INFO"      => Log::LOG_INFO,
		    "DEBUG"     => Log::LOG_DEBUG,
		    "TRACE"     => Log::LOG_TRACE,
	    );
	    $actualLevels = array();

	    // ----------------------------------------------------------------
	    // perform the change

	    foreach ($expectedLevels as $name => $dummy) {
	    	$actualLevels[$name] = Log::getLevelFromName($name);
	    }

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedLevels, $actualLevels);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForEmergencyLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => false,
		    'CRITICAL'  => false,
		    'ERROR'     => false,
		    'WARNING'   => false,
		    'NOTICE'    => false,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_EMERGENCY);
	    $actualMask2 = Log::getMaskForMinLevel('EMERGENCY');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForAlertLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => false,
		    'ERROR'     => false,
		    'WARNING'   => false,
		    'NOTICE'    => false,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_ALERT);
	    $actualMask2 = Log::getMaskForMinLevel('ALERT');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForCriticalLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => false,
		    'WARNING'   => false,
		    'NOTICE'    => false,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_CRITICAL);
	    $actualMask2 = Log::getMaskForMinLevel('CRITICAL');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForErrorLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => false,
		    'NOTICE'    => false,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_ERROR);
	    $actualMask2 = Log::getMaskForMinLevel('ERROR');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForWarningLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => true,
		    'NOTICE'    => false,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_WARNING);
	    $actualMask2 = Log::getMaskForMinLevel('WARNING');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForNoticeLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => true,
		    'NOTICE'    => true,
		    'INFO'      => false,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_NOTICE);
	    $actualMask2 = Log::getMaskForMinLevel('NOTICE');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForInfoLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => true,
		    'NOTICE'    => true,
		    'INFO'      => true,
		    'DEBUG'     => false,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_INFO);
	    $actualMask2 = Log::getMaskForMinLevel('INFO');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForDebugLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => true,
		    'NOTICE'    => true,
		    'INFO'      => true,
		    'DEBUG'     => true,
		    'TRACE'     => false,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_DEBUG);
	    $actualMask2 = Log::getMaskForMinLevel('DEBUG');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	    $this->assertNotSame($actualMask1, $actualMask2);
	}

	/**
	 * @covers DataSift\Stone\LogLib\Log::getMaskForMinLevel
	 */
	public function testCanGetMaskForTraceLevel()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $expectedMask = (object)array(
		    'EMERGENCY' => true,
		    'ALERT'     => true,
		    'CRITICAL'  => true,
		    'ERROR'     => true,
		    'WARNING'   => true,
		    'NOTICE'    => true,
		    'INFO'      => true,
		    'DEBUG'     => true,
		    'TRACE'     => true,
	    );

	    // ----------------------------------------------------------------
	    // perform the change

	    $actualMask1 = Log::getMaskForMinLevel(Log::LOG_TRACE);
	    $actualMask2 = Log::getMaskForMinLevel('TRACE');

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertEquals($expectedMask, $actualMask1);
	    $this->assertEquals($expectedMask, $actualMask2);
	    $this->assertNotSame($actualMask1, $actualMask2);
	}
}