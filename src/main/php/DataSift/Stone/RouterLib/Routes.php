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
 * @category  Tools
 * @package   Hornet
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

namespace DataSift\Stone\RouterLib;

use DataSift\Stone\LogLib\Log;

/**
 * A container for the routes that an application supports
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

class Routes
{
	/**
	 * our list of routes to support
	 * @var array
	 */
	protected $routes = array();

	const METHOD_GET  	= "GET";
	const METHOD_POST 	= "POST";
	const METHOD_PUT  	= "PUT";
	const METHOD_DELETE = "DELETE";

	/**
	 * add an additional route to our list of known routes
	 *
	 * @param string $verb
	 *        the HTTP verb that this route applies to
	 * @param string $pattern
	 *        the regex to match for this route
	 * @param string $controller
	 *        the controller script to transfer control to
	 */
	public function addRoute($verb, $pattern, $controller)
	{
		$this->routes[] = array (
			'verb'       => $verb,
			'pattern'    => $pattern,
			'controller' => $controller
		);
	}

	/**
	 * set the list of routes, replacing any existing routes in our list
	 *
	 * @param array $routes
	 *        the new list of routes to track
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}
}