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

use DataSift\Stone\HttpLib\HttpAddress;
use DataSift\Stone\HttpLib\HttpClient;
use DataSift\Stone\HttpLib\HttpClientRequest;
use DataSift\Stone\HttpLib\HttpClientResponse;
use DataSift\Stone\LogLib\Log;

/**
 * A generic class for receiving data via HTTP streaming
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpStreamConsumer
{
    /**
     * Read data from a stream, forever
     *
     * @param array    $requests where to find the stream
     * @param callback $callback we call this after every piece of data is read from the stream
     */
    public function consume($requests, $callback)
    {
        $streams = array();

        // deal with things when a non-array is passed in
        if (!is_array($requests))
        {
            $requests = array($requests);
        }

        foreach ($requests as $index => $request)
        {
            // lets get our requests made
            $client = new HttpClient();
            $bytesRead = 0;

            // we make the request
            $response = $client->newGetRequest($request);
            if (!$response instanceof HttpClientResponse)
            {
                // the connection failed
                // var_dump('failed connection');
            }
            else
            {
                $streams[$index] = array('request' => $request, 'client' => $client, 'response' => $response);

                // now, we need to make sure that we call the callback here
                if (!call_user_func_array($callback, array($client, $request, $response)))
                {
                    // remove us from the list of streams
                    $client->disconnect();
                    unset($streams[$index]);
                }
            }
        }

        // do we have any streams left?
        if (count($streams) == 0)
        {
            // no ... job done
            return;
        }

        // if we get here, then we have some streams to consume data from
        $done = false;
        do
        {
            // wait for a stream
            $readArray = array();
            foreach ($streams as $streamIndex => $stream)
            {
                if ($stream['client']->isConnected())
                {
                    $readArray[$streamIndex] = $stream['client']->getSocket();
                }
            }

            if (count($readArray) == 0)
            {
                // var_dump('>> READARRAY is 0');
                // We don't have anything to process, dive out
                return;
            }

            $sockets = $readArray;
            $writeArray = $exceptArray = array();
            $activeStreams = array();

            $selectedStreams = 0;
            while ($selectedStreams == 0)
            {
                $readArray = $sockets;

                // we use the scream operator here to suppress a stupid
                // warning about the system call being interrupted by
                // signals such as SIGALRM ... grrr at the warning!
                $selectedStreams = @stream_select($readArray, $writeArray, $exceptArray, 30);
            }

            foreach ($readArray as $socket)
            {
                $streamIndex = array_search($socket, $sockets);

                $client   = $streams[$streamIndex]['client'];
                $request  = $streams[$streamIndex]['request'];
                $response = $streams[$streamIndex]['response'];

                // step 1:
                //
                // get some data
                $client->readContent($response);

                // step 2:
                //
                // call the callback
                if (!call_user_func_array($callback, array($client, $request, $response)))
                {
                    // remove us from the list of streams
                    Log::write(Log::LOG_INFO, "Closing connection at the internal callback's request");

                    $client->disconnect();
                    unset($streams[$streamIndex]);
                }

                // step 3: was there a problem with the response?
                else if ($response->hasErrors())
                {
                    foreach ($response->errorMsgs as $msg)
                    {
                        Log::write(Log::LOG_WARNING, "HttpStreamConsumer error: $msg");
                    }

                    Log::write(Log::LOG_INFO, "Closing connection after errors");
                    $client->disconnect();
                    unset($streams[$streamIndex]);
                }

                else if ($response->connectionMustClose())
                {
                    Log::write(Log::LOG_INFO, "Closing connection (probably closed by remote end");
                    $client->disconnect();
                    unset($streams[$streamIndex]);
                }
            }
        }
        while (count($streams) > 0);
    }
}
