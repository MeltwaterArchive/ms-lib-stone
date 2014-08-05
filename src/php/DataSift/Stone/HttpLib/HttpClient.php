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

use DataSift\Stone\HttpLib\Transports\HttpDefaultTransport;
use DataSift\Stone\HttpLib\Transports\HttpChunkedTransport;
use DataSift\Stone\HttpLib\Transports\WsTransport;

/**
 * An effective URL client with detailed metrics built in
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class HttpClient
{
    /**
     * Our connection to the (probably remote) http server
     *
     * @var HttpClientConnection
     */
    protected $connection = null;

    /**
     * The helper library for working with the connection
     *
     * @var HttpTransport
     */
    protected $transport  = null;

    /**
     * A statsd client to log timing data to
     *
     * @var object
     */
    protected $statsdClient = null;

    // ==================================================================
    //
    // Support for making the request
    //
    // ------------------------------------------------------------------

    /**
     * Make a request to the HTTP server
     *
     * @param HttpClientRequest $request
     * @return HttpClientResponse|HttpStreamClient
     */
    public function newRequest(HttpClientRequest $request)
    {
        // before anything else, we need to connect to the (possibly)
        // remote server
        $this->connection = $this->getConnectionTo($request->getAddress());

        // pick our transport
        $this->transport = $this->getInitialTransport($request);

        // make the request
        $response = $this->transport->sendRequest($this->connection, $request);

        // special case - we need a response and then we'll (maybe) send
        // some more
        if ($request->getIsUpload() && $request->hasHeaderWithValue('Expect', '100-Continue')) {
            try {
                // we need a response back inside 1 second
                // this mimics the behaviour hard-coded into the
                // internals of cURL
                //
                // all of our testing is based around making sure our
                // services work well with cURL, because it is the
                // de-facto HTTP client of many languages
                $response = $this->transport->readResponse($this->connection, $request, 1);
            }
            catch (E5xx_HttpReadTimeout $e) {
                // at this point, cURL would just send the data anyway
                //
                // however, we're much stricter, because we're primarily
                // a library for use in tests, and we do not want to hide
                // anything that is potentially a symptom of a problem
                // in the HTTP service that we are trying to test
                if ($request->getStrictExpectsHandling()) {
                    throw new E5xx_100ContinueMissing();
                }
            }

            // we *probably* have a response - is it the one we wanted?
            if (isset($response->statusCode) && ($response->statusCode != 100)) {
                // no, it isn't what we wanted
                return $response;
            }

            // at this point, either the remote end has told us that it is
            // ready to accept data, or we have decided that we're going
            // to send the data anyway
            //
            // do we have any to send?
            if ($request->getIsStream()) {
                // no - we are now a stream
                return $response;
            }

            // yes, we do
            $this->transport->sendBody($this->connection, $request);
        }
        else {
            // send the payload too
            $this->transport->sendBody($this->connection, $request);

            // listen for a response with no timeout
            $response = $this->transport->readResponse($this->connection, $request);
        }

        // at this point, we have read all of the headers sent back to us
        //
        // do we need to switch transports?
        if ($response->transferIsChunked())
        {
            $this->transport = new HttpChunkedTransport();
        }

        // now, do we have any valid content to read?
        if ($response->type && !$response->hasErrors())
        {
            $this->transport->readContent($this->connection, $response);
        }

        // now that we have our content, do we have chunks to recombine?
        $response->combineChunksIfRequired();

        // return the results
        return $response;
    }

    /**
     * Use the request to decide which HttpTransport we need to use to
     * make our request
     *
     * @param  HttpClientRequest $request
     * @return HttpTransport
     */
    protected function getInitialTransport(HttpClientRequest $request)
    {
        // are we using websockets?
        $scheme = $request->getAddress()->scheme;
        if ($scheme == 'ws' || $scheme == 'wss') {
            return new WsTransport();
        }

        // are we dealing with the general case?
        if (!$request->getIsUpload()) {
            // go with the basics, and let the remote end tell us if
            // things need to change
            return new HttpDefaultTransport();
        }

        // special cases start here ...
        if ($request->getIsStream() || $request->getIsPayloadLarge()) {
            // the request wants us to use chunked-transport
            return new HttpChunkedTransport();
        }

        // at this point, we've run out of special cases to consider
        return new HttpDefaultTransport();
    }

    // ==================================================================
    //
    // Support for reading / writing with the remote server
    //
    // ------------------------------------------------------------------

    /**
     * Read more data from an existing HTTP connection
     *
     * @param HttpClientResponse $response
     * @return boolean false if there was no more data, true otherwise
     */

    public function readContent(HttpClientResponse $response)
    {
        // do we have an open connection?
        if (!isset($this->connection))
        {
            return null;
        }
        if (!$this->connection->isConnected())
        {
            // we are not connected
            return null;
        }

        // if we get here, we are connected
        $response->resetForNextResponse();
        return $this->transport->readContent($this->connection, $response);
    }

    /**
     * Send data to an existing HTTP connection
     *
     * @param string $data
     *      The data to send
     */
    public function sendContent($data)
    {
        // do we have an open connection?
        if (!isset($this->connection) || !$this->connection->isConnected())
        {
            throw new E4xx_NoHttpConnection();
        }

        // send the data
        $this->transport->sendContent($this->connection, $data);
    }

    public function doneSendingContent()
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            throw new E4xx_NoHttpConnection();
        }

        $this->transport->doneSendingContent($this->connection);

        // TODO: get any response from the server

        // all done
        $this->connection->waitForServerClose();
        $this->connection = null;
    }

    public function closeStream()
    {
        if (!$this->connection || !$this->connection->isConnected()) {
            throw new E4xx_NoHttpConnection();
        }

        $this->transport->doneSendingContent($this->connection);

        // all done
        $this->connection->waitForServerClose();
        $this->connection = null;
    }

    // =========================================================================
    //
    //  Additional connect/disconnect support
    //
    // -------------------------------------------------------------------------

    /**
     * Are we currently connected?
     * @return boolean
     */
    public function isConnected()
    {
        if (!$this->connection instanceof HttpClientConnection)
        {
            return false;
        }

        return $this->connection->isConnected();
    }

    /**
     * create a HttpClientConnection to a HTTP server
     *
     * if we already have a connection to this server, we will reuse it
     *
     * @param  HttpAddress $address
     * @return HttpClientConnection
     */
    protected function getConnectionTo(HttpAddress $address)
    {
        // are we already connected?
        if ($this->connection instanceof HttpClientConnection) {
            if ($this->connection->isConnectedTo($address)) {
                // we're already connected
                return $this->connection;
            }

            // if we get here, we're connected to somewhere else
            // we need to close that connection so that we can
            // start a new one
            $this->disconnect();
        }

        // if we get here, we need to make a new connection
        $connection = new HttpClientConnection();
        if (!$connection->connect($address, 5))
        {
            throw new E5xx_HttpConnectFailed((string)$address, "error information not available");
        }

        // at this point, we have successfully connected to $address,
        // and we are ready to send our request to the (possibly) remote
        // HTTP server!
        return $connection;
    }

    /**
     * Disconnect from the remote server.
     *
     * If we are not currently connected, do nothing
     */
    public function disconnect()
    {
        if ($this->connection->isConnected())
        {
            $this->transport->close($this->connection);
            $this->connection->disconnect();
        }
    }

    /**
     * Get the current socket from the underlying connection
     *
     * @return resource
     *         the TCP/IP socket
     */
    public function getSocket()
    {
        return $this->connection->getSocket();
    }
}
