<?php

/**
 * Stone1 - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone1
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone1\HttpLib;

use Exception;

/**
 * A simple HTTP server for use in publishers
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
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
            throw new Exception("Unable to create TCP/IP socket; error is " . socket_strerror(socket_last_error($this->socket)));
        }

        if (socket_bind($this->socket, '0.0.0.0', $port) === false)
        {
            throw new Exception("Unable to open TCP/IP socket on port $port; error is " . socket_strerror(socket_last_error($this->socket)));
        }
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
        if (socket_listen($this->socket, 1) === false)
        {
            throw new Exception("Unable to listen on port " . $this->port . "; error is " . socket_strerror(socket_last_error($this->socket)));
        }

        $requestSocket = socket_accept($this->socket);
        if ($requestSocket === false)
        {
            throw new Exception("Unable to accept incoming TCP/IP connection to port $this->listeningPort; error is " . socket_strerror(socket_last_error($this->socket)));
        }

        // mark this socket as blocking, and to hand around until all
        // data is transmitted
        socket_set_block($requestSocket);
        $linger = array('l_linger' => 1, 'l_onoff' => 1);
        socket_set_option($requestSocket, SOL_SOCKET, SO_LINGER, $linger);

        $request = socket_read($requestSocket, 2048, PHP_NORMAL_READ);
        if ($request === false)
        {
            throw new Exception("Unable to read from TCP/IP socket; error is " . socket_strerror(socket_last_error($this->socket)));
        }

        // if we get here, we think we have a request
        return array($requestSocket, $request);
    }

    /**
     * Tell the client listening on $requestSocket that we are going to send
     * our response back as HTTP chunks.
     *
     * @param socket $requestSocket the socket that the client is listening on
     * @param string $responseMimeType the mimetype of the data we're going to
     *               send back
     * @return mixed the return value from writing to the socket
     */
    public function setChunkedResponse($requestSocket, $responseMimeType = 'application/json')
    {
        $response = <<<EOS
HTTP/1.1 200 OK
Content-Type: $responseMimeType
Transfer-Encoding: chunked
Server: Hornet(6.6.6)


EOS;

        return socket_write($requestSocket, $response, strlen($response));
    }

    /**
     * Write a response to a HTTP stream
     *
     * @param socket $requestSocket the socket to write to
     * @param string $message the response to send
     * @return mixed false if we could not write to the socket, or the number
     *         of bytes we have written
     */
    public function streamResponse($requestSocket, $message)
    {
        // we send three lines ...
        //
        // line 1: the length of the message, in hexadecimal
        // line 2: the message itself
        // line 3: a blank line
        //
        // and if any of the writes fail, we bail

        $returnBytesWritten = 0;

        $tweetSize = strlen($message);
        $chunkSize = dechex(strlen($tweetSize) + strlen($message) +4) . "\r\n";
        $bytesWritten = socket_write($requestSocket, $chunkSize, strlen($chunkSize));
        if ($bytesWritten === false)
        {
            return false;
        }
        $returnBytesWritten = $bytesWritten;

        $bytesWritten = socket_write($requestSocket, $tweetSize . "\r\n", strlen($tweetSize) + 2);
        if ($bytesWritten === false)
        {
            return false;
        }
        $returnBytesWritten += $bytesWritten;

        $bytesWritten = socket_write($requestSocket, $message . "\r\n\r\n", strlen($message) + 4);
        if ($bytesWritten === false)
        {
            return false;
        }
        $returnBytesWritten += $bytesWritten;

        return $returnBytesWritten;
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
