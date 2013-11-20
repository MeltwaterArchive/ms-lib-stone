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
 * @category Libraries
 * @package  Stone/RateLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://datasift.github.io/stone
 */

namespace DataSift\Stone\RateLib;

use Phix_Project\ValidationLib4\Validator;
use Phix_Project\ValidationLib4\ValidationResult;

/**
 * Validate a rate limit passed on the command line
 *
 * @category Libraries
 * @package  Stone/RateLib
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://datasift.github.io/stone
 */
class MustBeValidRateLimit implements Validator
{
    const MSG_INVALIDFORMAT = "the rate limit must be '<amount><unit>/<time>'";
    const MSG_UNKNOWNUNIT   = "unrecognised rate size unit in '%value%'; must be one of: u, k, K, m, M, g, G";
    const MSG_UNKNOWNTIME   = "unsupported time period in '%value%'; must be one of: sec, min, qhour, hour, or day";

    public function validate($value, ValidationResult $result = null)
    {
        if ($result === null)
        {
            $result = new ValidationResult($value);
        }

        // special case - an unlimited data rate
        if ($value == 'unlimited') {
            // we're happy with that
            return $result;
        }

        // the rate limit is in the form:
        //
        // <amount><unit>/<time>

        $parts = explode('/', $value);
        if (count($parts) != 2) {
            $result->addError(static::MSG_INVALIDFORMAT);
            return $result;
        }
        if (empty($parts[0])) {
            $result->addError(static::MSG_INVALIDFORMAT);
            return $result;
        }

        // do we have a unit?
        $validUnits = array(
            'u' => true,
            'k' => true,
            'K' => true,
            'm' => true,
            'M' => true,
            'g' => true,
            'G' => true
        );
        $unit = substr($parts[0], -1, 1);
        if (!isset($validUnits[$unit])) {
            $result->addError(static::MSG_UNKNOWNUNIT);
            return $result;
        }

        // do we have a time period?
        $validTime = array(
            'sec' => true,
            'min' => true,
            'qhour' => true,
            'hour' => true,
            'day' => true
        );
        if(!isset($validTime[strtolower($parts[1])])) {
            $result->addError(static::MSG_UNKNOWNTIME);
            return $result;
        }

        // all done
        return $result;
    }
}
