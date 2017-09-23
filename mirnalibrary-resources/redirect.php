<?php

	/* 
	This file redirects the search query to a seo friendly url which can then be parsed by router
	*/
	$term = $_GET['search-term'];
	$type = $_GET['search-type'];
	$type =str_replace(" ", "-", $type);
	
	header("Location: ".HOME."/Search/results/".$type."/".$term);
	exit();
	
?>