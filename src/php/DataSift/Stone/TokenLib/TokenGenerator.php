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
 * @package   Stone/TokenLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Stone\TokenLib;

/**
 * Support for generating cryptographically-secure tokens
 *
 * @category Tools
 * @package  Hornet
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 * @link     http://crackstation.net/hashing-security.htm
 */

class TokenGenerator
{
	/**
	 * the separator between salt and hash when working with salted
	 * tokens
	 */
	const TOKEN_DELIMITER = '.$.';

	/**
	 * the app-unique string used when encrypting
	 * @var string
	 */
	private $secretKey;

	/**
	 * constructor
	 *
	 * @param string $secretKey
	 *        your application's unique secret key
	 * @see DataSift\Stone\TokenLib\TokenGenerator::setSecretKey()
	 */
	public function __construct($secretKey)
	{
		$this->setSecretKey($secretKey);
	}

	/**
	 * generate a hexadecimal token built from random data
	 *
	 * @param  integer $len
	 *         how long the final hexadecimal token should be
	 * @return string
	 *         the generated token
	 */
	public function generateToken($len = 32)
	{
		// how many bytes of random data do we need?
		$byteLen = $len / 2;

		// get some random data
		if (function_exists('mcrypt_create_iv')) {
			$randomBytes = mcrypt_create_iv($byteLen, MCRYPT_DEV_URANDOM);
		}
		else {
			$cryptoStrong = false;
			while (!$cryptoStrong) {
				$randomBytes = openssl_random_pseudo_bytes($byteLen, $cryptoStrong);
			}
		}

		// all done
		return bin2hex($randomBytes);
	}

	public function generateSaltedHash($passphrase, $salt=null, $cypher=MCRYPT_BLOWFISH)
	{
		// what encryption mode are we going to use?
		$encryptionMode = MCRYPT_MODE_CBC;

		// how long does the salt need to be?
		$saltSize = mcrypt_get_iv_size($cypher, $encryptionMode);

		// generate a salt if needed
		if ($salt === null) {
			$salt = $this->generateToken($saltSize);
		}

		// encode it
		$result = mcrypt_encrypt($cypher, $this->secretKey, $passphrase, $encryptionMode, $salt);

		// all done
		return $salt . TokenGenerator::TOKEN_DELIMITER . base64_encode($result);
	}


	/**
	 * get the token generator's secret key
	 *
	 * @return string
	 */
	public function getSecretKey() {
	    return $this->secretKey;
	}

	/**
	 * Set the token generator's secret key
	 *
	 * The secret key is used for creating salted hashes, and it should
	 * be unique to each of your applications.
	 *
	 * The whole point of using a secret key is to increase the cost of
	 * decrypting your hashed passwords when (not if) your passwords list
	 * gets stolen.
	 *
	 * Make sure that you don't store your secret key in the same place
	 * that you store your passwords in.  If your passwords are stored in
	 * a database, put the secret key somewhere else (a config file,
	 * perhaps, or an environment variable set in your Apache config file)
	 *
	 * @param string $newSecretKey
	 *        the secret key to use
	 *
	 * @return TokenGenerator
	 *         returns $this for fluent interface support
	 */
	public function setSecretKey($newSecretKey) {
	    $this->secretKey = $newSecretKey;

	    return $this;
	}
}