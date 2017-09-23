<?php

    // from https://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873
    require_once(realpath(dirname(__FILE__) . "/../config.php"));

    function renderLayoutWithContentFile($contentFile, $variables = array())
    {
        $contentFileFullPath = TEMPLATES_PATH . "/" . $contentFile . ".html";

        require_once('/../vendor/mustache/mustache/src/Mustache/Autoloader.php');
	    Mustache_Autoloader::register();

	    // use .html instead of .mustache for default template extension
	    $options =  array('extension' => '.html');

	    $m = new Mustache_Engine(array(
		    'loader' => new Mustache_Loader_FilesystemLoader(TEMPLATES_PATH, $options),
	    ));

        // define variables which may be overriden by passed in variables
        //$home = '/';
        $home_class = '';
        $download_class = '';
        $search_class = '';
        $info_class = '';
        $contact_class = '';
        $other_scripts = '';
        $footerArray = array();
     
        // making sure passed in variables are in scope of the template
        // each key in the $variables array will become a variable
        if (count($variables) > 0) {
            foreach ($variables as $key => $value) {
                if (strlen($key) > 0) {
                    ${$key} = $value;
                }
            }
        }

        $headerArray = array(
            'home' => HOME,
            'home_class' => $home_class,
            'download_class' => $download_class,
            'search_class' => $search_class,
            'info_class' => $info_class,
            'contact_class' => $contact_class
            );
        echo $m->render('header', $headerArray);

        

        // check to make sure there is a bodyArray, if not call error page
        // also check file exists
        if ($bodyArray === False or !file_exists($contentFileFullPath)){
            echo $m->render('error', array('home'=>$home));
        }else{
            $bodyArray['home'] = HOME;
            echo $m->render($contentFile, $bodyArray);
        }

        echo $m->render('footer', $footerArray);
    }
?>