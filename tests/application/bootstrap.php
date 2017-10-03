<?php

# Habilita a visualização de erros.
error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

# Define constantes.
define('BASE_PATH', realpath(dirname(__FILE__) . '/../../'));
define('APPLICATION_PATH', BASE_PATH . '/application');
define('APPLICATION_ENV', 'staging');
define('TESTING_MODE', true);

set_include_path(
    '.'
    . PATH_SEPARATOR . BASE_PATH . '/library'
    . PATH_SEPARATOR . get_include_path()
);

# Importa classes.
require_once 'Zend/Exception.php';
require_once 'Zend/Application.php';
require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Controller/Action.php';
require_once 'controllers/ControllerTestCase.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(false);
