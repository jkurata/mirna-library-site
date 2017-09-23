<?php    
    require_once '../../mirnalibrary-resources/bootstrap.php';
    
    // handles routing
    use Phroute\Phroute\RouteCollector;
    use Phroute\Phroute\Dispatcher;

    // examines if the url is the result of a search and needs to be reformated
	if (isset(parse_url($_SERVER["REQUEST_URI"])["query"])){
		include RESOURCE_PATH . "/redirect.php";
	}
    
    $router = new RouteCollector();
    // [ClassName, method]
    $router->group(['prefix' => 'Contact'], function($router){
        $router -> post('submitted', ['ContactController', 'submitted']);
        $router -> get('/', ['ContactController', 'index']);
        });
    $router->get('Download', ['DownloadController', 'index']);
    $router->group(['prefix' => 'Information'], function($router){
        $router->get('/CRISPR', ['InformationController', 'crispr']);
        $router->get('/miRNAs', ['InformationController', 'microrna']);
        $router->get('/', ['InformationController', 'index']);
    });
    $router->group(['prefix' => 'Search'], function($router){
        $router -> any('/results/{type}/{term}', ['SearchController', 'results']);
        $router -> get('/', ['SearchController', 'index']);
        });
    $router->group(['prefix' => 'PriMiR'], function($router){
        $router -> any('/view/{id}', ['PriMiRController', 'view']);
        });
    $router->group(['prefix' => 'SgRNA'], function($router){
        $router -> any('/view/{id}', ['SgRNAController', 'view']);
        });
    $router->any('/', ['IndexController', 'index']);

    $dispatcher = new Dispatcher($router->getData());
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
 
?>