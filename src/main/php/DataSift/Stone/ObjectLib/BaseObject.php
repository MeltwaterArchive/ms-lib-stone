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

namespace DataSift\Stone\ObjectLib;

use ReflectionObject;
use ReflectionProperty;
use stdClass;

/**
 * A base class to be a home for generic helper methods, at least until
 * we move to PHP 5.4 and traits
 *
 * Our emphasis here is to make it easier to work with classes w/out having
 * to create getters/setters for everything.  Not that there's anything
 * wrong with getters/setters, it's just that we can get away with writing
 * a lot less code in our particular JSON-driven environment :)
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class BaseObject extends stdClass
{
    /**
     * Do we have a named property set to non-null?
     *
     * @param  string $propName
     * @return boolean
     */
    public function has($propName)
    {
        return (isset($this->$propName));
    }

    /**
     * Is the named property set to true?
     *
     * @param  string $propName
     * @return boolean
     */
    public function is($propName)
    {
        return (isset($this->$propName) && $this->$propName);
    }

    /**
     * is the named property set to false?
     *
     * @param  string $propName
     * @return boolean
     */
    public function isNot($propName)
    {
        return (!isset($this->$propName) || $this->$propName != true);
    }

    /**
     * Do we have a named property, and is it a non-empty array?
     *
     * @param  string $propName
     * @return boolean
     */
    public function hasList($propName)
    {
        return (isset($this->$propName)
            && ((is_array($this->$propName) && count($this->$propName) > 0) ||
               (is_object($this->$propName))));
    }

    /**
     * retrieve the named property as an associative array, even if it is
     * actually an object
     *
     * @param  string $propName
     * @return array
     */
    public function getList($propName)
    {
        // do we have the property at all?
        if (!isset($this->$propName))
        {
            // no ... send back an empty list
            return array();
        }

        // is the property already a list?
        if (is_array($this->$propName))
        {
            // yes ... no conversion needed
            return $this->$propName;
        }

        // is the property something we can convert?
        if (is_object($this->$propName))
        {
            // yes
            $return = array();
            foreach ($this->$propName as $key => $value)
            {
                $return[$key] = $value;
            }

            return $return;
        }

        // if we get here, the property isn't naturally a list
        return array();
    }

    /**
     * return the named property as a string, or return the default if
     * the property isn't a string
     *
     * @param  string $propName name of property to retrieve
     * @param  string $default  default value to return if property not set
     * @return string
     */
    public function getString($propName, $default = '')
    {
        // does this property exist at all?
        if (!isset($this->$propName))
        {
            // no, so return the default
            return $default;
        }

        // is this property something that can be auto-converted to a
        // string reliably?
        if (is_string($this->$propName) || is_int($this->$propName) || is_double($this->$propName))
        {
            // yes
            return (string)$this->$propName;
        }

        // starting to clutch at straws now

        // a boolean, perhaps?
        if (is_bool(($this->$propName)))
        {
            if ($this->$propName)
            {
                return 'TRUE';
            }

            return 'FALSE';
        }

        // is it an object that can convert itself to a string?
        if (is_object($this->$propName))
        {
            $refObj = new ReflectionObject($this->$propName);
            if ($refObj->hasMethod('__toString'))
            {
                return (string)$this->$propName;
            }

            // sadly, the object cannot convert itself to a string
            return $default;
        }

        // add any other conversions in here

        // okay, we give up
        return $default;
    }

    /**
     * convert our public properties to an array
     *
     * @return array
     */
    public function getProperties_asList($prefix = null)
    {
        $return = array();

        // get a list of the properties of the $params object
        $refObj   = new ReflectionObject($this);
        $refProps = $refObj->getProperties(ReflectionProperty::IS_PUBLIC);

        // convert each property into an array entry
        foreach ($refProps as $refProp)
        {
            $propKey      = $refProp->getName();
            $retKey       = $propKey;

            // do we need to enforce the prefix?
            if ($prefix !== null && substr($this->$propKey, 0, strlen($prefix)) !== $prefix)
            {
                // yes we do
                $retKey = $prefix . $propKey;
            }

            // set the value
            $return[$retKey] = $this->$propKey;
        }

        // return the array that we've built
        return $return;
    }

    /**
     * magic method, called when there's an attempt to get a property
     * that doesn't actually exist
     *
     * @param  string $property name of the property being read
     * @return null
     */
    public function __get($property)
    {
        return null;
    }
}