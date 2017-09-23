<?php 
	class SgRNAController extends Controller{
		function index(){
			// Need to write something to redirect people to home 
			// Or could have complete table of sgRNAs...
		}
		
		function view($sgID){
			require_once(LIBRARY_PATH . "/SgRNAModel.php");
			$sgModel = new SgRNAModel($sgID);
			if ($sgModel){
				$sgPage = $sgModel->processSg();
			}else{
				$sgPage = False;
			}

			// Must pass in variables (as an array) to use in template
			$variables = array(
				'bodyArray' => $sgPage
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("sgRNA", $variables);
			
		}
	}
?>