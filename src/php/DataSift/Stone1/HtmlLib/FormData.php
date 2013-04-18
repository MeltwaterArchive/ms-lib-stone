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

/**
 * A simple mechanism for safely processing incoming data
 *
 * @category Libraries
 * @package  Stone1
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone1\HtmlLib;

use DataSift\Stone1\HttpLib\HttpData;

class FormData extends HttpData
{
    public function validateAgainstWidgets($widgets)
    {
        // let's get to it!
        foreach ($widgets as $widget)
        {
            $widget->sanitizeHttpData($this);
        }

        // now, what are the results?
        if (count($this->missingData) > 0 || count($this->validationErrors) > 0)
        {
            return false;
        }

        // if we get here, validation was successful, and the form data
        // is now ready to be consumed
        return true;
    }
}