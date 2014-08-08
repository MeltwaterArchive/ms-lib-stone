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

/**
 * The request that we want to make to the HTTP server
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

class HttpClientRequest
{
    /**
     * The URL we are connecting to
     * @var HttpAddress
     */
    private $address;

    /**
     * the HTTP verb for this request
     * @var string
     */
    private $httpVerb = 'GET';

    /**
     * The HTTP transport type to use for this request.
     *
     * Valid values are:
     * - default => HttpDefaultTransport
     * - chunked => HttpChunkedTransport
     *
     * @var string
     */
    private $transportType = 'default';

    /**
     * Do we explicitly want to open a long-lived HTTP stream when
     * uploading?
     *
     * Set this by calling setIsStream()
     * @var boolean
     */
    private $isStream = false;

    /**
     * Do we want to force chunked-transport for upload (to mimic cURL's
     * behaviour when the payload is over 1024 bytes in length)?
     *
     * Set this by calling setPayloadIsLarge()
     *
     * @var boolean
     */
    private $considerPayloadLarge = false;

    /**
     * the data body to include in the request
     * @var array|string| null
     */
    private $body = array();

    /**
     * do we want HttpLib to be strict about handling how well the
     * remote end works with the Expects: header?
     *
     * Switch this to FALSE by calling disableStrictExpectsHandling().
     *
     * @var boolean
     */
    private $strictExpectsHandling = true;

    /**
     * import our standard HttpHeaders support, which is also used
     * in the HttpClientResponse
     */
    use HttpHeaders;

    /**
     * Constructor
     *
     * We need to know the URL we are connecting to
     *
     * @param HttpAddress|string $address
     */
    public function __construct($address)
    {
        // set our default headers
        $this->setHeaders([
            'Accept'        => 'text/html,application/xhtml,+xml,application/xml,application/json',
            'AcceptCharset' => 'utf-8',
            'Connection'    => 'keep-alive',
            'UserAgent'     => 'Hornet/6.6.6 (DataSift Hive) PHP/CLI (Hornet, like wasps only with evil intent)',
        ]);

        // parse the destination address
        //
        // this sets the 'Host' header roo
        $this->setAddress($address);
    }

    // ==================================================================
    //
    // HTTP verb support
    //
    // ------------------------------------------------------------------

    /**
     * get the HTTP verb that we're going to use when we send this
     * request to the HTTP server
     *
     * @return string
     */
    public function getHttpVerb()
    {
        return $this->httpVerb;
    }

    /**
     * set the HTTP verb that we're going to use when we send this
     * request to the HTTP server
     *
     * @param string $httpVerb
     *        a valid HTTP verb
     *
     * @return HttpClientRequest $this
     */
    public function setHttpVerb($httpVerb)
    {
        $this->httpVerb = strtoupper($httpVerb);

        return $this;
    }

    /**
     * Set the HTTP verb to use with this request
     *
     * @param  string $verb
     *         one of: GET, POST, PUT, DELETE
     * @return HttpClientRequest $this
     */
    public function withHttpVerb($verb)
    {
        // make sure that we store the verb in upper case
        $this->httpVerb = strtoupper($verb);

        // all done
        return $this;
    }

    // ==================================================================
    //
    // HTTP address support
    //
    // ------------------------------------------------------------------

    /**
     * What address are we connecting to?
     *
     * @return HttpAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the URL that this request is for
     *
     * @param mixed $address
     *        The address for this request.
     *        Can be string, can be HttpAddress.
     */
    public function setAddress($address)
    {
        if ($address instanceof HttpAddress)
        {
            $this->address = $address;
        }
        else
        {
            $this->address = new HttpAddress($address);
        }

        $this->setHeader('Host', $this->address->hostname);
    }

    // ==================================================================
    //
    // HTTP Headers API
    //
    // this is built on top of the HttpHeaders trait
    //
    // ------------------------------------------------------------------

    /**
     * Set an extra header to send with this request
     *
     * @param string $heading
     * @param string $value
     * @return HttpClientRequest $this
     */
    public function withExtraHeader($heading, $value)
    {
        return $this->setHeader($heading, $value);
    }

    /**
     * Set the user-agent to send with this request
     *
     * The default should be fine unless we need to pretend to be a specific
     * browser
     *
     * @param string $userAgent the user-agent string to send
     * @return HttpClientRequest $this
     */
    public function withUserAgent($userAgent)
    {
        return $this->setHeader('UserAgent', $userAgent);
    }

    // ==================================================================
    //
    // 1st line of request
    //
    // ------------------------------------------------------------------

    /**
     * Obtain the request line to send to the HTTP server
     *
     * @param string $httpVersion
     *        the HTTP version number to use in the request line
     * @return string
     */
    public function getRequestLine($httpVersion = '1.1')
    {
        return $this->httpVerb . ' ' . $this->address->getRequestLine() . ' HTTP/' . $httpVersion;
    }

    // =========================================================================
    //
    // Support for GET requests
    //
    // -------------------------------------------------------------------------

    /**
     * Set this request to be a GET request
     *
     * @return HttpClientRequest $this
     */
    public function asGetRequest()
    {
        return $this->withHttpVerb("GET");
    }

    /**
     * mark this request as a HTTP GET request
     *
     * @return HttpClientRequest $this
     */
    public function setGetRequest()
    {
        return $this->withHttpVerb("GET");
    }

    // =========================================================================
    //
    // Support for POST and PUT requests
    //
    // -------------------------------------------------------------------------

    /**
     * mark this request as a HTTP POST request
     *
     * @return HttpClientRequest $this
     */
    public function asPostRequest()
    {
        return $this->withHttpVerb("POST");
    }

    /**
     * mark this request as a HTTP POST request
     *
     * @return HttpClientRequest $this
     */
    public function setPostRequest()
    {
        return $this->withHttpVerb("POST");
    }

    /**
     * mark this request as a HTTP PUT request
     *
     * @return HttpClientRequest $this
     */
    public function asPutRequest()
    {
        return $this->withHttpVerb("PUT");
    }

    /**
     * mark this request as a HTTP PUT request
     *
     * @return HttpClientRequest $this
     */
    public function setPutRequest()
    {
        return $this->withHttpVerb("PUT");
    }

    /**
     * add a key/value pair to the request's body data
     *
     * @param string $name
     *        name of the key to add
     * @param string $value
     *        value of the data to add
     */
    public function addData($name, $value)
    {
        $this->body[$name] = $value;
    }

    /**
     * set the body data for this request
     *
     * @param string $payload
     *        the data to submit for this request
     * @return HttpClientRequest $this
     */
    public function withPayload($payload)
    {
        $this->body = $payload;
        return $this;
    }

    /**
     * set the body data for this request
     *
     * @param string $payload
     *        the data to submit for this request
     */
    public function setPayload($payload)
    {
        $this->body = $payload;
    }

    /**
     * get the body data for this request
     *
     * if the body data is an array of key/value pairs, we'll automatically
     * convert that into an encoded string suitable for submitting as a
     * POSTed form
     *
     * @return string
     */
    public function getBody()
    {
        if (is_array($this->body))
        {
            return $this->getEncodedBody();
        }
        else
        {
            return $this->body;
        }
    }

    /**
     * do we have a body for this request?
     *
     * @return boolean
     */
    public function hasBody()
    {
        // do we have anything at all?
        if (empty($this->body)) {
            return false;
        }

        // yes we do
        return true;
    }

    /**
     * get the body of the request, encoded for submitting as a POSTed
     * form
     *
     * @return string
     */
    public function getEncodedBody()
    {
        return http_build_query($this->body);
    }

    /**
     * Is this request an upload, or a download?
     *
     * @return boolean
     */
    public function getIsUpload()
    {
        // these verbs are allowed to upload data
        if ($this->httpVerb == 'POST' || $this->httpVerb == 'PUT') {
            return true;
        }

        // other verbs are not allowed to upload data
        return false;
    }

    /**
     * Is this request for an upload stream?
     *
     * @return boolean
     */
    public function getIsStream()
    {
        return $this->isStream;
    }

    /**
     * We want this upload to be forced to be a HTTP stream
     *
     * This changes the behaviour of POST and PUT requests only.
     * For GET requests, it is the remote end that controls whether the
     * request is a stream or not.
     */
    public function setIsStream()
    {
        $this->isStream = true;
        $this->transportType = 'chunked';
    }

    /**
     * Does this request want the payload to treated as 'large'?
     *
     * By 'large', we mean do we want to force the upload to be encoded
     * used chunked-transport, to mimic cURL's behaviour?
     *
     * @return boolean
     */
    public function getIsPayloadLarge()
    {
        return $this->considerPayloadLarge;
    }

    /**
     * We want this upload to be forced over chunked-transport
     *
     * Use this when you want to mimic cURL's behaviour.  We never enable
     * this by default - that's a policy decision, which is owned by the
     * caller!
     */
    public function setPayloadIsLarge()
    {
        $this->considerPayloadLarge = true;
    }

    /**
     * We want this upload to mimic cURL's behaviour when the remote
     * server fails to issue a 100-Continue response in the time allowed
     *
     * You should only do this if you have an explicit test case that
     * needs this behaviour.  Otherwise, stick with our default behaviour
     * (which is to treat missing 100-Continue responses as an error) so
     * that your tests do not hide any potential problems in the HTTP
     * service under test
     *
     * @return void
     */
    public function disableStrictExpectsHandling()
    {
        $this->strictExpectsHandling = false;
    }

    /**
     * Do we expect the HttpClient to be strict about the behaviour of
     * the Expects: header and the 100-Continue response it should trigger?
     *
     * @return boolean
     */
    public function getStrictExpectsHandling()
    {
        return $this->strictExpectsHandling;
    }

    // =========================================================================
    //
    // Support for DELETE requests
    //
    // -------------------------------------------------------------------------

    /**
     * mark this request as being a HTTP DELETE request
     *
     * @return HttpClientRequest $this
     */
    public function asDeleteRequest()
    {
        return $this->withHttpVerb("DELETE");
    }
}
