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
use DataSift\Stone\ExceptionsLib\LegacyErrorCatcher;
use DataSift\Stone\HttpLib\HttpClientConnection;
use DataSift\Stone\HttpLib\HttpClientRequest;
use DataSift\Stone\HttpLib\HttpClientResponse;

/**
 * Support for dealing with content that is chunked
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpDefaultTransport extends HttpTransport
{
    /**
     * Read data from the connection
     *
     * @param HttpClientConnection $connection our connection to the HTTP server
     * @param HttpClientResponse $response where we put the results
     * @return mixed null on error, otherwise the size of the content read
     */
    public function readContent(HttpClientConnection $connection, HttpClientResponse $response)
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
            $body .= $connection->readLine();
            // var_dump($body);

            // keep count of how much data we've read
            $response->bytesRead += strlen($body);
        }
        while (!$connection->feof() && ($expectedLen && $response->bytesRead < $expectedLen));

        // stash the retrieved body in the response
        $response->decodeBody($body);
        $chunkSize = strlen($body);

        // how many bodies have we received?
        // $context->stats->increment('response.body');

        // does the connection need to close?
        if ($response->connectionMustClose())
        {
            $connection->disconnect($context);
        }

        // all done
        return $chunkSize;
    }
}
