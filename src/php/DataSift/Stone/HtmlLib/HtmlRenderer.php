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

namespace DataSift\Stone\HtmlLib;

use Twig_Environment;
use Twig_Loader_Filesystem;

class HtmlRenderer
{
	public function __construct($theme = 'html')
	{
		$this->loader = new Twig_Loader_Filesystem(APP_DIR . '/templates/html');
		$this->twig   = new Twig_Environment($this->loader, array (
			'cache' => APP_DIR . '/tmp/twig-cache'
		));
	}

	public function renderDataUsingTemplate($data, $templateName)
	{
		$template = $this->twig->loadTemplate($templateName);
		$template->display($data);
	}
}