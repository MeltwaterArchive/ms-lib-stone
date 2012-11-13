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
use DataSift\Stone\LogLib\Log;

/**
 * Support for dealing with content that is chunked
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpChunkedTransport extends HttpTransport
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

        if (!$response->transferIsChunked())
        {
            $response->addError("readContent", "Transfer-Encoding is not Chunked");
            return null;
        }

        // we are chunking!
        //
        // keep count of how many
        $chunkCount = 0;

        // do we need to read all of the chunks in one go?
        if ($response->connectionMustClose())
        {
            $chunkSize = 1;
            do
            {
                $chunkSize = $this->readChunk($connection, $response);
                $chunkCount++;
            }
            while (!$connection->feof() && $chunkSize != 0);
        }
        else
        {
            $chunkSize = $this->readChunk($connection, $response);
            $chunkCount++;
        }

        // how many chunks have we received?
        // $context->stats->updateStats('response.chunks', $chunkCount);

        // does the connection need to close?
        if ($response->connectionMustClose())
        {
            $connection->disconnect($context);
        }

        // all done
        return $chunkSize;
    }

    /**
     * Read a single chunk from the HTTP server
     *
     * @param HttpClientConnection $connection
     * @param HttpClientResponse $response
     * @return int the size of the chunk
     */
    protected function readChunk(HttpClientConnection $connection, HttpClientResponse $response)
    {
        $crlf = "\r\n";

        $chunkSize = $connection->readLine();
        $response->bytesRead += strlen($chunkSize);
        $chunkSize = substr($chunkSize, 0, -2);
        $chunkSize = hexdec($chunkSize);

        if (!is_int($chunkSize))
        {
            Log::write(Log::LOG_WARNING, "Received non-integer chunksize: " . $chunksize);
            $response->type      = HttpClientResponse::TYPE_INVALID;
            $response->mustClose = true;

            return 0;
        }
        else if ($chunkSize > 1024*1024*10)
        {
            $response->addError("readChunk", "chunk size too large: " . $chunkSize);
            $response->type      = HttpClientResponse::TYPE_INVALID;
            $response->mustClose = true;
            return 0;
        }

        if ($chunkSize > 0)
        {
            $expectedChunkSize = $chunkSize + 2;
            Log::write(Log::LOG_DEBUG, "Expecting chunk of size: " . $expectedChunkSize);

            $chunk = $connection->readBlock($expectedChunkSize);

            if (strlen($chunk) < $expectedChunkSize)
            {
                $response->addError("readChunk", sprintf("Expected %d bytes; received %d bytes\n", $expectedChunkSize, strlen($chunk)));

                $response->type      = HttpClientResponse::TYPE_INVALID;
                $response->mustClose = true;

                return 0;
            }

            $response->bytesRead += strlen($chunk);
            $response->decodeChunk($chunk);

            return $chunkSize;
        }
        else
        {
            // we assume that we are at the end of the stream
            $response->mustClose = true;
            return 0;
        }
    }
}
