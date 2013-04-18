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

use ReflectionObject;
use ReflectionProperty;
use stdClass;

use DataSift\Stone1\ObjectLib\JsonObject;

/**
 * Base class to represent data received over HTTP
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class GenericDataItem extends JsonObject
{
    public $name = null;
    public $value = null;
    public $required = false;
    public $default = null;
    public $allowEmpty = true;

    // ==================================================================
    //
    // Helpers for working with data validation
    //
    // ------------------------------------------------------------------

    /**
     * get the filters to apply to HttpLib's HttpData::sanitizeKey()
     * @return array
     */
    protected function getDataFilters()
    {
        return array(
            $this->name => function($data) {
                return filter_var($data, FILTER_SANITIZE_STRING);
            }
        );
    }

    /**
     * get the validators to apply to HttpLib's HttpData::sanitizeKey()
     * @return array
     */
    protected function getDataValidators()
    {
        return array(
            $this->name => function($data, &$errors) {
                if (empty($data))
                {
                    $errors[] = "Cannot be empty";
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * get a list of the default values for this widget
     * @return array
     */
    protected function getDefaults()
    {
        return array($this->name => $this->default);
    }

    /**
     * sanitize the data represented by this class
     *
     * @param  HttpData $data the data container to assist
     * @return void
     */
    public function sanitizeHttpData(HttpData $data)
    {
        // get our list of validators and filters
        $validators = $this->getDataValidators();
        $filters    = $this->getDataFilters();
        $defaults   = $this->getDefaults();

        // apply the validators, filters, and defaults
        $success = true;
        foreach ($validators as $name => $validator)
        {
            if (!$data->sanitizeKey($name, $validator, $filters[$name], $this->required, $defaults[$name]))
            {
                $success = false;
            }
        }

        // did the data pass the tests?
        if (!$success)
        {
            // no, it did not
            return;
        }

        // do we need to combine the pieces of data into a single
        // data item?
        if (count($defaults) > 1)
        {
            // yes, we do
            $data->setFilteredData($this->name, $this->getCombinedValue($data));
        }
    }
}