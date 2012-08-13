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

namespace DataSift\Stone\HttpLib\Transports;

use Exception;
use DataSift\Stone\ContextLib\Context;
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\HttpLib\HttpClientConnection;
use DataSift\Stone\HttpLib\HttpClientRequest;
use DataSift\Stone\HttpLib\HttpClientResponse;
use DataSift\Stone\StatsLib\StatsdClient;

/**
 * Base class for supporting all of the different connection types to a HTTP
 * server
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

abstract class HttpTransport
{
    /**
     * The OneTrueLineEnding(tm) for the HTTP dialect
     */
    const CRLF = "\r\n";

    /**
     * Send data to our connection as a GET request
     *
     * Override this for more exotic transports, such as web sockets
     *
     * @param Context $context
     *     the global state that we're allowed to use
     * @param HttpClientConnection $connection
     *     our network connection to the HTTP server
     * @param HttpClientRequest $request
     *     the request that we are sending
     * @return mixed
     *     HttpClientResponse on success,
     *     false if the connection was not open
     */
    public function sendGet(Context $context, HttpClientConnection $connection, HttpClientRequest $request)
    {
        // log how many GET requests we have made
        $context->stats->increment('request.verb.get');

        // cannot send if we do not have an open socket
        if (!$connection->isConnected())
        {
            return false;
        }

        // how quickly did we get the chance to send the first line off?
        $context->stats->timing('request.firstLineTime', microtime(true) - $connection->connectStart);

        // send the request
        //var_dump('>> SENDING');
        $connection->send($request->getRequestLine() . self::CRLF);

        // send any supporting headers

        $this->addAdditionalHeadersToRequest($context, $request);
        $headers = $request->getHeadersString();
        if ($headers !== null)
        {
            $connection->send($headers);
        }

        // send empty line to complete request
        $connection->send(self::CRLF);
        //var_dump('>> SENT');

        // how long did that take?
        $context->stats->timing('request.lastLineTime', microtime(true) - $connection->connectStart);
    }

    /**
     * Send data to our connection as a GET request
     *
     * Override this for more exotic transports, such as web sockets
     *
     * @param Context $context
     *     the global state that we're allowed to use
     * @param HttpClientConnection $connection
     *     our network connection to the HTTP server
     * @param HttpClientRequest $request
     *     the request that we are sending
     * @return mixed
     *     HttpClientResponse on success,
     *     false if the connection was not open
     */
    public function sendPost(Context $context, HttpClientConnection $connection, HttpClientRequest $request)
    {
        // log how many GET requests we have made
        $context->stats->increment('request.verb.get');

        // cannot send if we do not have an open socket
        if (!$connection->isConnected())
        {
            return false;
        }

        // how quickly did we get the chance to send the first line off?
        $context->stats->timing('request.firstLineTime', microtime(true) - $connection->connectStart);

        // send the request
        //var_dump('>> SENDING');
        $connection->send($request->getRequestLine() . self::CRLF);
        $encodedData = $request->getPostBody();
        $request->withExtraHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->withExtraHeader('Content-Length', strlen($encodedData));


        // send any supporting headers
        $this->addAdditionalHeadersToRequest($context, $request);
        $headers = $request->getHeadersString();
        if ($headers !== null)
        {
            $connection->send($headers);
        }

        // send empty line to complete request
        $connection->send(self::CRLF);
        //var_dump('>> SENT');

        $connection->send($encodedData . self::CRLF);

        // how long did that take?
        $context->stats->timing('request.lastLineTime', microtime(true) - $connection->connectStart);
    }

    /**
     * Generate any additional header lines to send for a request
     *
     * This is here for exotic transports (like web sockets) to override when
     * they need to do something funky
     *
     * @param Context $context
     *     the *only* global state that we're allowed to use
     * @param HttpClientRequest $request
     *     the request that the user wants to send
     *     we add any additional headers to the request object
     */
    protected function addAdditionalHeadersToRequest(Context $context, HttpClientRequest $request)
    {
        // do nothing by default
    }

    /**
     * Read the response line + response headers from the HTTP connection
     *
     * @param Context $context the global state that we are allowed to use
     * @param HttpClientConnection $connection our connection to the HTTP server
     * @param HttpClientRequest $request the request that we want a response to
     * @return HttpClientResponse the response we received
     */
    public function readResponse(Context $context, HttpClientConnection $connection, HttpClientRequest $request)
    {
        // now, we need to see what the server said
        $response = new HttpClientResponse($connection);
        $statusCode = $this->readResponseLine($context, $connection, $response);

        // do we think it is safe to read the response headers?
        if (!$response->hasErrors())
        {
            // yes - let's go get them
            $this->readHeaders($context, $connection, $response);
        }

        // what do we think of the response?
        //
        // this hook is here for the more exotic transports
        $this->evaluateResponse($context, $connection, $request, $response);

        // all done, for better or for worse
        return $response;
    }

    /**
     * Read the very first line back that we get back from the HTTP server
     * after making a request
     *
     * @param Context $context the global state we're allowed to reuse
     * @param resource $socket the network socket to the remote server
     * @param HttpClientResponse $response
     * @return type
     */
    protected function readResponseLine(Context $context, HttpClientConnection $connection, HttpClientResponse $response)
    {
        // make sure the socket is valid
        if (!$connection->isConnected())
        {
            $response->addError("readResponseLine", "not connected");
            return false;
        }

        // we are expecting statusLine
        $statusLine = $connection->readLine();
        // var_dump('>> STATUS: ' . $statusLine);
        $response->bytesRead += strlen($statusLine);
        $statusLine = substr($statusLine, 0, -2);

        // how long did it take to get the first response?
        $context->stats->timing('response.firstLineTime', microtime(true) - $connection->connectStart);

        // decode the statusLine
        $response->decodeStatusLine($statusLine);

        // what response code did we get?
        $context->stats->increment('response.status.' . $response->statusCode);

        // all done
        return $response->statusCode;
    }

    /**
     * Read the headers from the remote server
     *
     * @param Context $context the global state that we're allowed to reuse
     * @param HttpClientConnection $connection the network connection to the HTTP server
     * @param HttpClientResponse $response
     */
    protected function readHeaders(Context $context, HttpClientConnection $connection, HttpClientResponse $response)
    {
        // make sure the socket is valid
        if (!$connection->isConnected())
        {
            $response->addError("readHeaders", "not connected");
            return false;
        }

        // retrieve the headers
        $headersCompleted = false;
        do
        {
            $headerLine = $connection->readLine();
            //var_dump('>> HEADER: ' . $headerLine);
            $response->bytesRead += strlen($headerLine);
            $headerLine = substr($headerLine, 0, -2);

            if (strlen($headerLine) == 0)
            {
                $headersCompleted = true;
            }
            else
            {
                $response->decodeHeader($headerLine);
            }
        }
        while (!$connection->feof() && !$headersCompleted);
    }

    /**
     * Read data from the connection
     *
     * Each transport mechanism needs to provide its own readContent() that
     * copes with whatever perculiarities abound
     *
     * @param Context $context the global state that we're allowed to use
     * @param HttpClientConnection $connection our connection to the HTTP server
     * @param HttpClientResponse $response where we put the results
     * @return mixed null on error, otherwise the size of the content read
     */
    abstract public function readContent(Context $context, HttpClientConnection $connection, HttpClientResponse $response);

    /**
     * Work out whether we like the response or not
     *
     * This is called after the response line + all response headers have been
     * retrieved from the remote HTTP server
     *
     * Exotic transports (such as websockets) should override this
     *
     * @param Context $context
     *     the *only* global state that we're allowed to use
     * @param HttpClientConnection $connection
     *     our connection to the HTTP server
     * @param HttpClientRequest $request
     *     the request that this is a response to
     * @param HttpClientResponse $response
     *     the response containing the headers we have received
     */
    protected function evaluateResponse(Context $context, HttpClientConnection $connection, HttpClientRequest $request, HttpClientResponse $response)
    {
        // do nothing by default :)
    }

    public function close(Context $context, HttpClientConnection $connection)
    {
        // do nothing by default
    }
}
