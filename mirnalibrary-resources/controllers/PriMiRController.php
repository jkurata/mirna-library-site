<?php 
	class PriMiRController extends Controller{
		function index(){
			// Need to write something to redirect people to home 
			// Or could have complete table of primiRNAs...
		}
		
		function view($priID){
			require_once LIBRARY_PATH . "/PriMiRModel.php";
			$priModel = new PriMiRModel($priID);
			if ($priModel){
				$priPage = $priModel->processPri();
			}else{
				$priPage = False;
			}
			$footerArray = array("other-scripts"=>"<script src='".HOME."/scripts/PriMiRScript.js'></script><script>\$(function(){\$('[data-toggle=\"popover\"]').popover()})</script>");
			

			// Must pass in variables (as an array) to use in template
			$variables = array(
				'bodyArray' => $priPage,
				'footerArray' => $footerArray
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("pri-miRNA", $variables);
			
		}
	}
?>