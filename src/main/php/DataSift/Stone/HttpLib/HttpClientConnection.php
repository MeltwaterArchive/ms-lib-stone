<?php

/**
 * Stone
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

use Exception;
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\StatsLib\StatsdClient;

use DataSift\Stone\HttpLib\Transports\HttpChunkedTransport;
use DataSift\Stone\HttpLib\Transports\HttpDefaultTransport;

/**
 * Low-level connection to a HTTP server
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
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
     * Connect to the given URL
     *
     * @param HttpClientRequest $request
     * @param int $timeout
     * @return boolean did we successfully connect?
     */
    public function connect(HttpAddress $address)
    {
        // timers!
        //var_dump('>> CONNECTING');
        $wrapper = new LegacyErrorCatcher();
        $callback = function($hostname, $port)
        {
            return fsockopen($hostname, $port);

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
        }
        $microEnd = microtime(true);
        //var_dump('>> CONNECTED');

        // log some stats
        // $context->stats->timing('connect.open', $microEnd - $microStart);

        // what happened?
        if (!is_resource($this->socket))
        {
            // connection failed
            // log it
            // $context->stats->increment('connect.failed');
            // $context->stats->timing('connect.close', $microEnd - $microStart);
            return false;
        }

        // if we get here, we have a successful connection
        // $context->stats->increment('connect.success');

        $this->connectStart = $microStart;
        $this->connectEnd   = $microEnd;

        return true;
    }

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

        //$context->stats->increment('connect.disconnect');
        //$context->stats->timing('connect.close', microtime(true) - $this->connectStart);
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
        // var_dump('>> readLine() ' . __LINE__);
        $line = false;
        do
        {
            $line = fgets($this->socket);
        }
        while (!$line && !$this->feof());
        // var_dump($line);

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

        $block = '';
        do
        {
            $block .= fread($this->socket, $blockSize - strlen($block));
        }
        while (strlen($block) < $blockSize && !$this->feof());

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

    public function send($data)
    {
        // do we have a socket to send to?
        if (!is_resource($this->socket))
        {
            return;
        }

        // send the data
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
