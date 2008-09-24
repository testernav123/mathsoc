<?php

// Todo: load below from config
// Todo: move load config and load template to separate files

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);
date_default_timezone_set('America/Toronto');

// directory setup and class loading
set_include_path('.' . PATH_SEPARATOR . '../libs/'
     . PATH_SEPARATOR . '../application/default/models'
     . PATH_SEPARATOR . get_include_path());

include "Zend/Loader.php";
Zend_Loader::registerAutoload();

// load configuration
$config = new Zend_Config_Ini('../config/main.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);

$config = new Zend_Config_Ini('../config/main.ini', 'exambank');

// Add database connection
$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);

// Todo: load access control
Zend_Loader::loadClass('Zend_Auth');

// Create the view and load in the ViewRenderer
include_once( '../application/default/views/helpers/initialize.inc' );

// Todo: add logging information to track users for use-case analysis

// setup controller
$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->addModuleDirectory('../application/');

// Add required routers
$router = $frontController->getRouter();

// Add routers for the ajax calls in the exambank
$router->addRoute('courses',
	new Zend_Controller_Router_Route('exambank/courses',
		array('module' => 'exambank', 'controller' => 'index', 'action' => 'courses')));
$router->addRoute('courses1',
	new Zend_Controller_Router_Route('exambank/courses/:prefix',
		array('module' => 'exambank', 'controller' => 'index', 'action' => 'courses')));
$router->addRoute('courses2',
	new Zend_Controller_Router_Route('exambank/courses/:prefix/:number',
		array('module' => 'exambank', 'controller' => 'index', 'action' => 'courses')));
$router->addRoute('exams',
	new Zend_Controller_Router_Route('exambank/exams/:prefix/:number',
		array('module' => 'exambank', 'controller' => 'index', 'action' => 'exams')));
$router->addRoute('exams1',
	new Zend_Controller_Router_Route('exambank/exams/:prefix/:number/:term/:type',
		array('module' => 'exambank', 'controller' => 'index', 'action' => 'exams')));
$router->addRoute('council',
	new Zend_Controller_Router_Route('council/minutes/:page',
		array('module' => 'council', 'controller' => 'index', 'action' => 'minutes')));
$router->addRoute('council1',
	new Zend_Controller_Router_Route('council/policies/:page',
		array('module' => 'council', 'controller' => 'index', 'action' => 'policies')));

/*
$router->addRoute('council',
	new Zend_Controller_Router_Route('position/:position', array('controller' => 'council', 'action' => 'positions')));
$router->addRoute('index',
	new Zend_Controller_Router_Route('users/:username', array('controller' => 'auth', 'action' => 'profile')));*/

// run!
$frontController->dispatch();

