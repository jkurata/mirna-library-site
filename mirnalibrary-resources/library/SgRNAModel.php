<?php
	
	class SgRNAModel extends Model{
		private $sgID;
		
		function __construct($sgID){
			if($this->validateSg($sgID)){
				$this->sgID = $sgID;
			}else{
				return False;
			}
		}
		
		private function validateSg($var){
			if (!isset($var)){
				return False;
			}
			// Check sgID is in the correct format
			if (intval($var)==0 || !(intval($var) >= 1 && intval($var) <= 26600)){
				return False;
			}
			return True;
		}
		
		function processSg(){
			$instance = Database::getInstance();
			$sgFind = $instance-> selectById("SingleGuideRNA", "SgID", $this->sgID);
			if (! $sgFind || count($sgFind)!= 1){
				return False;
			}else{
				$sgInfo = $sgFind[0];
			}
			
			$include = $this->isInPool();
			if ($include === "Yes"){
				$sgInfo["includeGraphic"] = "ok";
			}else{
				$sgInfo["includeGraphic"] = "remove";
			}
			$targInfo = $this->targetInfo();
			
			if (isset($sgInfo["ZhangLibrary"])){
				$sgInfo["InZhang"] = "Yes";
			}else{
				$sgInfo["InZhang"] = "No";
			}
			
			$sgInfo["include"] = $include;
			$sgInfo["targInfo"] = $targInfo;
			return $sgInfo;
		}
		
		private function isInPool(){
			$instance = Database::getInstance();
			$rows = $instance-> selectById("InPool", "SgID", $this->sgID);
			if (!$rows){
				return "No";
			}
			else{
				return "Yes";
			}
		}
		
		private function targetInfo(){
			$query = "SELECT *, s.SgID AS SgID FROM SingleGuideRNA AS s JOIN SgRNATargetInformation AS t"
			." ON s.SgID = t.SgID WHERE s.SgID = '".$this->sgID."';";
			$instance = Database::getInstance();
			$eachTarget = $instance->queryDB($query);
			
			foreach ($eachTarget as $key=>$row){
				$priID = $row["PriID"];
				$priArray = $this->getPri($priID);
				$matArray = $this->getMat($priID);
				
				$eachTarget[$key] = array_merge($eachTarget[$key], $priArray[0]);
				$eachTarget[$key]["matArray"] = $matArray;
			}
			return $eachTarget;
		}
		
		private function getMat($priID){
			$instance = Database::getInstance();
			$matResult = $instance-> selectById("MatureMicroRNA", "PriID", $priID);
			return $matResult;
		}
		
		private function getPri($priID){
			$instance = Database::getInstance();
			$priResult = $instance-> selectById("PrimaryMicroRNA", "PriID", $priID);
			return $priResult;
		}
		
	}
	
	
?>