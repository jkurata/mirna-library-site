<?php 
	abstract class Controller{
		
		// Load default values
		function __construct(){
			// load function which will actually create to html
    		require_once(LIBRARY_PATH . "/templateFunction.php");
		}
	}


?>