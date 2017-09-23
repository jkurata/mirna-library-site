<?php

	class SearchModel extends Model{
		public $output = null;
		
		private $results;
		private $search;
		private $expectedTypes;
		private $type;
		private $term;
		
		function __construct($type, $term, $expectedTypes){
			$this->type = $type;
			$this->term = $term;
			$this->expectedTypes = $expectedTypes;
		}
		
		public function run_search(){
			if ($this->validate()){
				$this->search_db();
				$this->process_results();
				return $this->output;
			}else{
				/* if validation fails*/
				return False;
			}
		}
		
		/* Validate and Process Input */
		private function validate(){
			// Make sure there is a type and a term submitted
			if ((! isset($this->type)) || (! isset($this->term))){
				return False;
			}else{
				// Process type and term to prevent SQL injection
				$type = str_replace("-", " ", $this->type);
				$this->type = $this->process_input($type);
				$this->term = $this->process_input($this->term);
			}
			
			// Make sure the type of search is a valid type
			if (! in_array($this->type, $this->expectedTypes)){
				return False;
			}
			return True;
		}
		
		private function process_input($data){
			// prevents SQL injection
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
		
			return $data;
		}
		
		/* Queries the database with the search term */
		private function search_db(){
			// Query finds all of the matches of the type specified
			switch($this->type){
				case "Primary miRNA Name":
					$query = "SELECT * FROM PrimaryMicroRNA WHERE PriMiRName LIKE '%".$this->term."%'";
					break;
				case "Primary miRNA ID":
					$query = "SELECT * FROM PrimaryMicroRNA WHERE PriID LIKE '%".$this->term."%'";
					break;
				case "sgRNA Sequence":
					$query = "SELECT * FROM SingleGuideRNA WHERE SgRNA LIKE '%".$this->term."%'";
					break;
				case "sgRNA ID":
					$query = "SELECT * FROM SingleGuideRNA WHERE SgID LIKE '%".$this->term."%'";
					break;
			}
			// Query the Database object
			$instance = Database::getInstance();
			$this->results = $instance->queryDB($query);
		}
		
		private function process_results(){
			$rows = array();
			
			switch ($this->type){
				case "Primary miRNA Name":
				case "Primary miRNA ID":
					$this->output["header"] = array("miRNA ID", "miRNA Name", "sgRNAs", "In Pool");
					$this->output["link"] = "PriMiR";
					$this->output["otherLink"]  = "SgRNA";
					
					// Finds all the sgRNAs for the given miRNA, load that plus in pool and id into array
					foreach( $this->results as $row){
						$matchResults = $this->find_additional_matches($row["PriID"], "miRNA");
						if ($matchResults["include"]){
							$incluGraphic = "ok";
						}
						else{
							$incluGraphic ="remove";
						}
						$rows[]= array("id"=>$row["PriID"], "name"=>$row["PriMiRName"], "added"=>$matchResults["matches"], "included"=>$incluGraphic);
					}
					break;
				case "sgRNA Sequence":
				case "sgRNA ID":
					$this->output["header"] = array("sgRNA ID", "sgRNA Sequence", "miRNAs Targeting", "In Pool");
					$this->output["link"] = "SgRNA";
					$this->output["otherLink"]  = "PriMiR";
					
					// Finds all the miRNA targeted by a given sgRNA, load that plus in pool and id into array
					foreach( $this->results as $row){
						$matchResults = $this->find_additional_matches($row["SgID"], "sgRNA");
						if ($matchResults["include"]){
							$incluGraphic = "ok";
						}
						else{
							$incluGraphic ="remove";
						}
						$rows[]= array("id"=>$row["SgID"], "name"=>$row["SgRNA"], "added"=>$matchResults["matches"], "included"=>$incluGraphic);
					}
					break;
				
			}
			$this->output["rowNum"] = count($this->results);
			$this->output["term"] = $this->term;
			$this->output["type"] = $this->type;
			$this->output["reArray"] = $rows;
		}
		
		/* 
		finds the corresponding sgRNAs or miRNAs to the given row
		*/
		private function find_additional_matches($id, $type){
			$results = array();
			$matchOut = array();
			switch($type){
				case "miRNA":
					// MySQL has no full join
					$query = "SELECT *, t.SgID AS SgID FROM PrimaryMicroRNA AS p JOIN SgRNATargetInformation AS t ".
					"ON p.PriID = t.PriID LEFT JOIN InPool AS i ON t.SgID = i.SgID ".
					"WHERE p.PriID ='".$id."' UNION ".
					"SELECT *, t.SgID AS SgID FROM PrimaryMicroRNA AS p JOIN SgRNATargetInformation AS t ".
					"ON p.PriID = t.PriID RIGHT JOIN InPool AS i ON t.SgID = i.SgID WHERE p.PriID ='".$id."';";
					// Query the Database object
					$instance = Database::getInstance();
					$matches = $instance->queryDB($query);

					// Count the number of sgRNAs for the primary miRNA which are included in the pool
					$numIncluded = 0;
					foreach ($matches as $match){
						if (!is_null($match["OligoSeq"])){
							++$numIncluded;
						}
						$matchOut[]=$match["SgID"];
					}
					// If number of sgRNA greater than or equal to 4, the miRNA is included in the pool
					if ($numIncluded >= 4){
						$include = true;
					}
					else{
						$include = false;
					}
					break;
					
				case "sgRNA":
					$query = "SELECT * FROM SingleGuideRNA AS s JOIN SgRNATargetInformation AS t ON t.SgID = s.SgID ".
					"JOIN PrimaryMicroRNA AS p on p.PriID = t.PriID LEFT JOIN InPool AS i ON t.SgID = i.SgID ".
					"WHERE s.SgID ='".$id."' UNION ".
					"SELECT * FROM SingleGuideRNA AS s JOIN SgRNATargetInformation AS t ON t.SgID = s.SgID  ".
					"JOIN PrimaryMicroRNA AS p on p.PriID = t.PriID RIGHT JOIN InPool AS i ON t.SgID = i.SgID ".
					"WHERE s.SgID ='".$id."';"; 
					// Query the Database object
					$instance = Database::getInstance();
					$matches = $instance->queryDB($query);
					foreach ($matches as $match){
						$priID = $match["PriID"];
						if (!in_array($priID, $matchOut)){
							$matchOut[]=$priID;
						}
					}
					// Look at the first sgRNA as it should be the same for each row, only the priMiRNA should be changing 
					if (!is_null($matches[0]["OligoSeq"])){
						$include = true;
					}
					else{
						$include = false;
					}
					break;
			}
			$results["matches"] = $matchOut;
			$results["include"] = $include;
			return $results;
		}
				
	}
	
?>