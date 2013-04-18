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

namespace DataSift\Stone1\HtmlLib;

use stdClass;
use DataSift\Stone1\LogLib\Log;

/**
 * Helper class for loading widget classes on demand
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class WidgetLoader
{
    /**
     * Look in all the right places for the class that represents a named
     * widget.
     *
     * If there's no specific class for the widget, this will fallback to
     * loading the generic TextWidget class (which all widgets should have
     * extended anyway)
     *
     * @param  string $name                 the name of the widget to load
     * @param  array  $additionalNamespaces where to look first for the widget
     *                                      (we append the 'Widgets' bit to the end of each of these namespaces)
     * @return TextWidget
     */
    static public function newWidget($name, $additionalNamespaces = array())
    {
        $classesToLookFor = array();
        foreach ($additionalNamespaces as $additionalNamespace)
        {
            $classesToLookFor[] = $additionalNamespace . '\\Widgets\\' . $name . 'Widget';
        }

        // add in our two fallbacks
        $classesToLookFor[] = 'DataSift\\Stone1\\HtmlLib\\Widgets\\' . $name . 'Widget';
        $classesToLookFor[] = 'DataSift\\Stone1\\HtmlLib\\Widgets\\TextWidget';

        // go searching!
        foreach ($classesToLookFor as $classToLookFor)
        {
            Log::write(Log::LOG_DEBUG, "Looking for widget class " . $classToLookFor);
            if (class_exists($classToLookFor))
            {
                $widget = new $classToLookFor;
                return $widget;
            }
        }

        // if we get here, the widget does not exist!
        throw new E5xx_UnknownWidget($name);
    }
}