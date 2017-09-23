<?php

	class FormatPri{
		private $annotations;
		private $longSeq;
		private $priStart;
		private $priEnd;
		private $priStrand;
		
		function __construct($longSeq, $priStart, $priEnd, $priStrand){
			$this->longSeq = $longSeq;
			$this->priStart = $priStart;
			$this->priEnd = $priEnd;
			$this->priStrand = $priStrand;
		}
		
		function makeAnnotated($matMiRArray, $sgRNAArray, $secStruct){
			$this->findAnnotations($matMiRArray, $sgRNAArray);
			$this->displayInstructions($secStruct);
			$annotatedDisplay = $this->insertTags();
			return $annotatedDisplay;
		}
		
		function insertTags(){
			// The array to which the sequence which will be on each row will be added
			$seqArray = array(1=>"", 2=>"", 3=>"", 4=>"", 5=>"");
			$keys = array_keys($this->annotations);
			
			foreach ($this->rows as $rowNum=>$directions){
				$previousClasses = array();
				foreach ($directions as $toAdd){
					switch (True){
						// Deals with all the special characters
						case (is_string($toAdd)):
							if (!empty($previousClasses)){
								$seqArray[$rowNum] .= "</span>".$toAdd;
								$previousClasses=array();
							}else{
								$seqArray[$rowNum] .= $toAdd;
							}
							break;
						// Deals with inserting a base
						case (is_int($toAdd)):
							$base = substr($this->longSeq, $toAdd, 1);
							$classes = $this->annotations[$keys[$toAdd]];
							if (empty($previousClasses) && (!empty($classes))){
								$seqArray[$rowNum] .= "<span class='".implode(" ", $classes)."'>".$base;
								$previousClasses = $classes;
							}elseif((!empty($previousClasses)) && empty($classes)){
								$seqArray[$rowNum] .= "</span>".$base;
								$previousClasses = $classes;
							}elseif ((!empty(array_diff($classes, $previousClasses))) || 
							(!empty(array_diff($previousClasses, $classes)))){
								$seqArray[$rowNum] .= "</span><span class='".implode(" ", $classes)."'>".$base;
								$previousClasses = $classes;
							}else{
								$seqArray[$rowNum] .= $base;
								$previousClasses = $classes;
							}
							break;
					}
				}
				if (!empty($previousClasses)){
					$seqArray[$rowNum] .= "</span>";
					$previousClasses=array();
				}
			}
			return $seqArray;
		}

		
		/*
		* Creates array with annotations which apply to each base in the PriMiR + extended sequence
		*
		* Takes: $matMiR - array of miRNAs returned from database
		*		$sgRNA - array of sgRNAs returned from database
		*
		* Sets $this->annotations: annotations for each base in the sequence, with genomic location keys and 
		*	values which are arrays of classes to which that genomic location should be annotated
		*
		*	All of the starts and ends are genomic relative to hg38 and start is ALWAYS less than end
		*
		*/
		private function findAnnotations($matMiR, $sgRNA){
			
			// Creates an array with keys equal to the genomic position of the nucleotide
			// The values are arrays which will hold the classes associated with that nucleotide
			$annotations = array();
			foreach (range($this->priStart-20, $this->priEnd+20) as $i){
				$annotations[$i]=array();
			}
			
			// Add classes to nucleotides in mature miRNA or extended sequence
			foreach ($matMiR as $mir){
				foreach (range($mir["MatStart"], $mir["MatEnd"]) as $miRi){
					$annotations[$miRi][]="miR";
					$annotations[$miRi][]=$mir["MatID"];
				}
			}
			foreach(range($this->priStart-20, $this->priStart-1) as $exti){
				$annotations[$exti][] = "extSeq";
			}
			foreach(range($this->priEnd+1, $this->priEnd+20) as $exti){
				$annotations[$exti][] = "extSeq";
			}
			
			// Add classes to nucleotides in the sgRNA target sequence
			foreach ($sgRNA as $sg){
				foreach (range($sg["SgStart"], $sg["SgEnd"]) as $sgi){
					$annotations[$sgi][]="sgRNA";
					$annotations[$sgi][]=$sg["ID"];
					if (($sg["SgStrand"]=="+" && $this->priStrand=="+")||($sg["SgStrand"]=="-" && $this->priStrand=="-")){
						$annotations[$sgi][]="fwd";
					}
					else{
						$annotations[$sgi][]="rev";
					}
				}
				// Add cleavage site tags
				$annotations[$sg["CleaveStart"]][]="cleave".$sg["ID"];
				$annotations[$sg["CleaveEnd"]][]="cleave".$sg["ID"];
			}
			
			// Change the orientation of the array based on the strand of DNA, so the 0 index is the classes
			// for the 0 index character in the priSequence
			if ($this->priStrand === "+"){
				$this->annotations = $annotations;
			}else{
				$this->annotations = array_reverse($annotations, True);
			}
			
		}
		
		/*
		* Creates an array which says what should be displayed at every index for each row in the PriMiR diagram
		*
		* Takes: $secStruct - the RNAfold secondary structure of the PriMiR (not including extended sequence)
		*
		* Sets $this->rows: array with instructions for what should be displayed, with row number keys and 
		*	values which are arrays of special characters or the index on the longSeq which should be displayed
		*
		*/
		private function displayInstructions($secStruct){
			// Length not including the extended sequences
			$len = strlen($secStruct);
			// Array to keep track of which indexes are included in each row
			$rows = array(1=>array(), 2=>array(), 3=>array(), 4=>array(), 5=>array());
			// Find the number of stemloops in the structure
			// Normally 1, but some have small 2nd stem at end
			$stemloopMatch = preg_match_all("/([\(|\.]*\()\.*?(\)[\)|\.]*)/", $secStruct, $out, PREG_OFFSET_CAPTURE);
			// Look for longest stem,
			$maxStem = 0;
			for ($i=0; $i<$stemloopMatch; $i++){
				$startStem = $out[1][$i][1] + 1;
				$endStem = $out[2][$i][1];
				$stemLen = $endStem-$startStem;
				if ($stemLen > $maxStem){
					$maxStem = $stemLen;
					$maxIndex = $i;
				}
			}
			// Straighten short stem
			for ($i=0; $i<$stemloopMatch; $i++){
				if ($i != $maxIndex){
					$startStem = $out[1][$i][1];
					$endStem = $out[2][$i][1]+strlen($out[2][$i][0]);
					$stemLen = $endStem-$startStem;
					$secStruct=substr_replace($secStruct, str_repeat(".", $stemLen), $startStem, $stemLen);
				}
			}
			
			$loopMatch = preg_match("/(\()\.*?(\))/", $secStruct, $loopOut, PREG_OFFSET_CAPTURE);
			// Location of the last base before the loop
			$startLoop = $loopOut[1][1] + 1;
			// Location of the first base after the loop
			$endLoop = $loopOut[2][1];
				
			$loopLen = $endLoop - $startLoop;
			/* ADD LOOP */
			switch ($loopLen){
				case (1):
					$row1 = array("&nbsp");
					$row2 = array("&nbsp");
					$row3 = array($startLoop+20);
					$row4 = array("&nbsp");
					$row5 = array("&nbsp");
					break;
				
				case (2):
					$row1 = array("&nbsp");
					$row2 = array($startLoop+20);
					$row3 = array("&nbsp");
					$row4 = array($startLoop+21);
					$row5 = array("&nbsp");
					break;
					
				case(3):
					$row1 = array("&nbsp","&nbsp");
					$row2 = array($startLoop+20, "&nbsp");
					$row3 = array("&nbsp", $startLoop+21);
					$row4 = array($startLoop+22, "&nbsp");
					$row5 = array("&nbsp","&nbsp");
					break;
				case($loopLen%2 == 0):
					$topLen = ($loopLen-2)/2;
					$row1 = array_merge(range($startLoop+20, $startLoop+$topLen+20), array("&nbsp"));
					$row2 = array_merge(array_fill(0, $topLen, "&nbsp"), array($startLoop+$topLen+21));
					$row3 = array_merge(array_fill(0, $topLen, "&nbsp"), array("&nbsp"));
					$row4 = array_merge(array_fill(0, $topLen, "&nbsp"), array($startLoop+$topLen+22));
					$row5 = array_merge(array_reverse(range($startLoop+$topLen+23, $endLoop-1+20)), array("&nbsp"));
					break;
				default:
					$topLen = ($loopLen-3)/2;
					$row1 = array_merge(range($startLoop+20, $startLoop+$topLen+20), array("&nbsp"));
					$row2 = array_merge(array_fill(0, $topLen, "&nbsp"), array($startLoop+$topLen+21));
					$row3 = array_merge(array_fill(0, $topLen, "&nbsp"), array($startLoop+$topLen+22));
					$row4 = array_merge(array_fill(0, $topLen, "&nbsp"), array($startLoop+$topLen+23));
					$row5 = array_merge(array_reverse(range($startLoop+$topLen+24, $endLoop-1+20)), array("&nbsp"));
			}
			// Add loop to $rows
			foreach ($rows as $rowNum=>$rowVal){
				$rows[$rowNum] = array_merge(${"row".$rowNum},$rowVal);
			}
			
			/* ADD STEM */
			// Where we are on either side of the stem
			// This index does not include the extended seq
			$p5 = $startLoop - 1;
			$p3 = $endLoop;
			while (1 == 1){
				// End of loop conditions
				if ($p5 < 0 && $p3 >= $len){
					break;
				}elseif ($p5 < 0){
					// The rest of p3
					$space = array_fill(0, $len-$p3, "&nbsp");
					
					$rows[1] = array_merge(array_fill(0, $len-$p3,"-"), $rows[1]);
					$rows[2] = array_merge($space, $rows[2]);
					$rows[3] = array_merge($space, $rows[3]);
					$rows[4] = array_merge($space, $rows[4]);
					$rows[5] = array_merge(array_reverse(range($p3+20, $len+19)), $rows[5]);
					break;
				}elseif ($p3 >= $len){
					// The rest of p5, add 1 since we index from 0
					$space = array_fill(0, $p5+1, "&nbsp");
					
					$rows[1] = array_merge(range(21, $p5+21), $rows[1]);
					$rows[2] = array_merge($space, $rows[2]);
					$rows[3] = array_merge($space, $rows[3]);
					$rows[4] = array_merge($space, $rows[4]);
					$rows[5] = array_merge(array_fill(0, $p5+1,"-"), $rows[5]);
					break;
				}
				
				// If we are not at the end of the miRNA
				// Loads the next structure and base for both sides of the stem
				$struc5 = substr($secStruct, $p5, 1);
				$struc3 = substr($secStruct, $p3, 1);
				if ($struc5 == ')'){
					echo "Error: still have more than 1 stem";
					break;
				}elseif ($struc3 == '('){
					echo "Error: still have more than 1 stem";
					break;
				}elseif ($struc5 == '(' && $struc3 == ')'){
					$addToRows = array("&nbsp",$p5+20, "|", $p3+20, "&nbsp"); 
					
					$p5 = $p5 - 1;
					$p3 = $p3 + 1;
				}elseif ($struc5 == '.' && $struc3 == '.'){
					$addToRows = array($p5+20,"&nbsp", "&nbsp", "&nbsp", $p3+20); 

					$p5 = $p5 - 1;
					$p3 = $p3 + 1;
				}elseif ($struc5 == '(' && $struc3 == '.'){
					$addToRows = array("-","&nbsp", "&nbsp", "&nbsp", $p3+20); 
					
					$p3 = $p3 + 1;
				}
				elseif ($struc5 == '.' && $struc3 == ')'){
					$addToRows = array($p5+20,"&nbsp", "&nbsp", "&nbsp", "-"); 
					
					$p5 = $p5 - 1;
				}
				
				// This should always be set, but just in case...
				if (isset($addToRows)){
					// Add to $rows
					foreach ($rows as $rowNum=>$rowVal){
						$rows[$rowNum] = array_merge(array($addToRows[$rowNum-1]), $rowVal);
					}
					unset($addToRows);
				}
			}
			
			
			/* ADD EXT SEQ */
			$rows[1] = array_merge(range(0,19), $rows[1]);
			$rows[2] = array_merge(array_fill(0,20,"&nbsp"), $rows[2]);
			$rows[3] = array_merge(array_fill(0,20,"&nbsp"), $rows[3]);
			$rows[4] = array_merge(array_fill(0,20,"&nbsp"), $rows[4]);
			$rows[5] = array_merge(array_reverse(range($len+20,$len+39)), $rows[5]);
			
			$this->rows = $rows;
		}
	}
?>