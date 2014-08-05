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
 * Represents HTTP headers
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

trait HttpHeaders
{
    /**
     * our list of actual headers
     * @var array
     */
    public $headers = [];

    /**
     * our translation layer of case-insensitive HTTP headers
     * @var array
     */
    private $caseInsensitiveHeaders = [];

    /**
     * Create a header
     *
     * @param string $heading
     * @param string $value
     * @return HttpClientRequest $this
     */
    public function setHeader($heading, $value)
    {
        $this->headers[$heading] = $value;
        $this->caseInsensitiveHeaders[strtolower($heading)] = $heading;

        return $this;
    }

    /**
     * set the list of headers
     *
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        $this->caseInsensitiveHeaders = [];
        foreach ($headers as $key => $value) {
            $this->caseInsensitiveHeaders[strtolower($key)] = $key;
        }
    }

    /**
     * return all of the headers currently defined
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return the headers to send to the browser, as a single well-formed string
     *
     * @return string
     */
    public function getHeadersString()
    {
        $headersString = '';
        foreach ($this->headers as $heading => $value)
        {
            $headersString .= $heading . ': ' . $value . "\r\n";
        }

        return $headersString;
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
        if (isset($this->caseInsensitiveHeaders[strtolower($headerName)]))
        {
            return true;
        }

        return false;
    }

    /**
     * Do we have a header with this value?
     *
     * @param  string $headerName
     *         the header we are looking for
     * @param  string $value
     *         the value we're looking for in the header
     * @return boolean
     *         TRUE if the header exists AND it has the value
     *         FALSE otherwise
     */
    public function hasHeaderWithValue($headerName, $value)
    {
        $return = $this->getHeaderCalled($headerName);
        if ($return === null)
        {
            return false;
        }

        if ($return != $value)
        {
            return false;
        }

        return true;
    }

    /**
     * Return the value of a HTTP request header
     *
     * @param  string $headerName
     *         the name of the header to retrieve
     *
     * @return string|null
     */
    public function getHeaderCalled($headerName)
    {
        $caseInsensitiveHeaderName = strtolower($headerName);
        if (!isset($this->caseInsensitiveHeaders[$caseInsensitiveHeaderName]))
        {
            return NULL;
        }

        $key = $this->caseInsensitiveHeaders[$caseInsensitiveHeaderName];

        return $this->headers[$key];
    }

    /**
     * remove any headers that have been set
     *
     * @return void
     */
    public function resetHeaders()
    {
        $this->headers = [];
        $this->caseInsensitiveHeaders = [];
    }
}
