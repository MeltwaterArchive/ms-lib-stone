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
 * Test class for HttpServer class
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpServerTest extends PHPUnit_Framework_Testcase
{
	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::__construct
	 */
	public function testCanInstantiate()
	{
	    // ----------------------------------------------------------------
	    // perform the change

	    $obj = new HttpServer();

	    // ----------------------------------------------------------------
	    // test the results

	    $this->assertTrue($obj instanceof HttpServer);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::startServer
	 *
	 * @expectedException PHPUnit_Framework_Error
	 */
	public function testCanStart()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj  = new HttpServer();
	    // we need a random-ish port to allow time for the kernel to
	    // close the damn thing after the last test we wrote
	    $port = 4099 + (time() % 120);

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->startServer($port);

	    // ----------------------------------------------------------------
	    // test the results

	    // if the server is running, then we should be unable to open
	    // the port ourselves
	    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	    $this->assertTrue(is_resource($socket));

	    // PHPUnit converts this to an exception if it fails
	    // we expect it to fail!
	    socket_bind($socket, '0.0.0.0', $port);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::startServer
	 * @covers DataSift\Stone\HttpLib\E5xx_HttpServerCannotStart::__construct
	 * @expectedException DataSift\Stone\HttpLib\E5xx_HttpServerCannotStart
	 */
	public function testThrowsExceptionWhenCannotStart()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj  = new HttpServer();
	    // we need a random-ish port to allow time for the kernel to
	    // close the damn thing after the last test we wrote
	    $port = 4199 + (time() % 120);

	    // to stop the server running, we need to open the port that it
	    // is going to try and use
	    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	    $this->assertTrue(is_resource($socket));

	    // PHPUnit converts this to an exception if it fails
	    socket_bind($socket, '0.0.0.0', $port);

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->startServer($port);

	    // ----------------------------------------------------------------
	    // test the results

	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::startServer
	 * @covers DataSift\Stone\HttpLib\HttpServer::waitForRequest
	 * @covers DataSift\Stone\HttpLib\HttpServer::listenForConnections
	 * @covers DataSift\Stone\HttpLib\HttpServer::acceptInboundConnection
	 * @covers DataSift\Stone\HttpLib\HttpServer::readRequestFromSocket
	 */
	public function testCanListenForHttpRequest()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj  = new HttpServer();
	    // we need a random-ish port to allow time for the kernel to
	    // close the damn thing after the last test we wrote
	    $port = 4399 + (time() % 120);

	    $obj->startServer($port);

	    // our HTTP request line to send
    	$expectedRequest = "GET /hello_world HTTP/1.1\r\n";

	    // ----------------------------------------------------------------
	    // perform the change

	    $pid = pcntl_fork();
	    if (!$pid) {
	    	// we are the child process
	    	// we are going to write to the HTTP port, then quit
	    	usleep(100);
	    	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	    	socket_connect($socket, '127.0.0.1', $port);
	    	socket_write($socket, $expectedRequest, strlen($expectedRequest));
	    	exit(0);
	    }

	    // if we get here, we are the original PHPUnit test
	    list($requestSocket, $actualRequest) = $obj->waitForRequest();

	    // ----------------------------------------------------------------
	    // test the results
	 	$this->assertTrue(is_resource($requestSocket));

	 	// the $actualRequest is missing the carriage-return because of
	 	// the way we need to read from the socket in the HttpServer
	 	$this->assertEquals(trim($expectedRequest), trim($actualRequest));
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::waitForRequest
	 * @covers DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept::__construct
	 * @expectedException DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept
	 */
	public function testThrowsExceptionWhenServerNotStartedFirst()
	{
	    // ----------------------------------------------------------------
	    // setup your test
	    //
	    // we need a server that has NOT been started
	    // this will ensure there isn't a valid socket to listen() on

	    $obj = new HttpServer();

	    // ----------------------------------------------------------------
	    // perform the change
	    //
	    // an exception is thrown here

	    $obj->waitForRequest();
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::listenForConnections
	 * @covers DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept::__construct
	 * @expectedException DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept
	 */
	public function testThrowsExceptionWhenCannotListenForInboundConnections()
	{
	    // ----------------------------------------------------------------
	    // setup your test
	    //
	    // we need a server that has a socket, but a socket that has
	    // been connect()ed rather than bind()ed
	    //
	    // this will trigger the error states that we are interested in

	    $obj = new HttpServerWrapper();
	    $obj->createConnectSocket();

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->listenForConnectionsWrapper();
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::acceptInboundConnection
	 * @covers DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept::__construct
	 * @expectedException DataSift\Stone\HttpLib\E5xx_HttpServerCannotAccept
	 */
	public function testThrowsExceptionWhenCannotAcceptInboundConnections()
	{
	    // ----------------------------------------------------------------
	    // setup your test
	    //
	    // we need a server that has a socket, but a socket that has
	    // been connect()ed rather than bind()ed
	    //
	    // this will trigger the error states that we are interested in

	    $obj = new HttpServerWrapper();
	    $obj->createConnectSocket();

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->acceptInboundConnectionWrapper();
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::startServer
	 * @covers DataSift\Stone\HttpLib\HttpServer::waitForRequest
	 * @covers DataSift\Stone\HttpLib\HttpServer::completeResponse
	 */
	public function testCanReply()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj  = new HttpServer();
	    // we need a random-ish port to allow time for the kernel to
	    // close the damn thing after the last test we wrote
	    $port = 4599 + (time() % 120);

	    $obj->startServer($port);

	    // our HTTP request line to send
    	$expectedRequest = "GET /hello_world HTTP/1.1\r\n";

    	// our reply to send
    	$expectedReply = "HTTP/1.1 200 Okay" . $obj->EOL
    	               . "Server: UnitTest" . $obj->EOL
    	               . $obj->EOL
    	               . json_encode(['port' => $port]) . $obj->EOL;

    	// we need some IPC going on here
    	//
    	// our parent process will use $ipcSockets[0]
    	// our child process will use $ipcSockets[1]
		$domain = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX);
		socket_create_pair($domain, SOCK_STREAM, 0, $ipcSockets);

	    // ----------------------------------------------------------------
	    // perform the change

	    $pid = pcntl_fork();
	    if (!$pid) {
	    	// we are the child process
	    	usleep(100);

	    	$httpSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	    	socket_connect($httpSocket, '127.0.0.1', $port);
	    	socket_write($httpSocket, $expectedRequest, strlen($expectedRequest));
		    $reply = socket_read($httpSocket, 2048);
		    socket_close($httpSocket);

		    socket_write($ipcSockets[1], $reply, strlen($reply));
		    exit(0);
	    }

	    // if we get here, we are the original PHPUnit test
	    list($requestSocket, $actualRequest) = $obj->waitForRequest();
	 	$this->assertTrue(is_resource($requestSocket));

	 	// the $actualRequest is missing the carriage-return because of
	 	// the way we need to read from the socket in the HttpServer
	 	$this->assertEquals(trim($expectedRequest), trim($actualRequest));

	 	// send the response
	 	socket_write($requestSocket, $expectedReply, strlen($expectedReply));
	 	$obj->completeResponse($requestSocket);

	 	// tidy up
	 	$obj->stopServer();

	    // ----------------------------------------------------------------
	    // test the results

	 	// find out what was send to our child process
	 	$actualReply = socket_read($ipcSockets[0], 2048);

	 	// did we get what we expected to get?
	 	$this->assertEquals($expectedReply, $actualReply);
	}

	/**
	 * @covers DataSift\Stone\HttpLib\HttpServer::stopServer
	 */
	public function testCanStop()
	{
	    // ----------------------------------------------------------------
	    // setup your test

	    $obj = new HttpServer();
	    // we need a random-ish port to allow time for the kernel to
	    // close the damn thing after the last test we wrote
	    $port = 4799 + (time() % 120);

	    $obj->startServer($port);

	    // ----------------------------------------------------------------
	    // perform the change

	    $obj->stopServer();

	    // ----------------------------------------------------------------
	    // test the results

	    // I have no idea how to prove that the server has closed the
	    // socket
	    //
	    // for now, I'm going to assume that no errors / exceptions
	    // means success
	}

}