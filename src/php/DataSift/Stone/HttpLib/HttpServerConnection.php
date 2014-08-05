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
 * Represents a connection received by our simple HttpServer
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpServerConnection
{
    /**
     * The TCP socket we accept incoming connection requests from
     * @var resource
     */
    protected $socket;

    // import the correct line ending to use in HTTP headers
    use HttpLineEndings;

    /**
     * constructor
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
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
     * read the first line from the request socket
     *
     * @return string
     *         the first line of the (possibly) HTTP request
     */
    public function readRequest()
    {
        $request = socket_read($this->socket, 2048, PHP_NORMAL_READ);
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
        return $request;
    }

    public function sendReply()
    {
        // TBD
    }

    /**
     * Ensure all data has been sent
     */
    public function completeResponse()
    {
        // send the EOF notification
        socket_send($this->socket, '', 0, MSG_EOF);
        socket_close($this->socket);
    }
}
