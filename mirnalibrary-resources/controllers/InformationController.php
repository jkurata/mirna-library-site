<?php 
	class InformationController extends Controller{
		public function index(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'info_class' => 'active',
				'bodyArray' => array()
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("information", $variables);
		}

		public function crispr(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'info_class' => 'active',
				'bodyArray' => array()
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("crispr", $variables);
		}

		public function microrna(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'info_class' => 'active',
				'bodyArray' => array()
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("microrna", $variables);
		}
	}
?>