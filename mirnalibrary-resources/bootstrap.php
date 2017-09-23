<?php
    // Use the autoloader from composer
    require_once __DIR__ . '/vendor/autoload.php';

    // load config file
    require_once __DIR__ .'/config.php';
    // load database
    require_once __DIR__ .'/database.php';

    define("HOME", "http://mirnalibrary.jessicakurata.com");

    defined("RESOURCE_PATH")
        or define("RESOURCE_PATH", realpath(dirname(__FILE__)));

    defined("LIBRARY_PATH")
        or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));
    
    defined("CONTROLLER_PATH")
        or define("CONTROLLER_PATH", realpath(dirname(__FILE__) . '/controllers'));

    defined("VENDOR_PATH")
        or define("VENDOR_PATH", realpath(dirname(__FILE__) . '/vendor'));
        
    defined("TEMPLATES_PATH")
        or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

    // Load classes
    // This is slow and should be moved to autoloader at some point
	require_once CONTROLLER_PATH . '/Controller.php';
    require_once CONTROLLER_PATH . '/IndexController.php';
    require_once CONTROLLER_PATH . '/PriMiRController.php';
    require_once CONTROLLER_PATH . '/ContactController.php';
    require_once CONTROLLER_PATH . '/DownloadController.php';
    require_once CONTROLLER_PATH . '/InformationController.php';
    require_once CONTROLLER_PATH . '/SearchController.php';
    require_once CONTROLLER_PATH . '/SgRNAController.php';

    require_once LIBRARY_PATH . '/Model.php';
?>