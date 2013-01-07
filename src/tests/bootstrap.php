<?php

// namespaced classes that we want to use
use Phix_Project\Autoloader4\PSR0_Autoloader;
use Phix_Project\Autoloader4\Autoloader_Path;

// load our autoloader
require_once (__DIR__ . '/../../vendor/php/Phix_Project/Autoloader4/PSR0/Autoloader.php');

// start autoloading
PSR0_Autoloader::startAutoloading();

// add in our vendor/ folder
Autoloader_Path::searchFirst(realpath(__DIR__ . '/../../vendor/php/'));

// add in our test classes
Autoloader_Path::searchFirst(__DIR__ . '/php');

// add in our code folder
Autoloader_Path::searchFirst(__DIR__ . '/../main/php');

// we're now fully bootstrapped