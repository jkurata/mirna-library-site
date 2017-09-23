<?php 
	class DownloadController extends Controller{
		public function index(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'download_class' => 'active',
				'bodyArray' => array()
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("download", $variables);
		}
	}
?>