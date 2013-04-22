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
 * @package   Stone/DataLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\DataLib;

use ReflectionObject;
use ReflectionProperty;

/**
 * A helper class for working with potentially unprintable data
 *
 * @category Libraries
 * @package  Stone/DataLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class DataPrinter
{
    /**
     * used when we want arrays / objects to be converted to JSON
     * format
     */
    const JSON_FORMAT = "convertToJson";

    /**
     * used when we want arrays / objects to be converted using
     * PHP's print_r() function
     */
    const PRINTR_FORMAT = "convertToPrintr";

    /**
     * used when we want arrays / objects to be converted using
     */
    const VAREXPORT_FORMAT = "convertToVarexport";

    /**
     * the name of the method to call to convert arrays / objects
     * to strings
     *
     * @var string
     */
    protected $convertor;

    /**
     * constructor. indicate how we want arrays / objects converted
     * to strings
     *
     * @param string $format
     *        use one of the *_FORMAT constants
     */
    public function __construct($format = self::JSON_FORMAT)
    {
        $this->convertor = $format;
    }

    /**
     * convert a PHP variable to a printable string
     *
     * @param  mixed $mixed
     *         the variable to be converted
     *
     * @return string
     *         a printable string
     */
    public function convertToString($mixed)
    {
        // what are we looking at?
        if (is_object($mixed)) {
            return $this->objectToString($mixed);
        }
        if (is_array($mixed)) {
            $convertor = $this->convertor;
            return $this->$convertor($mixed);
        }
        if (is_resource($mixed)) {
            return $this->resourceToString($mixed);
        }

        // if we get here, then no complicated conversion required
        return (string)$mixed;
    }

    /**
     * convert an object to a printable string
     *
     * if the object defines a string convertor method (the __toString()
     * method), then we will use that.
     *
     * if it doesn't, then we'll convert it to the format requested
     * in our constructor
     *
     * @param  object $obj
     *         the object to convert
     *
     * @return string
     *         a printable string
     */
    public function objectToString($obj)
    {
        // does the object have a '__toString' method?
        if (method_exists($obj, '__toString')) {
            // yes - let PHP convert it for us
            return (string)$obj;
        }

        // no, object does not have a string convertor predefined
        $convertor = $this->convertor;
        return $this->$convertor($obj);
    }

    /**
     * convert a PHP resource to a printable string
     *
     * @param  resource $resource
     *         the resource to convert
     *
     * @return string
     *         a printable string
     */
    public function resourceToString($resource)
    {
        // nothing else we can do but print a static string
        //
        // it would be great if we could see inside these just a little
        // bit one day
        return "(PHP resource)";
    }

    /**
     * convert an array or object to JSON-encoding
     *
     * @param  array|object $input
     *         the variable to convert
     *
     * @return string
     *         a JSON-encoded string
     */
    public function convertToJson($input)
    {
        return json_encode($input);
    }

    /**
     * convert an array or object to print_r() format
     *
     * @param  array|object $input
     *         the variable to convert
     *
     * @return string
     *         a string created by print_r()
     */
    public function convertToPrintr($input)
    {
        return print_r($input, true);
    }

    /**
     * convert an array or object to var_export() format
     *
     * @param  array|object $input
     *         the variable to convert
     *
     * @return string
     *         a string created by var_export()
     */
    public function convertToVarexport($input)
    {
        return var_export($input, true);
    }
}
