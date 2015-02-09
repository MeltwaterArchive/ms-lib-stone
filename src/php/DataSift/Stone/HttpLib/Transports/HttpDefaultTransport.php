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

namespace DataSift\Stone\HttpLib\Transports;

use Exception;
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\HttpLib\HttpClientConnection;
use DataSift\Stone\HttpLib\HttpClientRequest;
use DataSift\Stone\HttpLib\HttpClientResponse;

/**
 * Support for dealing with content that is not chunked
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpDefaultTransport extends HttpTransport
{
    // ==================================================================
    //
    // Support for sending content
    //
    // ------------------------------------------------------------------

    /**
     * send one or more lines of content to the remote HTTP server
     *
     * NOTE: do NOT use this to send HTTP headers!!
     *
     * @param  HttpClientConnection $connection
     *         our connection to the remote server
     * @param  array|string $payload
     *         the content to send (MUST BE CRLF terminated)
     * @return void
     */
    public function sendContent(HttpClientConnection $connection, $payload)
    {
        if (!$connection->isConnected()) {
            return;
        }

        if (is_array($payload)) {
            foreach ($payload as $line) {
                $connection->send($line);
            }
        }
        else {
            $connection->send($payload);
        }
    }

    // ==================================================================
    //
    // Support for receiving content
    //
    // ------------------------------------------------------------------

    /**
     * Read data from the connection
     *
     * @param HttpClientConnection $connection our connection to the HTTP server
     * @param HttpClientResponse $response where we put the results
     * @return mixed null on error, otherwise the size of the content read
     */
    public function readContent(HttpClientConnection $connection, HttpClientResponse $response, $isStream = false)
    {
        // cannot read if we do not have an open socket
        if (!$connection->isConnected())
        {
            $response->addError("readContent", "not connected");
            return null;
        }

        // how much content do we expect to read?
        //
        // this may be NULL
        $expectedLen = $response->getExpectedContentLength();

        // retrieve the body
        $body = '';
        do
        {
            if ($expectedLen !== null) {
                $remainingLen = $expectedLen - strlen($body);
            }
            else {
                $remainingLen = null;
            }
            $body .= $connection->readLine($remainingLen);
            // var_dump($body);

            // keep count of how much data we've read
            $response->bytesRead += strlen($body);
        }
        while (!$connection->feof() && ($expectedLen && (strlen($body) < $expectedLen)));

        // stash the retrieved body in the response
        $response->decodeBody($body);
        $chunkSize = strlen($body);

        // how many bodies have we received?
        // $context->stats->increment('response.body');

        // does the connection need to close?
        if ($response->connectionMustClose())
        {
            $connection->disconnect();
        }

        // all done
        return $chunkSize;
    }
}
