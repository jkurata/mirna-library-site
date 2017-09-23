<?php

	class SearchController extends Controller{
		// Types of searches allowed
		private $types; 
		
		function __construct(){
			Controller::__construct();
			$this->types = array("Primary miRNA Name", "Primary miRNA ID", "sgRNA Sequence", "sgRNA ID");
		}
		function index(){
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'search_class' => 'active',
				'bodyArray' => array("submit_action"=>"", "search_types"=>$this->types)
			);
			// defined in templateFunction.php
    		renderLayoutWithContentFile("search", $variables);
		}
		
		// submits input to model and outputs results
		function results($type, $term){
			require_once(LIBRARY_PATH . "/SearchModel.php");
			$searchModel = new SearchModel($type, $term, $this->types);
			$searchResults = $searchModel->run_search();
			
			if (! $searchResults || $searchResults["rowNum"] == 0){
				// remove dashes for display
				$type_output = str_replace("-", " ", $type);
				// Must pass in variables (as an array) to use in template
				$variables = array(
					'bodyArray' => array("term"=>$term, "type"=>$type_output)
				);
				// defined in templateFunction.php
				renderLayoutWithContentFile("no_results", $variables);
			}else{
				$footerArray = array("other-scripts"=>"<script>$(document).ready(function(){".
"$('#search-output').tablesorter({theme: 'bootstrap', widthFixed: true, headerTemplate: '{content} {icon}', ".
"widgets:['uitheme']});});</script>");
				// Must pass in variables (as an array) to use in template
				$variables = array(
					'bodyArray' => $searchResults,
					'footerArray' => $footerArray
				);
				// defined in templateFunction.php
				renderLayoutWithContentFile("search_results", $variables);
			}
			
		}
	}
	
?>