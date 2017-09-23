<?php
	/*
	* Inserts spans around annotations for the given priMiR
	*
	* Takes: $seq - priMiR extended sequence
	*		$priStart - Genomic starting loaction of the miRNA
	*		$priEnd - Genomic ending location of the miRNA
	* 		$matMiR - array of miRNAs with id as key and start/end locations as values
	*		$sgRNA - array of sgRNAs with each sgRNA containing an array with start, end and cleavage sites
	*
	* Returns: Annotated miRNA with inserted spans
	*
	*
	*	All of the starts and ends are genomic relative to hg38 and start is ALWAYS less than end
	*
	*/
	
	function findAnnotations($seq, $priStart, $priEnd, $matMiR, $sgRNA){
		$annotations = array();
		
		// Annotate mature miRNAs
		foreach ($matMiR as $mir){
			$annotations[] = array("classes"=>array("miR", $mir["MatID"]), "location"=>array("start"=>$mir["MatStart"], 
			"end"=>$mir["MatEnd"]));
		}
		
		// Annotate extended sequences
		// Should never overlap with mature miRNAs
		$annotations[] = array("classes"=>array("extSeq"), "location"=>array("start"=>$priStart-20, 
			"end"=>$priStart-1));
		$annotations[]= array("classes"=>array("extSeq"), "location"=>array("start"=>$priEnd+1, 
			"end"=>$priEnd+20));
		
		// Annotate sgRNAs 
		foreach ($sgRNA as $sg){
			$start = $sg["SgStart"];
			$end = $sg["SgEnd"];
			
			// Keeps track if the sgRNA tags have been added yet
			$added = False;
			// If the sgStrand is positive, set to fwd, else set to rev (ternary)
			// sgStrand is relative to genome, not miRNA
			$strand = ($sg["SgStrand"]=="+") ? "fwd" : "rev";
			
			// Classes for the sgRNA
			$newClasses = array("sgRNA", $strand, $sg["ID"]);
			
			foreach ($annotations as $key => $tag){
				// Pull out the start and end of the existing annotation
				$tStart = $tag["location"]["start"];
				$tEnd = $tag["location"]["end"];
				
				/* Check if they overlap */
				if ($tStart == $start && $tEnd == $end){
					$overlap = "total";
				}
				elseif (($tStart<=$start) && ($start<=$tEnd) && ($tEnd<=$end)){
					$overlap = "left";
				}
				elseif (($tStart<=$end) && ($end<=$tEnd) && ($start<=$tStart)){
					$overlap = "right";
				}
				else{
					// Move on to the next tag
					continue;
				}
				
				// Create a merge of classes with the current classes and remove duplicates
				$curClasses = $tag["classes"];
				$merge = array_merge($newClasses, $curClasses);
				var_dump(array_unique($merge));
				
				switch ($overlap){
					case "total":
						$annotations[$key]["classes"] = $merge;
						$added = True;
						break;
						
					case "left":
						// Create new combined tag
						$annotations[] = array("classes"=>$merge, "location"=>array("start"=>$start, 
						"end"=>$tEnd));
						// Create new sg tag
						$annotations[] = array("classes"=> $newClasses, "location"=> array("start"=>$tEnd+1, 
						"end"=>$end));
						// Shorten previous tag
						$annotations[$key]["location"]["end"] = $start-1;
						$added = True;
						break;
						
					case "right":
						// Create new combined tag
						$annotations[] = array("classes"=>$merge, "location"=>array("start"=>$tStart, 
						"end"=>$end));
						// Create new sg tag
						$annotations[] = array("classes"=> $newClasses, "location"=>array("start"=>$start, 
						"end"=>$tStart-1));
						// Shorten previous tag
						$annotations[$key]["location"]["start"] = $end+1;
						$added = True;
						break;
				}
				
			}
			// If this sgRNA does not overlap with any tag
			if (!$added){
				$annotations[] = array("classes"=> $newClasses, "location"=> array("start"=>$start, "end"=>$end));
			}
		}
		
		return $annotations;
	}
?>