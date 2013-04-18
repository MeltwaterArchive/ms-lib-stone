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

use DataSift\Stone1\ApiLib\ApiHelpers;

/**
 * A simple HTTP server for use in publishers
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class HttpRedirector
{
    /**
     * @codeCoverageIgnore
     * @param  [type] $url    [description]
     * @param  [type] $params [description]
     * @return [type]
     */
    static public function send_302($url, $params)
    {
        header('HTTP/1.0 302 Temporarily moved');
        header('Location: ' . $url . self::convertParamsToQueryString($params));
        exit(0);
    }

    /**
     * @codeCoverageIgnore
     * @param  [type] $url    [description]
     * @param  [type] $params [description]
     * @return [type]
     */
    static public function send_303($url, $params)
    {
        header('HTTP/1.0 303 See Other');
        header('Location: ' . $url . self::convertParamsToQueryString($params));
        exit(0);
    }

    /**
     * Convert a set of params into the query string for a URL
     *
     * The parameters can either be an array, or an object.
     *
     * @param  mixed $params the params to convert
     * @return string
     */
    static public function convertParamsToQueryString($params)
    {
        // just in case we have an object instead of an array
        $paramsToConvert = ApiHelpers::normaliseParams($params);

        // do we have any parameters to convert?
        if (count($paramsToConvert) == 0)
        {
            // no, we do not
            return '';
        }

        // encode the parameters individually
        array_walk($paramsToConvert, array(__CLASS__, 'encodeParam'));

        // flatten the parameters
        $pairs = array();
        foreach ($paramsToConvert as $key => $value)
        {
            $pairs[] = $key . '=' . $value;
        }
        return '?' . join('&', $pairs);
    }

    /**
     * Callback for array_walk() call
     *
     * @param  string &$param the param to be encoded
     * @return void
     */
    static public function encodeParam(&$param)
    {
        $param = urlencode($param);
    }
}