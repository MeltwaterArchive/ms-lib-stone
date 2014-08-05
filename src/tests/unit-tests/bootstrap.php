<?php

// =========================================================================
//
// tests/bootstrap.php
//		A helping hand for running our unit tests
//
// Author	Stuart Herbert
//		(stuart@stuartherbert.com)
//
// Copyright	(c) 2011 Stuart Herbert
//		Released under the New BSD license
//
// =========================================================================

use Phix_Project\Autoloader4\PSR0_Autoloader;
use Phix_Project\Autoloader4\Autoloader_Path;

// step 1: create the APP_TOPDIR constant that all components require
define('APP_TOPDIR', realpath(__DIR__ . '/../../../src/php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../../vendor'));
define('APP_TESTDIR', realpath(__DIR__ . '/php'));

// step 2: find the autoloader, and install it
require_once(APP_LIBDIR . '/phix/autoloader/src/php/Phix_Project/Autoloader4/PSR0/Autoloader.php');
require_once(APP_LIBDIR . '/phix/autoloader/src/php/Phix_Project/Autoloader4/Autoloader/Path.php');

// step 3: add the additional paths to the include path
Autoloader_Path::searchFirst(APP_LIBDIR);
Autoloader_Path::searchFirst(APP_TESTDIR);
Autoloader_Path::searchFirst(APP_TOPDIR);

// step 4: enable autoloading
PSR0_Autoloader::startAutoloading();

// step 5: enable ContractLib if it is available
if (class_exists('Phix_Project\ContractLib\Contract'))
{
        \Phix_Project\ContractLib\Contract::EnforceWrappedContracts();
}