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

use Exception;

/**
 * A simple HTTP server for use in data publishers
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpServer
{
    /**
     * The TCP socket we accept incoming connection requests from
     * @var resource
     */
    protected $socket;

    /**
     * The port number that we listen to incoming connection requests on
     * @var int
     */
    protected $listeningPort;

    // import the correct line ending to use in HTTP headers
    use HttpLineEndings;

    /**
     * constructor
     */
    public function __construct()
    {
        // does nothing for now
    }

    /**
     * Start the HTTP server
     *
     * We open the socket that we will listen on.  Any problems, we will throw
     * an Exception
     *
     * @param int $port the port we are going to listen on
     */
    public function startServer($port)
    {
        $this->listeningPort = $port;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false)
        {
            // @codeCoverageIgnoreStart
            throw new HttpServerCannotStart("Unable to create TCP/IP socket; error is " . socket_strerror(socket_last_error($this->socket)));
            // @codeCoverageIgnoreEnd
        }

        // we need to install a dummy error handler, otherwise the
        // global one will kick in if socket_bind() fails
        //
        // this is needed for PHPUnit testing
        set_error_handler([$this, 'dummyLegacyErrorHandler']);
        $result = socket_bind($this->socket, '0.0.0.0', $port);
        restore_error_handler();

        if ($result === false)
        {
            throw new E5xx_HttpServerCannotStart("Unable to open TCP/IP socket on port $port; error is " . socket_strerror(socket_last_error($this->socket)));
        }
    }

    /**
     * used to work around global error handlers that interfere with the
     * exceptions we want to throw
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function dummyLegacyErrorHandler()
    {
        // do nothing
    }

    /**
     * Listen on our (already open) socket, and wait for a HTTP client to
     * connect and ask for data
     *
     * @return array(socket, request) the socket to talk to the client on, and
     *         the request that the client has made
     */
    public function waitForRequest()
    {
        // do we actually *have* a socket to listen on?
        if (!is_resource($this->socket)) {
            throw new E5xx_HttpServerCannotAccept("Please call HttpServer::startServer() first!");
        }

        // yes we do
        $this->listenForConnections();

        // wait for an actual connection
        $requestSocket = $this->acceptInboundConnection();

        // what is the request's first line?
        return $this->readRequestFromSocket($requestSocket);
    }

    /**
     * put our server socket into listen mode
     *
     * @return void
     */
    protected function listenForConnections()
    {
        // we need to install a dummy error handler, otherwise the
        // global one will kick in if socket_listen() fails
        //
        // this is needed for PHPUnit testing
        set_error_handler([$this, 'dummyLegacyErrorHandler']);

        // socket_listen() can return NULL as well as FALSE
        $result = socket_listen($this->socket, 1);

        // put the error handler back
        restore_error_handler();

        // what happened?
        if (!$result)
        {
            throw new E5xx_HttpServerCannotAccept("Unable to listen on port " . $this->listeningPort . "; error is " . socket_strerror(socket_last_error($this->socket)));
        }
    }

    /**
     * wait for an actual inbound connection
     *
     * @return resource the socket that the request is on
     */
    protected function acceptInboundConnection()
    {
        // we need to install a dummy error handler, otherwise the
        // global one will kick in if socket_accept() fails
        //
        // this is needed for PHPUnit testing
        set_error_handler([$this, 'dummyLegacyErrorHandler']);
        $requestSocket = socket_accept($this->socket);
        restore_error_handler();

        // what happened?
        if ($requestSocket === false)
        {
            throw new E5xx_HttpServerCannotAccept("Unable to accept incoming TCP/IP connection to port $this->listeningPort; error is " . socket_strerror(socket_last_error($this->socket)));
        }

        // mark this socket as blocking, and to hand around until all
        // data is transmitted
        socket_set_block($requestSocket);
        $linger = array('l_linger' => 1, 'l_onoff' => 1);
        socket_set_option($requestSocket, SOL_SOCKET, SO_LINGER, $linger);

        // all done
        return $requestSocket;
    }

    /**
     * read the first line from the request socket
     *
     * @param  resource $requestSocket
     *         the socket to read from
     * @return array(resource, string)
     *         the $requestSocket, and the string read from the socket
     */
    protected function readRequestFromSocket($requestSocket)
    {
        $request = socket_read($requestSocket, 2048, PHP_NORMAL_READ);
        if ($request === false)
        {
            // I can't think of a legit way to make socket_read() fail
            // at this point for unit testing purposes
            //
            // @codeCoverageIgnoreStart
            throw new E5xx_HttpServerCannotRead("Unable to read from TCP/IP socket; error is " . socket_strerror(socket_last_error($this->socket)));
            // @codeCoverageIgnoreEnd
        }

        // if we get here, we think we have a request
        return array($requestSocket, $request);
    }

    /**
     * Ensure all data has been sent
     * @param socket $requestSocket
     */
    public function completeResponse($requestSocket)
    {
        // send the EOF notification
        socket_send($requestSocket, '', 0, MSG_EOF);
        // socket_close($requestSocket);
    }

    /**
     * Stop listening for more client connections
     */
    public function stopServer()
    {
        socket_close($this->socket);
    }
}
