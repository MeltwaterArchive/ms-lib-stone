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

/**
 * Helpers for dealing with API data
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\ApiLib;

use ReflectionObject;
use ReflectionProperty;

class ApiHelpers
{
    /**
     * convert a set of params (which may be an array, or which may be
     * a stdClass) into an array, for consumption elsewhere
     *
     * @param  mixed $params the parameters that need to be normalised
     * @return array
     */
    static public function normaliseParams($params)
    {
        // are the params already in array form?
        if (is_array($params))
        {
            // yes - nothing for us to do here
            return $params;
        }

        // if we get here, we need to conver the params into an array
        $paramsToSub = array();

        // get a list of the properties of the $params object
        $refObj   = new ReflectionObject($params);
        $refProps = $refObj->getProperties(ReflectionProperty::IS_PUBLIC);

        // convert each property into an array entry
        foreach ($refProps as $refProp)
        {
            $propName = $refProp->getName();
            $paramsToSub[$propName] = $params->$propName;
        }

        // return the array that we've built
        return $paramsToSub;
    }

    /**
     * Merge two sets of params into a single list
     *
     * @param  mixed $params1 first set of params to merge
     * @param  mixed $params2 second set of params to merge
     * @return array
     */
    static public function mergeParams($params1, $params2)
    {
        $paramsToMerge1 = self::normaliseParams($params1);
        $paramsToMerge2 = self::normaliseParams($params2);

        return array_merge($paramsToMerge1, $paramsToMerge2);
    }
}