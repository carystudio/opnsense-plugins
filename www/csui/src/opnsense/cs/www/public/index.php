<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Config\Adapter\Ini as ConfigIni;

define(
    "APP_PATH",
    realpath("..") . "/"
);

$csg2000p_config = new ConfigIni(
    APP_PATH . "app/config/config.ini"
);

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH.$csg2000p_config->application->controllersDir,
        APP_PATH.$csg2000p_config->application->modelsDir,
        APP_PATH.$csg2000p_config->application->commonDir,
        APP_PATH.$csg2000p_config->application->backendDir,
	'/usr/local/opnsense/mvc/app/library/',
	'/usr/local/opnsense/mvc/app/controllers/',
    ]
);

$loader->register();



// Create a DI
$di = new FactoryDefault();
//$config = "/usr/local/opnsense/mvc/app/config/config.php";
//include "/usr/local/opnsense/mvc/app/config/loader.php";
//include "/usr/local/opnsense/mvc/app/config/services.php";

// Setup the view component
$di->set(
    "view",
    function () use ($csg2000p_config) {
        $view = new View();

        $view->setViewsDir(APP_PATH . $csg2000p_config->application->viewsDir);

        return $view;
    }
);

// Setup a base URI so that all generated URIs include the "tutorial" folder
$di->set(
    "url",
    function () use ($csg2000p_config){
        $url = new UrlProvider();

        $url->setBaseUri(APP_PATH . $csg2000p_config->application->baseUri);

        return $url;
    }
);

$di->set('config', $csg2000p_config);

// Start the session the first time when some component request the session service
$di->setShared(
    "session",
    function () {
        $session = new Session();

        $session->start();

        return $session;
    }
);



$application = new Application($di);

try {
    // Handle the request
    $response = $application->handle();

    $response->send();
} catch (\Exception $e) {
    echo "Exception: ", $e->getMessage();
}

