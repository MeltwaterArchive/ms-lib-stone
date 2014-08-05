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
 * @package   Stone/DataLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\DataLib;

use ReflectionObject;
use ReflectionProperty;

/**
 * A helper class for converting a tree into dot notation
 *
 * @category  Libraries
 * @package   Stone/DataLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class DotNotationConvertor
{
    /**
     * convert a PHP variable to a dot notation list
     *
     * @param  mixed $mixed
     *         the variable to be converted
     *
     * @return string
     *         a printable string
     */
    public function convertToArray($mixed)
    {
        // our return value
        $return = [];

        // what are we looking at?
        if (is_object($mixed)) {
            $this->objectToArray($mixed, '', $return);
            return $return;
        }
        if (is_array($mixed)) {
            $this->arrayToArray($mixed, '', $return);
            return $return;
        }
        if (is_resource($mixed)) {
            $this->resourceToArray($mixed, '', $return);
            return $return;
        }

        // if we get here, then no complicated conversion required
        return [$mixed];
    }

    /**
     * convert an object to dot notation
     *
     * @param  object $obj
     *         the object to convert
     */
    public function objectToArray($obj, $prefix, &$return)
    {
        foreach ($obj as $name => $value) {
            if (is_scalar($value)) {
                $return[$prefix . $name] = $value;
            }
            else if (is_object($value)) {
                $this->objectToArray($value, $prefix . $name . '.', $return);
            }
            else if (is_array($value)) {
                $this->arrayToArray($value, $prefix . $name, $return);
            }
            else {
                $return[$prefix. $name] = null;
            }
        }
    }

    public function arrayToArray($array, $prefix, &$return)
    {
        foreach ($array as $index => $value) {
            if (is_scalar($value)) {
                $return[$prefix . '[' . $index . ']'] = $value;
            }
            else if (is_object($value)) {
                $this->objectToArray($value, $prefix . "[$index].", $return);
            }
            else if (is_array($value)) {
                $this->arrayToArray($value, $prefix . "[$index]", $return);
            }
            else {
                $return [$prefix . "[$index]"] = null;
            }
        }
    }
}
