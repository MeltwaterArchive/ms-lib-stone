<?php

/**
 * Stone - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\HttpLib;

/**
 * The request that we want to make
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
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
     * The list of headers to send with this request
     * @var array
     */

    public $headers = array(
        'Accept'            => 'text/html,application/xhtml,+xml,application/xml,application/json',
        'AcceptEncoding'    => 'none',
        'AcceptCharset'     => 'utf-8',
        'Connection'        => 'keep-alive',
        'UserAgent'         => 'Hornet/6.6.6 (DataSift Hive) PHP/CLI (Hornet, like wasps only with evil intent)',
    );

    /**
     * THe headers to send with this request, as a single string for efficiency
     * @var string
     */
    private $headersString = null;

    /**
     * the data body to include in the request
     * @var array|string| null
     */
    private $body = array();

    /**
     * Constructor
     *
     * We need to know the URL we are connecting to
     *
     * @param type $addressString
     */
    public function __construct($address)
    {
        $this->setAddress($address);
    }

    public function getHttpVerb()
    {
        return $this->httpVerb;
    }

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
     *      The address for this request. Can be string, can be HttpAddress
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

        $this->headers['Host'] = $this->address->hostname;
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

    /**
     * Set an extra header to send with this request
     *
     * @param string $heading
     * @param string $value
     * @return HttpClientRequest $this
     */
    public function withExtraHeader($heading, $value)
    {
        $this->headers[$heading] = $value;
        $this->headersString = null;

        return $this;
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
        $this->headers['UserAgent'] = $userAgent;
        $this->headersString = null;
        return $this;
    }

    /**
     * Return the headers to send to the browser, as a single well-formed string
     *
     * @return string
     */
    public function getHeadersString()
    {
        if (!isset($this->headersString))
        {
            $this->headersString = '';
            foreach ($this->headers as $heading => $value)
            {
                $this->headersString .= $heading . ': ' . $value . "\r\n";
            }
        }

        return $this->headersString;
    }

    /**
     * Do we have the named header already set?
     *
     * @param  string $headerName
     *         the name of the header to check for
     *
     * @return boolean
     *         TRUE if the header already exists
     *         FALSE if it does not
     */
    public function hasHeaderCalled($headerName)
    {
        if (isset($this->headers[$headerName]))
        {
            return true;
        }

        return false;
    }

    /**
     * Obtain the request line to send to the HTTP server
     *
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


    // =========================================================================
    //
    // Support for POST and PUT requests
    //
    // -------------------------------------------------------------------------

    public function asPostRequest()
    {
        return $this->withHttpVerb("POST");
    }

    public function asPutRequest()
    {
        return $this->withHttpVerb("PUT");
    }

    public function addData($name, $value)
    {
        $this->body[$name] = $value;
    }

    public function setPayload($payload)
    {
        $this->body = $payload;
    }

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

    public function getEncodedBody()
    {
        $return = '';
        foreach ($this->body as $key => $value)
        {
            if (strlen($return) > 0)
            {
                $return .= '&';
            }
            $return .= urlencode($key) . '=' . urlencode($value);
        }

        return $return;
    }

    // =========================================================================
    //
    // Support for DELETE requests
    //
    // -------------------------------------------------------------------------

    public function asDeleteRequest()
    {
        return $this->withHttpVerb("DELETE");
    }
}