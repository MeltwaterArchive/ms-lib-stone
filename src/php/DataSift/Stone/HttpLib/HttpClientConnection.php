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
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;

use DataSift\Stone\HttpLib\Transports\HttpChunkedTransport;
use DataSift\Stone\HttpLib\Transports\HttpDefaultTransport;

/**
 * Low-level connection to a HTTP server
 *
 * @category  Libraries
 * @package   Stone/HttpLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class HttpClientConnection
{
    /**
     * The TCP socket that we're all about
     * @var resource
     */
    private $socket;

    /**
     * When did we start this connection?
     *
     * Used to track our timings
     * @var float
     */
    public $connectStart = null;

    /**
     * when did we succeed in connecting to the (possibly) remote server?
     *
     * Used to track our timings
     * @var float
     */
    public $connectEnd = null;

    /**
     * how long to wait for reads to happen, in seconds
     *
     * @var float
     */
    private $readTimeout = 5.0;

    /**
     * where are we connected to?
     * @var HttpAddress
     */
    private $httpAddress;

    /**
     * Connect to the given URL
     *
     * @param HttpAddress address
     *        the URL to connect to
     * @param float $timeout
     *        how long to wait before timing out the connection attempt
     * @return void
     */
    public function connect(HttpAddress $address, $timeout = 5.0)
    {
        //var_dump('>> CONNECTING');
        $wrapper = new LegacyErrorCatcher();
        $errno  = 0;
        $errstr = '';
        $callback = function($hostname, $port) use($errno, $errstr, $timeout)
        {
            return fsockopen($hostname, $port, $errno, $errstr, $timeout);
        };

        $microStart = microtime(true);
        try
        {
            $this->socket = $wrapper->callUserFuncArray($callback, array($address->hostname, $address->port));
        }
        catch (Exception $e)
        {
            // well, that did not go well
            // var_dump($e->getMessage());
            throw new E5xx_HttpConnectFailed($address, $e->getMessage());
        }
        $microEnd = microtime(true);
        //var_dump('>> CONNECTED');

        // what happened?
        if (!is_resource($this->socket))
        {
            // connection failed
            throw new E5xx_HttpConnectFailed($address, $errstr);
        }

        // set the stream to timeout aggressively
        socket_set_timeout($this->socket, 0, $this->readTimeout);

        // remember how long the connection took
        $this->connectStart = $microStart;
        $this->connectEnd   = $microEnd;

        // remember where we are connected to, to help us reuse
        // this connection
        $this->httpAddress = $address;

        // all done
		return true;
    }

    /**
     * keep reading from the socket (throwing away whatever we read) until
     * the server closes the socket
     *
     * @return void
     */
    public function waitForServerClose()
    {
        if (!$this->isConnected())
        {
            return;
        }

        while (fread($this->socket, 1024));
    }

    /**
     * Disconnect from the HTTP server
     */
    public function disconnect()
    {
        if (!$this->isConnected())
        {
            return;
        }

        fclose($this->socket);
        $this->socket = null;
    }

    /**
     * Read a single CRLF-terminated string from the connection
     *
     * This is a separate method to make testing/debugging easier
     *
     * @return string
     */
    public function readLine()
    {
        $start = microtime(true);

        // var_dump('>> readLine() ' . __LINE__);
        $line = false;
        $returnLine = '';
        do
        {
            // var_dump($this->feof());
            $line = fgets($this->socket);
            if ($line) {
                $returnLine = $returnLine . $line;
            }
            // var_dump($line);
            $now = microtime(true);
        }
        while((!$line || substr($returnLine, -2, 2) != "\r\n") && !$this->feof() && ($now < ($start + $this->readTimeout)));

        // var_dump($line);
        // var_dump($this->feof());
        // var_dump($start + $this->timeout);
        // var_dump($now);
        // var_dump($now < $start + $this->timeout);
        return $returnLine;
    }

    public function readLineWithTimeout($timeout = 1)
    {
        // make sure we have a valid timeout
        if ($timeout == null) {
            $timeout = 1;
        }

        // build the list of sockets to select() on
        $read_list = [ $this->socket ];
        $write_list = $except_list = [];

        // we assume that simply waiting for a socket to have data is
        // enough to satisfy the timeout condition
        $noOfStreamsToRead = stream_select($read_list, $write_list, $except_list, $timeout);

        // did we get anything?
        if ($noOfStreamsToRead == 0 || count($read_list) == 0) {
            throw new E5xx_HttpReadTimeout();
        }

        // because we cannot put a timeout on the fgets() operation,
        // this does create a loophole where the timeout might not be
        // strictly accurate :(
        $line = fgets($this->socket);

        // all done
        var_dump($line);
        return $line;
    }

    /**
     * Read a block of data from the connection
     *
     * This is a separate method to make testing/debugging easier
     *
     * @param int $blockSize the amount of bytes to read
     * @return string the data we read
     */
    public function readBlock($blockSize)
    {
        // var_dump('>> readBlock(' . $blockSize . ')');

        $start = microtime(true);

        $block = '';
        do
        {
            $block .= fread($this->socket, $blockSize - strlen($block));
            $now   = microtime(true);
        }
        while (strlen($block) < $blockSize && !$this->feof() && ($start + $this->readTimeout) > $now);

        // var_dump($block);

        return $block;
    }

    /**
     * Check our socket for if we're at the end of the socket's file stream or
     * not
     *
     * @return boolean true if we're at the end of the socket stream
     */
    public function feof()
    {
        if (!is_resource($this->socket))
        {
            return true;
        }

        return feof($this->socket);
    }

    /**
     * write data to the TCP socket, forcing a flush() on the socket
     * after writing is complete
     *
     * @param  string $data
     *         the data to write
     * @return void
     */
    public function send($data)
    {
        // do we have a socket to send to?
        if (!is_resource($this->socket))
        {
            return;
        }

        // send the data
        // var_dump($data);
        fwrite($this->socket, $data, strlen($data));
        fflush($this->socket);
    }

    /**
     * Are we connected to a remote server?
     *
     * @return boolean
     */
    public function isConnected()
    {
        return (is_resource($this->socket));
    }

    /**
     * Are we connected to the same address?
     *
     * The HttpClient uses this to work out whether the connection can
     * be reused or not
     *
     * @param  HttpAddress $address
     *         the address to compare against
     * @return boolean
     *         TRUE if we are connected to $address,
     *         FALSE otherwise
     */
    public function isConnectedTo(HttpAddress $address)
    {
        if (!$this->isConnected())
        {
            // we're currently disconnected
            return false;
        }

        // are we connected via the same scheme?
        if ($this->httpAddress->scheme != $address->scheme) {
            return false;
        }

        // are we connected to the same host?
        if ($this->httpAddress->hostname != $address->hostname) {
            return false;
        }

        // same host ... but same port?
        if ($this->httpAddress->port != $address->port) {
            return false;
        }

        // what about auth credentials?
        if (isset($this->httpAddress->user)) {
            if ($this->httpAddress->user != $address->user) {
                return false;
            }

            if ($this->httpAddress->password != $address->password) {
                return false;
            }
        }

        // if we get here, we're confident that we're connected to
        // the same place
        //
        // this means that the connection can be reused by the HttpClient
        return true;
    }

    /**
     * Get our socket
     *
     * This is useful (for example) if you're building up a list of
     * sockets to poll via stream_select()
     *
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }
}
