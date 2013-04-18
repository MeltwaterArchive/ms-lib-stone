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

namespace DataSift\Stone\HtmlLib\Widgets;

use ReflectionObject;
use ReflectionProperty;
use stdClass;
use DataSift\Stone\HttpLib\GenericDataItem;

/**
 * Helper class for working with a generic config option
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class TextWidget extends GenericDataItem
{
    /**
     * Get a list of the properties that must be present if the user
     * submits a form
     *
     * @return array(string)
     */
    public function getExpectedConfigAsList()
    {
        // does the config state that this widget is required?
        if (isset($this->required) && $this->required)
        {
            // yes it does
            return array($this->name);
        }

        // not required - return an empty list
        return array();
    }

    /**
     * Get the fully-qualified filename of this widget's snippet file
     *
     * @param  string $format what output format is required?
     *                        default is html
     * @param  string $file   pass __FILE__ if you're overriding this class
     *                        default is the filename for the TextWidget class
     * @return string
     */
    public function getSnippetFileToInclude($format = 'html', $file = __FILE__)
    {
        // return the full path to the snippet file for this widget
        return dirname($file) . '/' . basename($file, '.php') . '.' . $format;
    }

    // ==================================================================
    //
    // Helpers for rendering widgets
    //
    // ------------------------------------------------------------------

    public function getRequiredAttr()
    {
        if (isset($this->required) && $this->required)
        {
            return 'required';
        }

        return '';
    }
}