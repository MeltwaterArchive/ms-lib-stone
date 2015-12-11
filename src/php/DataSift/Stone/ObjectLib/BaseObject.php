<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Stone/ObjectLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\ObjectLib;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use ReflectionObject;
use ReflectionProperty;
use stdClass;

use Twig_Environment;
use Twig_Loader_String;

/**
 * A base class to be a home for generic helper methods, at least until
 * we move to PHP 5.5 and traits
 *
 * We're skipping PHP 5.4 because of production problems with APC
 *
 * Our emphasis here is to make it easier to work with classes w/out having
 * to create getters/setters for everything.  Not that there's anything
 * wrong with getters/setters, it's just that we can get away with writing
 * a lot less code in our particular JSON-driven environment :)
 *
 * @category  Libraries
 * @package   Stone/ObjectLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class BaseObject extends stdClass implements ArrayAccess, IteratorAggregate
{
    // ====================================================================
    //
    // merging two objects together
    //
    // --------------------------------------------------------------------

    /**
     * Copy all of the properties from another object (loaded from a
     * .json file or received over the net) into our own properties
     *
     * We convert any objects nested inside the other object into
     * BaseObjects too, so that all the helpers defined in BaseObject
     * are available.
     *
     * @param  mixed $src the object to copy
     * @return void
     */
    public function mergeFrom($src)
    {
        // special case - do we have a basic value?
        if (!is_object($src) && !is_array($src))
        {
            // as we don't know what else to call it, we'll just add it
            // to 'value'
            $this->value = $src;

            // nothing more to do here
            return;
        }

        // if we get here, then we have a complex structure to evaluate,
        // which we're going to handle recursively
        $this->mergeInto($this, $src);
    }

    /**
     * merge a (possibly complex) object or array into our (possibly complex)
     * object or array
     *
     * along the way, we'll convert any plain old stdClass objects into
     * funky new BaseObjects
     *
     * this was originally created for supporting merging two or more
     * config files (e.g. a default one, and an overrides file) into
     * one usable data structure
     *
     * PLEASE NOTE that this method is recursive, which will cause
     * problems if you go nuts on how deep your data structures are
     *
     * @param  object|array $ours
     *         the array or object that we're adding to
     * @param  object|array $theirs
     *         the array or object that we're merging from
     * @return object|array
     *         $ours, after we've merged across from $theirs
     */
    private function mergeInto($ours, $theirs)
    {
        // let's see what is inside their object
        foreach ($theirs as $key => $value)
        {
            // special case - conversion only
            if (!isset($ours->$key))
            {
                // what are we looking at inside their object?
                if (is_object($value) && ($value instanceof stdClass))
                {
                    // for convenience, turn stdClass into more BaseObjects
                    $ours->$key = $this->convertObject($value);
                }
                else if (is_array($value))
                {
                    // for convenience, turn any stdClass objects that are
                    // inside this array into more BaseObjects
                    $ours->$key = $this->convertArray($value);
                }
                else
                {
                    $ours->$key = $value;
                }

                // all done converting
                continue;
            }

            // we have a clash ...
            //
            // if ours and theirs are incompatible data types, we will
            // have to just overwrite
            //
            // this is how we handle merging into an existing object
            if (is_object($ours->$key))
            {
                if (!is_object($value))
                {
                    // cannot merge object and non-object
                    $ours->$key = $value;
                    continue;
                }

                if (!$ours->$key instanceof stdClass || !$value instanceof stdClass)
                {
                    // we cannot merge any complex classes at all
                    $ours->$key = $value;
                    continue;
                }

                // if we get here, then we have two compatible objects to merge
                //
                // we don't know what's inside their object ... recursion to
                // the rescue :(
                $ours->$key = $this->mergeInto($ours->$key, $value);
                continue;
            }

            // this is how we handle merging into an existing array
            if (is_array($ours->$key))
            {
                if(!is_array($value))
                {
                    // cannot merge array and non-array
                    $ours->$key = $value;
                    continue;
                }

                // if we get here, then we have two arrays to merge
                $ours->$key = $this->mergeInfo($ours->$key, $value);
                continue;
            }

            // if we get here, then we have nothing that we can merge,
            // and can only overwrite
            $ours->$key = $value;
        }

        // all done
        return $ours;
    }

    /**
     * convert a stdClass into a BaseObject
     *
     * this does a deep conversion, and therefore can be recursive
     *
     * @param  stdClass $src
     *         the object that we want to convert
     * @return BaseObject
     *         the replacement object
     */
    private function convertObject(stdClass $src)
    {
        $return = new BaseObject();
        $return->mergeFrom($src);

        return $return;
    }

    /**
     * look inside an array, to see if there are any stdClass objects
     * that need converting into BaseObjects
     *
     * this does a deep conversion, and therefore can be recursive
     *
     * @param  array $src
     *         the array to convert
     * @return array
     *         the replacement array
     */
    private function convertArray($src)
    {
        $mustConvert = false;

        // do we actually need to do any conversion?
        // let's inspect the array to make a decision
        $keys = array_keys($src);
        foreach ($keys as $key)
        {
            // we only want to convert in two cases
            //
            // we find a stdClass object (must be converted)
            // we find a nested array (we must look deeper)
            if ((is_object($src[$key]) && $src[$key] instanceof stdClass) || is_array($src[$key]))
            {
                $mustConvert = true;
                break;
            }
        }

        // can we just send back what we have received?
        if (!$mustConvert)
        {
            // yes :)
            return $src;
        }

        // if we get here, then we need to convert what we have
        //
        // this is what we'll send back to the caller
        $return = array();

        // let's iterate over the array once more
        foreach ($src as $key => $value)
        {
            // do we have a convertable object?
            if (is_object($value) && $value instanceof stdClass)
            {
                // yes - convert it
                $return[$key] = $this->convertObject($value);
            }
            // do we have a nested array?
            else if (is_array($value))
            {
                // yes - let's look inside it, in case there are stdClass
                // objects lurking within
                $return[$key] = $this->convertArray($value);
            }
            else
            {
                // whatever we have, it is not convertable
                // just copy it across
                $return[$key] = $value;
            }
        }

        // all done - return the replacement array to the caller
        return $return;
    }

    // ====================================================================
    //
    // Helpers
    //
    // These are mostly syntactic sugar, but they help a tiny bit with
    // code robustness because they can deal correctly with unset properties,
    // something many developers forget to check for
    //
    // --------------------------------------------------------------------

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
     * @param string prefix
     *        an optional prefix to stick on the front of the property
     *        to form our array keys
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
     * do we have any properties defined?
     *
     * @return boolean
     *         TRUE if there are any public properties
     *         FALSE otherwise
     */
    public function hasProperties()
    {
        // get a list of our properties
        $refObj   = new ReflectionObject($this);
        $refProps = $refObj->getProperties(ReflectionProperty::IS_PUBLIC);

        // do we have any?
        if (count($refProps)) {
            // yes we do
            return true;
        }

        // if we get here, we do not
        return false;
    }

    /**
     * magic method, called when there's an attempt to get a property
     * that doesn't actually exist
     *
     * @param  string $property name of the property being read
     * @throws E5xx_NoSuchProperty
     */
    public function __get($property)
    {
        throw new E5xx_NoSuchProperty(get_class($this), $property);
    }

    /**
     * magic method, called when a BaseObject is cloned
     *
     * our default behaviour is to perform a deep clone
     *
     * @return void
     */
    public function __clone()
    {
        // deep-clone!
        foreach ($this as $key => $val) {
            if (is_object($val) || is_array($val)) {
                $this->$key = unserialize(serialize($val));
            }
        }
    }

    // ==================================================================
    //
    // Twig variable support
    //
    // ------------------------------------------------------------------

    /**
     * expand any variables in $this
     *
     * @param  array|object $baseData
     *         the config to use for expanding variables (optional)
     * @return array|object
     *         a copy of the config stored in $this, with any Twig
     *         variables expanded
     */
    public function getExpandedData($baseData = null)
    {
        return $this->expandData($this, $baseData);
    }

    /**
     * expand any piece of data
     *
     * @param  mixed $dataToExpand
     *         the data to run through Twig
     * @param  array|object $baseData
     *         the config to use for expanding variables (optional)
     * @return array|object
     *         a copy of the config stored in $this, with any Twig
     *         variables expanded
     */
    protected function expandData($dataToExpand, $baseData = null)
    {
        // special case - is the data expandable in the first place?
        if (!is_object($dataToExpand) && !is_array($dataToExpand) && !is_string($dataToExpand)) {
            return $dataToExpand;
        }

        // we're going to use Twig to expand any parameters in our
        // config
        //
        // this seems horribly inefficient, but it does work reliably
        $loader = new Twig_Loader_String();
        $templateEngine   = new Twig_Environment($loader);

        // Twig is a template engine. it needs a text string to operate on
        $configString = json_encode($dataToExpand);

        // Twig needs an array of data to expand variables from
        if ($baseData === null) {
            $baseData = $this;
        }
        $varData = json_decode(json_encode($baseData), true);

        // use Twig to expand any config variables
        $raw = $templateEngine->render($configString, $varData);
        $expandedData = json_decode($raw);

        // make sure we have our handy BaseObject, because it does nice
        // things like throw exceptions when someone tries to access an
        // attribute that does not exist
        if (is_object($expandedData)) {
            $tmp = new BaseObject();
            $tmp->mergeFrom($expandedData);
            $expandedData = $tmp;
        }
        else if (is_array($expandedData)) {
            foreach (array_keys($expandedData) as $key) {
                if (is_object($expandedData[$key])) {
                    $tmp = new BaseObject();
                    $tmp->mergeFrom($expandedData[$key]);
                    $expandedData[$key] = $tmp;
                }
            }
        }

        // all done
        return $expandedData;
    }

    // ==================================================================
    //
    // dot.notation.support
    //
    // ------------------------------------------------------------------

    /**
     * retrieve data using a dot.notation.path
     *
     * NOTE that you should treat any data returned from here as READ-ONLY
     *
     * @param  string $path
     *         the dot.notation.path to use to navigate
     *
     * @return mixed
     */
    public function getData($path)
    {
        // special case
        if (empty($path)) {
            return $this;
        }

        $retval = $this->getPath($path);
        $retval = $this->expandData($retval);

        return $retval;
    }

    protected function &getPath($path, $expandPath = false)
    {
        // special case
        if (empty($path)) {
            return $this;
        }

        // this is where we start from
        $retval = $this;

        // this is where we have been so far, for error-reporting
        // purposes
        $pathSoFar = [];

        // walk down the path
        $parts = explode(".", $path);
        foreach ($parts as $part)
        {
            if (is_object($retval)) {
                if (isset($retval->$part)) {
                    $retval = &$retval->$part;
                }
                else if ($expandPath) {
                    $retval->$part = new BaseObject;
                    $retval = &$retval->$part;
                }
                else {
                    throw new E4xx_PathNotFound($path);
                }
            }
            else if (is_array($retval)) {
                if (isset($retval[$part])) {
                    $retval = &$retval[$part];
                }
                else if ($expandPath) {
                    $retval[$part] = new BaseObject;
                    $retval = &$retval[$part];
                }
                else {
                    throw new E4xx_PathNotFound($path);
                }
            }
            else {
                // we can go no further
                if ($expandPath) {
                    throw new E4xx_PathCannotBeExtended($path, implode('.', $pathSoFar), gettype($retval));
                }
                else {
                    throw new E4xx_PathNotFound($path);
                }
            }

            // remember where we have been, in case we need to report
            // and error soon
            $pathSoFar[] = $part;
        }

        // if we get here, we have walked the whole path
        return $retval;
    }

    /**
     * retrieve data from a dot.notation.path
     *
     * throws an exception if the path does not point to an array
     *
     * @param string $path
     *        the dot.notation.path to the data to return
     * @return array
     */
    public function getArray($path)
    {
        $retval = $this->getPath($path);

        if (!is_array($retval)) {
            throw new E4xx_PropertyNotAnArray($path);
        }

        $retval = (array)$this->expandData($retval);

        return $retval;
    }

    /**
     * retrieve data from a dot.notation.path
     *
     * throws an exception if the path does not point to an object
     *
     * @param string $path
     *        the dot.notation.path to the data to return
     * @return object
     */
    public function getObject($path)
    {
        $retval = $this->getPath($path);

        if (!is_object($retval)) {
            throw new E4xx_PropertyNotAnObject($path);
        }

        $retval = $this->expandData($retval);

        return $retval;
    }

    /**
     * check for existence of data using a dot.notation.path
     *
     * @param  string $path
     *         the dot.notation.path to use to navigate
     *
     * @return boolean
     */
    public function hasData($path)
    {
        // special case
        if (empty($path)) {
            return true;
        }

        // walk down the path
        $parts = explode(".", $path);

        // this is where we start from
        $retval = $this;

        foreach ($parts as $part)
        {
            if (is_object($retval)) {
                if (isset($retval->$part)) {
                    $retval = $retval->$part;
                }
                else {
                    return false;
                }
            }
            else if (is_array($retval)) {
                if (isset($retval[$part])) {
                    $retval = $retval[$part];
                }
                else {
                    return false;
                }
            }
            else {
                // we can go no further
                return false;
            }
        }

        // if we get here, we have walked the whole path
        return true;
    }

    /**
     * merge data into this config
     *
     * @param  string $path
     *         path.to.merge.to
     * @param  mixed $dataToMerge
     *         the data to merge at $path
     * @return void
     */
    public function mergeData($path, $dataToMerge)
    {
        // special case - we treat objects and arrays differently to other
        // data types
        if (!is_object($dataToMerge) && !is_array($dataToMerge)) {
            // we just need to set the data instead
            $this->setData($path, $dataToMerge);
            return;
        }

        // get to where we need to be
        $leaf =& $this->getPath($path, true);

        // if we get here, then we know where we are adding the new
        // data
        if (is_object($leaf)) {
            $leaf->mergeFrom($dataToMerge);
        }
        else if (is_array($leaf)) {
            $leaf = array_merge($leaf, $dataToMerge);
        }
        else {
            throw new E4xx_PathCannotBeExtended($path, $path, gettype($leaf));
        }

        // all done
    }

    /**
     * assigns data to a specific path
     *
     * @param string $path
     *        the path to assign to
     * @param mixed $data
     *        the data to assign
     */
    public function setData($path, $data)
    {
        // special case - empty path
        if (empty($path)) {
            $this->mergeFrom($data);
            return;
        }

        // walk down the path
        $parts = explode(".", $path);
        $lastPart = end($parts);
        $parts = array_slice($parts, 0, count($parts) - 1);

        // create the path
        $leaf =& $this->getPath(implode(".", $parts), true);

        // if we get here, then we know where we are adding the new
        // data
        if (is_array($leaf)) {
            $leaf[$lastPart] = $data;
        }
        else if (is_object($leaf)) {
            $leaf->$lastPart = $data;
        }
        else {
            // we cannot add to this
            throw new E4xx_PathCannotBeExtended($path, implode(".", $parts), gettype($leaf));
        }

        // all done
    }

    /**
     * remove data using a dot.notation.path
     *
     * @param  string $path
     *         the dot.notation.path to use to navigate
     *
     * @return void
     */
    public function unsetData($path)
    {
        // walk down the path
        $parts = explode(".", $path);
        $lastPart = end($parts);
        $parts = array_slice($parts, 0, count($parts) - 1);

        // this is where we start from
        $retval =& $this->getPath(implode(".", $parts));

        // if we get here, we have walked the whole path, and are ready
        // to unset the value
        if (is_object($retval)) {
            if (isset($retval->$lastPart)) {
                unset($retval->$lastPart);
            }
            else {
                throw new E4xx_PathNotFound($path);
            }
        }
        else if (is_array($retval)) {
            if (isset($retval[$lastPart])) {
                unset($retval[$lastPart]);
            }
            else {
                throw new E4xx_PathNotFound($path);
            }
        }
        else {
            throw new E4xx_PathNotFound($path);
        }
    }

    /**
     * support foreach() loops over our data
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    // ==================================================================
    //
    // ArrayAccess support
    //
    // ------------------------------------------------------------------

    /**
     * does this array key exist?
     */
    public function offsetExists ($offset)
    {
        return isset($this->$offset);
    }

    /**
     * retrieve data from our databag
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * set a value in our databag
     *
     * @param  mixed $offset
     *         the array key to use
     * @param  mixed $value
     *         the value to store
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // special case - we cannot cope with NULL
        if ($offset === null) {
            throw new InvalidArgumentException("BaseObject does not support appending to the end of an array");
        }

        $this->$offset = $value;
    }

    /**
     * delete a value from our databag
     *
     * @param  mixed  $offset
     *         the array key to delete
     * @return void
     */
    public function offsetUnset($offset)
    {
        // NOTE: it is NOT an error to attempt to unset() something
        // that does not exist
        if (isset($this->$offset)) {
            unset($this->$offset);
        }
    }

    /**
     * convert ourselves into a genuine PHP array
     *
     * @return array
     */
    public function toArray()
    {
        $retval = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value) && method_exists($value, "toArray")) {
                $retval[$key] = $value->toArray();
            }
            else {
                $retval[$key] = $value;
            }
        }

        // all done
        return $retval;
    }
}
