<?php 
	class IndexController extends Controller{
		public function index(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'home_class' => 'active',
				'bodyArray' => array()
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("home", $variables);
		}
	}
?>