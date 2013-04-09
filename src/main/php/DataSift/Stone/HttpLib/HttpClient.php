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

use DataSift\Stone\HttpLib\Transports\HttpDefaultTransport;
use DataSift\Stone\HttpLib\Transports\HttpChunkedTransport;
use DataSift\Stone\HttpLib\Transports\WsTransport;

/**
 * An effective URL client with detailed metrics built in
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
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

    /**
     * Make a request to the HTTP server
     *
     *
     *
     * @param Context $context
     * @param HttpClientRequest $request
     * @return type
     */
    public function newRequest(HttpClientRequest $request)
    {
        $method = 'new' . ucfirst(strtolower($request->getHttpVerb())) . 'Request';
        return call_user_func_array(array($this, $method), array($request));
    }

    // =========================================================================
    //
    // Support for GET requests, possibly ones that stream
    //
    // -------------------------------------------------------------------------

    /**
     * Make a new GET request to the HTTP server
     *
     * NOTE: the connection to the HTTP server will only be closed *if* the
     *       HTTP server sends a Connection: close header
     *
     * @param HttpClientRequest $request the request to make
     * @return HttpClientResponse what we got back from the HTTP server
     */
    public function newGetRequest(HttpClientRequest $request)
    {
        // var_dump('>> GET ' . (string)$request->getAddress());
        // can we connect to the remote server?
        $this->connection = new HttpClientConnection();
        if (!$this->connection->connect($request->getAddress(), 5))
        {
            // could not connect
            return false;
        }

        // choose a transport; this may change as we work with the connection
        if ($request->getAddress()->scheme == 'ws')
        {
            $this->transport = new WsTransport();
        }
        else
        {
            $this->transport = new HttpDefaultTransport();
        }

        // now, send the GET request
        $this->transport->sendGet($this->connection, $request);

        // listen for an answer
        $response = $this->transport->readResponse($this->connection, $request);

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

        // return the results
        return $response;
    }

    /**
     * Make a new POST request to the HTTP server
     *
     * NOTE: the connection to the HTTP server will only be closed *if* the
     *       HTTP server sends a Connection: close header
     *
     * @param HttpClientRequest $request the request to make
     * @return HttpClientResponse what we got back from the HTTP server
     */
    public function newPostRequest(HttpClientRequest $request)
    {
        // can we connect to the remote server?
        $this->connection = new HttpClientConnection();
        if (!$this->connection->connect($request->getAddress(), 5))
        {
            // could not connect
            return false;
        }

        // choose a transport; this may change as we work with the connection
        if ($request->getAddress()->scheme == 'ws')
        {
            $this->transport = new WsTransport();
        }
        else
        {
            $this->transport = new HttpDefaultTransport();
        }

        // now, send the POST request
        $this->transport->sendPost($this->connection, $request);

        // listen for an answer
        $response = $this->transport->readResponse($this->connection, $request);

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

        // return the results
        return $response;
    }

    // =========================================================================
    //
    // Support for PUT requests, possibly ones that stream
    //
    // -------------------------------------------------------------------------

    /**
     * Make a new PUT request to the HTTP server
     *
     * NOTE: the connection to the HTTP server will only be closed *if* the
     *       HTTP server sends a Connection: close header
     *
     * @param HttpClientRequest $request the request to make
     * @return HttpClientResponse what we got back from the HTTP server
     */
    public function newPutRequest(HttpClientRequest $request)
    {
        // var_dump('>> PUT ' . (string)$request->getAddress());
        // can we connect to the remote server?
        $this->connection = new HttpClientConnection();
        if (!$this->connection->connect($request->getAddress(), 5))
        {
            // could not connect
            return false;
        }

        // choose a transport; this may change as we work with the connection
        if ($request->getAddress()->scheme == 'ws')
        {
            $this->transport = new WsTransport();
        }
        else
        {
            $this->transport = new HttpDefaultTransport();
        }

        // now, send the GET request
        $this->transport->sendPut($this->connection, $request);

        // listen for an answer
        $response = $this->transport->readResponse($this->connection, $request);

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

        // return the results
        return $response;
    }

    // =========================================================================
    //
    // Support for DELETE requests, possibly ones that stream
    //
    // -------------------------------------------------------------------------

    /**
     * Make a new DELETE request to the HTTP server
     *
     * NOTE: the connection to the HTTP server will only be closed *if* the
     *       HTTP server sends a Connection: close header
     *
     * @param HttpClientRequest $request the request to make
     * @return HttpClientResponse what we got back from the HTTP server
     */
    public function newDeleteRequest(HttpClientRequest $request)
    {
        // var_dump('>> DELETE ' . (string)$request->getAddress());
        // can we connect to the remote server?
        $this->connection = new HttpClientConnection();
        if (!$this->connection->connect($request->getAddress(), 5))
        {
            // could not connect
            return false;
        }

        // choose a transport; this may change as we work with the connection
        if ($request->getAddress()->scheme == 'ws')
        {
            $this->transport = new WsTransport();
        }
        else
        {
            $this->transport = new HttpDefaultTransport();
        }

        // now, send the GET request
        $this->transport->sendDelete($this->connection, $request);

        // listen for an answer
        $response = $this->transport->readResponse($this->connection, $request);

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

        // return the results
        return $response;
    }


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
    public function sendData($data)
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

        // send the data
        $this->connection->send($data);
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
     * Disconnect from the remote server.
     *
     * If we are not currently connected, do nothing
     *
     * @param Context $context
     */
    public function disconnect()
    {
        if ($this->connection->isConnected())
        {
            $this->transport->close($this->connection);
            $this->connection->disconnect();
        }
    }

    public function getSocket()
    {
        return $this->connection->getSocket();
    }

    public function isSocketConnected()
    {
        return !!$this->getSocket();
    }
}
