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
 * Represents a given URL
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpAddress
{
    /**
     * The URL as a string, in the form:
     *
     * <scheme>://<hostname>[:<port>]/[<queryPath>][#fragment][?<params>]
     *
     * @var string
     */
    protected $rawAddress;

    /**
     * What protocol are we using? Normally 'http' or 'https'.
     * @var type
     */
    public $scheme = null;

    /**
     * The host we are connecting to
     * @var string
     */

    public $hostname = null;

    /**
     * The port we are connecting to.
     *
     * NOTE: PHP's parse_url() will not provide a port number if there isn't
     *       one in the rawAddress; we need to work this out for ourselves in
     *       that situation
     *
     * @var type
     */
    public $port = null;

    /**
     * The username we need to connect as (HTTP Basic Auth)
     * @var string
     */

    public $user = null;

    /**
     * The password to use when we connect (HTTP Basic Auth)
     * @var string
     */
    public $password = null;

    /**
     * The PATH section of the URL
     * @var string
     */
    public $path = null;

    /**
     * The query string section of the URL (everything after '?').
     * @var string
     */
    public $queryString = null;

    /**
     * The fragment section of the URL (everything after '#')
     * @var string
     */
    public $fragment = null;

    /**
     * Constructor.
     * @param string $addressString the URL we are representing
     */
    public function __construct($addressString)
    {
        $this->setAddress($addressString);
    }

    /**
     * Set the URL that we are representing.
     *
     * This is called by the constructor; you only need to call it yourself
     * if you're changing the URL for some reason.
     *
     * @param type $addressString
     */
    public function setAddress($addressString)
    {
        $parts = parse_url($addressString);
        if (!is_array($parts))
        {
            throw new Exception('unable to parse URL');
        }

        // okay, what do we have?
        static $components = array (
            'scheme'      => 'scheme',
            'hostname'    => 'host',
            'port'        => 'port',
            'user'        => 'user',
            'password'    => 'pass',
            'path'        => 'path',
            'queryString' => 'query',
            'fragment'    => 'fragment'
        );

        foreach ($components as $attribute => $index)
        {
            if (isset($parts[$index]))
            {
                $this->$attribute = $parts[$index];
            }
        }

        // fill in the blanks
        $method = 'postProcessSetAddress' . ucfirst($this->scheme);
        if (method_exists($this, $method))
        {
            call_user_func(array($this, $method));
        }

        // make __toString() very easy to do
        $this->rawAddress = $addressString;

        // at this point, we're all set to go :)
    }

    /**
     * fill in the blanks when we have a HTTP address
     */
    private function postProcessSetAddressHttp()
    {
        if ($this->port == null)
        {
            $this->port = 80;
        }
        if ($this->path == null)
        {
            $this->path = '/';
        }
    }

    /**
     * fill in the blanks when we have a HTTPS address
     */
    private function postProcessSetAddressHttps()
    {
        if ($this->port == null)
        {
            $this->port = 443;
        }
        if ($this->path == null)
        {
            $this->path = '/';
        }
    }

    /**
     * fill in the blanks when we have a WS address
     */
    private function postProcessSetAddressWs()
    {
        if ($this->port == null)
        {
            $this->port = 80;
        }
        if ($this->path == null)
        {
            $this->path = '/';
        }
    }

    /**
     * fill in the blanks when we have a WSS address
     */
    private function postProcessSetAddressWss()
    {
        if ($this->port == null)
        {
            $this->port = 443;
        }
        if ($this->path == null)
        {
            $this->path = '/';
        }
    }

    /**
     * Obtain the string that would be passed to a HTTP server when making
     * a GET request.
     *
     * @return string
     */
    public function getRequestLine()
    {
        $return = $this->path;
        if (isset($this->queryString))
        {
            $return .= '?' . $this->queryString;
        }

        if (isset($this->fragment))
        {
            $return .= '#' . $this->fragment;
        }

        return $return;
    }

    /**
     * Return the URL we represent as a string
     * @return string
     */
    public function __toString()
    {
        return $this->rawAddress;
    }

}