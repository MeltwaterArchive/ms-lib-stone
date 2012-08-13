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
 * Support for talking to a HTTP server via WebSockets
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class WsTransport extends HttpTransport
{
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
    public function addAdditionalHeadersToRequest(Context $context, HttpClientRequest $request)
    {
        // build the headers
        $request->withExtraHeader('Upgrade', 'websocket')
                ->withExtraHeader('Connection', 'upgrade')
                ->withExtraHeader('Sec-WebSocket-Version', 13)
                ->withExtraHeader('Sec-WebSocket-Key', $this->generateWebSocketKey());
    }

    /**
     * Generate a base64-encoded web socket key for the handshake
     *
     * @return string
     */
    protected function generateWebSocketKey()
    {
        // step 1: create 16 bytes of random data
        $byteLen = 16;

        if (@is_readable('/dev/urandom'))
        {
           $fp = fopen('/dev/urandom', 'r');
           $nonceBytes = fread($fp, $byteLen);
           fclose($fp);
        }
        else
        {
            $nonceBytes = '';
            for ($i = 0; $i < $byteLen; $i++)
            {
                $nonceBytes += chr(mt_rand(0, 255));
            }
        }

        // step 2: encode it
        $key = base64_encode($nonceBytes);

        // all done
        return $key;
    }

    /**
     * Work out whether we like the response or not
     *
     * This is called after the response line + all response headers have been
     * retrieved from the remote HTTP server
     *
     * Exotic transports (such as websockets) should override this
     *
     * @link http://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-17
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
        // at this point, we've had the websocket upgrade attempt back
        // the question is ... do we like what we see?
        //
        // we do our very best to follow the verification steps laid out in
        // the RFC. Mess with these steps at your peril!!

        // step 1: if we didn't get a statusCode of 101, then there's no upgrade
        if ($response->statusCode != 101)
        {
            // nothing else for us to evaluate tbh
            //
            // we do not mark this response as invalid, because we may be
            // looking at a 'must authenticate' or 'redirect' type of response,
            // which are valid
            return;
        }

        // step 2: do we have a valid upgrade header?
        if (!isset($response->headers['Upgrade']))
        {
            $response->addError("evaluateResponse", "No 'Upgrade' header in response");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }
        if (strcasecmp($response->headers['Upgrade'], 'websocket') !== 0)
        {
            $response->addError("evaluateResponse", "'Upgrade' header does not contain correct value");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }

        // step 3: do we have a valid connection header?
        if (!isset($response->headers['Connection']))
        {
            $response->addError("evaluateResponse", "No 'Connection' header in response");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }
        if (strcasecmp($response->headers['Connection'], 'upgrade') !== 0)
        {
            $response->addError("evaluateResponse", "'Connection' header does not contain correct value");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }

        // step 4: do we have our key back?
        if (!isset($response->headers['Sec-WebSocket-Accept']))
        {
            $response->addError("evaluateResponse", "No 'Sec-WebSocket-Accept' header");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }
        $expectedKey = base64_encode(sha1($request->headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        if (strcasecmp($response->headers['Sec-WebSocket-Accept'], $expectedKey) !== 0)
        {
            $response->addError("evaluateResponse", "'Sec-WebSocket-Accept' header does not contain correct value");
            $response->setType(HttpClientResponse::TYPE_INVALID);

            $connection->disconnect($context);
            return;
        }

        // step 5: any problems with the protocol extensions?
        if (isset($request->headers['Sec-WebSocket-Extensions']))
        {
            // we asked for one or more extensions
            // now we need to make sure the server dealt with that properly
            if (!isset($response->headers['Sec-WebSocket-Extensions']))
            {
                $response->addError("evaluateResponse", "No 'Sec-WebSocket-Extensions' header");
                $response->setType(HttpClientResponse::TYPE_INVALID);

                $connection->disconnect($context);
                return;
            }

            if (!$this->areValidExtensions($request->headers['Sec-WebSocket-Extensions'], $response->headers['Sec-WebSocket-Extensions']))
            {
                $response->addError("evaluateResponse", "'Sec-WebSocket-Extensions' header is invalid");
                $response->setType(HttpClientResponse::TYPE_INVALID);

                $connection->disconnect($context);
                return;
            }
        }

        // step 6: any problems with the subprotocols?
        if (isset($request->headers['Sec-WebSocket-Protocol']))
        {
            // we asked for one or more subprotocols
            // now we need to make sure the server dealt with that properly
            if (!isset($response->headers['Sec-WebSocket-Protocol']))
            {
                $response->addError("evaluateResponse", "No 'Sec-WebSocket-Protocol' header");
                $response->setType(HttpClientResponse::TYPE_INVALID);

                $connection->disconnect($context);
                return;
            }

            if (!$this->isValidSubprotocol($request->headers['Sec-WebSocket-Protocol'], $response->headers['Sec-WebSocket-Protocol']))
            {
                $response->addError("evaluateResponse", "'Sec-WebSocket-Protocol' header is invalid");
                $response->setType(HttpClientResponse::TYPE_INVALID);

                $connection->disconnect($context);
                return;
            }
        }

        // if we *ever* make it here, we are talking to a working websocket!!
        $response->setType(HttpClientResponse::TYPE_WEBSOCKET);
    }

    protected function areValidExtensions($requestedString, $respondedString)
    {
        $requestedList = explode(';', $requested);
        $respondedList = explode(';', $responded);

        // we are looking for the server telling us about an extension that
        // we did not ask for
        //
        // if we find one, that is a FAIL
        foreach ($respondedList as $respondedExt)
        {
            if (!in_array($respondedExt, $requestedList))
            {
                return false;
            }
        }

        // if we get here, then all is well with the world ...
        // okay, all is well with the list of extensions in the response
        return true;
    }

    protected function isValidSubprotocol($requestedString, $respondedString)
    {
        $requestedList = explode(';', $requested);

        // we are looking for the server offering us a subprotocol that we
        // did not ask for
        //
        // if we find one, that is a FAIL
        if (!in_array($respondedString, $requestedList))
        {
            return false;
        }

        // if we get here, then all is well with the world ...
        // well, at least with the bit of the world we care about this instent!
        return true;
    }

    // =========================================================================
    //
    // WebSocket frame support
    //
    // -------------------------------------------------------------------------

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
    public function readContent(Context $context, HttpClientConnection $connection, HttpClientResponse $response)
    {
        // var_dump('>> READING FRAME');

        // cannot read if we do not have an open socket
        if (!$connection->isConnected())
        {
            $response->addError("readContent", "not connected");
            return null;
        }

        // it may take more than one frame to retrieve the content
        $done = false;
        $failed = false;
        $continuation   = false;
        $lastDataOpcode = false;

        $payloadToDate = '';
        $payloadOffset = 0;

        while (!$done && !$failed)
        {
            // retrieve the frame
            //
            // step 1: how long is the bloody thing?
            $frame = new WsFrame();

            // var_dump('>> reading first 2 bytes of frame');
            $data = $connection->readBlock(2);
            if (strlen($data) < 2)
            {
                // hrm ... something went wrong
                $response->addError("readContent", "unable to read first two bytes of frame");
                $failed = true;
                break;
            }
            $remainingHeaderBytes = $frame->determineRemainingHeaderBytes($data);
            if ($remainingHeaderBytes === false)
            {
                // hrm ... something went wrong
                $response->addError("readContent", "unable to determine remaining header bytes for frame");
                $failed = true;
                break;
            }

            if ($remainingHeaderBytes > 0)
            {
                $data .= $connection->readBlock($remainingHeaderBytes);
            }
            $remainingFrameBytes = $frame->determineRemainingFrameBytes($data);

            // var_dump('Wire size of frame is ' . strlen($data) + $remainingFrameBytes);

            if ($remainingFrameBytes > 0)
            {
                $data .= $connection->readBlock($remainingFrameBytes);
            }

            // var_dump('>> Attempted to read ' . $remainingFrameBytes);
            // var_dump('>> Frame size read is ' . strlen($data));

            // at this point, $data contains all of the frame, as read from the
            // wire.
            //
            // step 2: decode the frame
            $frame->initFromData($data);

            // var_dump($frame);
            // var_dump('>> OPCODE is ' . $frame->getOpcode());

            // step 3: keep track of how many frames we have received
            // $context->stats->increment('response.frames');

            // step 4: make sure the RSV value is accurate
            if ($frame->getRSV() != 0)
            {
                // invalid frame
                $response->addError("readContent", "non-zero RSV bits received");
                $this->sendCloseFrame($context, $connection, '');
                $failed = true;
            }

            if (!$done  && !$failed)
            {
                // step 5: handle the different frame types
                $opcode = $frame->getOpcode();
                // var_dump('>> OPCODE: ' . $opcode);

                switch ($opcode)
                {
                    case WsFrame::OPCODE_CONTINUATION:
                        if (!$continuation)
                        {
                            $response->addError("readContent", "unexpected continuation frame received");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                            $lastDataOpcode = false;
                        }
                        else
                        {
                            // add the data to the response
                            $payloadOffset = strlen($payloadToDate) - 1;
                            $response->decodeFrame($frame);
                            $payloadToDate .= $frame->getApplicationData();

                            // is this the last frame of the response?
                            if ($frame->isFin())
                            {
                                $done = true;
                            }
                            else
                            {
                                $continuation = true;
                            }
                        }
                        break;

                    case WsFrame::OPCODE_TEXT:
                        // not allowed if we are in a fragmented stream
                        if ($continuation)
                        {
                            $response->addError("readContent", "TEXT frame received when continuation frame expected");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        else
                        {
                            // this set of frames will be text
                            $lastDataOpcode = WsFrame::OPCODE_TEXT;

                            // add the data to the response
                            $response->decodeFrame($frame);
                            $payloadToDate .= $frame->getApplicationData();

                            // is this the last frame of the response?
                            if ($frame->isFin())
                            {
                                $done = true;
                            }
                            else
                            {
                                $continuation = true;
                            }
                        }
                        break;

                    case WsFrame::OPCODE_BINARY:
                        // not allowed if we are in a fragmented stream
                        if ($continuation)
                        {
                            $response->addError("readContent", "BINARY frame received when continuation frame expected");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        else
                        {
                            // this set of frames will be text
                            $lastDataOpcode = WsFrame::OPCODE_BINARY;

                            // add the data to the response
                            $response->decodeFrame($frame);
                            $payloadToDate .= $frame->getApplicationData();
                            $response->decodeChunk($frame->getApplicationData());

                            // is this the last frame of the response?
                            if ($frame->isFin())
                            {
                                $done = true;
                            }
                            else
                            {
                                $continuation = true;
                            }
                        }
                        break;

                    case WsFrame::OPCODE_PING:
                        // we need to send a pong frame, echoing back the data
                        if ($frame->getPayloadLen() > 125)
                        {
                            // invalid control frame!!
                            $response->addError("readContent", "PING frame received with payload too large");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        else if (!$frame->isFin())
                        {
                            // invalid control frame!!
                            $response->addError("readContent", "PING frame received with FIN bit not set");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        else
                        {
                            $this->sendPongFrame($context, $connection, $frame->getApplicationData());
                        }
                        break;

                    case WsFrame::OPCODE_PONG:
                        // make sure we do not have an invalid payload size
                        if ($frame->getPayloadLen() > 125)
                        {
                            // invalid control frame!!
                            $response->addError("readContent", "PONG frame received with payload too large");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        // no action needed
                        break;

                    case WsFrame::OPCODE_CLOSE:
                        // game over!
                        if ($frame->getPayloadLen() > 125)
                        {
                            // invalid control frame!!
                            $response->addError("readContent", "CLOSE frame received with payload too large");
                            $this->sendCloseFrame($context, $connection, '');
                            $failed = true;
                        }
                        else
                        {
                            $this->sendCloseFrame($context, $connection, $frame->getApplicationData());
                        }
                        $connection->waitForServerClose($context);
                        $connection->disconnect($context);
                        $done = true;
                        break;

                    default:
                        // oh dear :(
                        $this->sendCloseFrame($context, $connection, '');
                        $failed = true;
                }

                // at this point, we have a (possibly partial) payload to check
                // for UTF8 nastiness
                if (!$failed && $lastDataOpcode == WsFrame::OPCODE_TEXT && strlen($payloadToDate) > 0)
//                if (!$failed && $lastDataOpcode == WsFrame::OPCODE_TEXT && strlen($payloadToDate) > 0)
                {
                    // we are looking at a partial payload
                    if (!websocket_utf8_check($payloadToDate, $payloadOffset))
                    {
                        // not a valid payload - not valid UTF8
                        $response->addError("readContent", "TEXT sequence contains invalid UTF-8 at frame " . count($response->frames));
                        $failed = true;
                        $this->sendCloseFrame($context, $connection, '');
                    }
                }
            }
        }

        /*
        if (!$failed)
        {
            // check for well-formed UTF-8
            if ($lastDataOpcode == WsFrame::OPCODE_TEXT && strlen($payloadToDate) > 0 && preg_match('/^.{1}/us', $payloadToDate) != 1)
            {
                // not a valid payload - not valid UTF8
                $response->addError("readContent", "TEXT sequence has payload of invalid UTF-8");
                //echo hex_dump($payloadToDate);
                $this->sendCloseFrame($context, $connection, '');
                $failed = true;
            }
        }
         *
         */

        if (!$failed)
        {
            $response->decodeBody($payloadToDate);
        }
        else
        {
            $response->frames = array();
            $connection->disconnect($context);
        }

        // does the connection need to close?
        if ($response->connectionMustClose())
        {
            $connection->disconnect($context);
        }

        // all done
        return strlen($payloadToDate);
    }

    public function sendCloseFrame(Context $context, HttpClientConnection $connection, $appData)
    {
        $frame = new WsFrame();
        $frame->initAsCloseFrame()
              ->withApplicationData($appData)
              ->willSendFromClient();

        $connection->send((string)$frame);
    }

    public function sendPongFrame(Context $context, HttpClientConnection $connection, $appData)
    {
        $frame = new WsFrame();
        $frame->initAsPongFrame()
              ->withApplicationData($appData)
              ->willSendFromClient();

        $connection->send((string)$frame);
    }
}

if (!function_exists('hex_dump'))
{
    function hex_dump($data, $newline="\n")
    {
      static $from = '';
      static $to = '';

      static $width = 16; # number of bytes per line

      static $pad = '.'; # padding for non-visible characters

      if ($from==='')
      {
        for ($i=0; $i<=0xFF; $i++)
        {
          $from .= chr($i);
          $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
        }
      }

      $hex = str_split(bin2hex($data), $width*2);
      $chars = str_split(strtr($data, $from, $to), $width);

      $offset = 0;
      foreach ($hex as $i => $line)
      {
        echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
        $offset += $width;
      }
    }
}