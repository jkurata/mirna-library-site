<?php
	class PriMiRModel extends Model{
		protected $priID;
		
		
		function __construct($priID){

			if($this->validatePri($priID)){
				$this->priID = $priID;
			}else{
				return False;
			}
		}
		
		private function validatePri($var){
			if (!isset($var)){
				return False;
			}
			// Check priID is in the correct format
			if (preg_match("/MI[0-9]{7}/", $var) == 0){
				return False;
			}
			return True;
		}
		
		function processPri(){
			$instance = Database::getInstance();
			$rows = $instance->selectById("PrimaryMicroRNA", "PriID", $this->priID);
			if (!$rows || count($rows) != 1){
				return False;
			}else{
				$row = $rows[0];
			}
			
			
			$name = $row['PriMiRName'];
			
			// Genomic location string
			$geneLoc = "Chr".$row['Chr'].":".$row['GenomeStart']."-".$row['GenomeEnd'];
			$strand = $row['ChrStrand'];
			
			// High Confidence
			if (is_null($row['HighConfidence'])){
				$highCon = "No";
			}
			else{
				$highCon="Yes";
			}
			
			// miRNA Family
			if (is_null($row['MiRFamily'])){
				$fam = "N/A";
			}
			else{
				$fam=$row['MiRFamily'];
			}
			
			//mature miRAs 
			$matArray = $this->getMat();
			
			//sgRNAs
			$sgFound = $this->getSgRNAs();
			$sgArray = $sgFound["sgA"];
			
			// Make and annotates miRNA diagram
			require_once LIBRARY_PATH . "/annotate-diagramPriMiR.php";
			$annoObj = new formatPri($row['LongSeq'], $row['GenomeStart'], $row['GenomeEnd'], $row['ChrStrand']);
			$seqDig = $annoObj->makeAnnotated($matArray, $sgArray, $row['RNAfold']);
			// miRNA targeted by at least 4 sgRNAs in pool
			if ($sgFound["sgNum"] >= 4){
				$mirInclude = "Yes";
			}else{
				$mirInclude = "No";
			}

			return array("priName"=>$name, "priID"=>$this->priID, "seq"=>$seqDig, "genLoc"=>$geneLoc, "strand"=>$strand, "conf"=>$highCon,
			"fam"=>$fam, "matArray"=>$matArray, "miR-included"=> $mirInclude, "sgArray"=>$sgArray, );
		}
		
		private function getMat(){
			$instance = Database::getInstance();
			$rows = $instance->selectById("MatureMicroRNA", "PriID", $this->priID);
			return $rows;
		}
		
		/* 
		Finds the sgRNA associated with the primary miRNA
		Returns array of sgRNAs and number of sgRNAs the priMiR has in the pool
		*/
		private function getSgRNAs(){
			// Have to use aliases to deal with ambigous column names
			// MySQL has no full join
			$sgQuery = "SELECT *, s.SgID AS ID, s.SgRNA AS SgRNA FROM SgRNATargetInformation AS t JOIN SingleGuideRNA AS s ".
			"ON t.SgID=s.SgID LEFT JOIN InPool AS p ON t.SgID = p.SgID WHERE t.PriID ='".$this->priID."' UNION ".
			"SELECT *, s.SgID AS ID, s.SgRNA AS SgRNA FROM SgRNATargetInformation AS t JOIN SingleGuideRNA AS s ".
			"ON t.SgID=s.SgID LEFT JOIN InPool AS p ON t.SgID = p.SgID WHERE t.PriID ='".$this->priID."';";
			$instance = Database::getInstance();
			$sgResult = $instance->queryDB($sgQuery);
			$numSg = 0;
			foreach ($sgResult as $key=>$sgRow){
				if (is_null($sgRow["OligoSeq"])){
					$sgResult[$key]["included"] = "remove";
				}
				else{
					$sgResult[$key]["included"] = "ok";
					++$numSg;
				}
			}
			return array("sgA"=>$sgResult,"sgNum"=> $numSg);
		}
	}
?>