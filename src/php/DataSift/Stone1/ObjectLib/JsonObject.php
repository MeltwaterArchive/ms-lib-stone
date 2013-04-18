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

namespace DataSift\Stone1\ObjectLib;

use ReflectionObject;
use ReflectionProperty;
use stdClass;

/**
 * Adds support for initialising an object from an object that was either
 * loaded from a JSON file on disk, or received in JSON format over the
 * net
 *
 * @category Tools
 * @package  Hornet
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class JsonObject extends BaseObject
{
    /**
     * Copy all of the properties from another object (loaded from a
     * .json file or received over the net) into our own properties
     *
     * By default, we convert any objects nested inside the other object
     * into JsonObjects too, so that all the helpers defined in BaseObject
     * are available.
     *
     * @param  mixed $json the object to copy
     * @return void
     */
    public function initFromJson($json, $deep = true)
    {
        // special case - do we have a basic value?
        if (!is_object($json) && !is_array($json))
        {
            $this->value = $json;
            return;
        }

        // take advantage of PHP's ability to iterate over an object
        // and its properties
        foreach ($json as $key => $value)
        {
            if ($deep && is_object($value) && !($value instanceof JsonObject))
            {
                $this->$key = $this->convertObject($value);
            }
            else if ($deep && is_array($value))
            {
                $this->$key = $this->convertArray($value);
            }
            else
            {
                $this->$key = $value;
            }
        }
    }

    private function convertObject($json)
    {
        $return = new JsonObject();
        $return->initFromJson($json);

        return $return;
    }

    private function convertArray($json)
    {
        $mustConvert = false;

        // special case - do we actually need to do any conversion?
        $keys = array_keys($json);
        foreach ($keys as $key)
        {
            if (is_object($json[$key]) || is_array($json[$key]))
            {
                $mustConvert = true;
                break;
            }
        }

        // can we just send back what we have received?
        if (!$mustConvert)
        {
            // yes :)
            return $json;
        }

        $return = array();
        foreach ($json as $key => $value)
        {
            if (is_object($value))
            {
                $return[$key] = $this->convertObject($value);
            }
            else if (is_array($value))
            {
                $return[$key] = $this->convertArray($value);
            }
            else
            {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}