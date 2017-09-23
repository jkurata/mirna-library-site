<?php
	function displayPriMiR($seq, $secStruct){
		$len = strlen($seq);
			
		$row1 = ""; // The top out of stem row
		$row2 = ""; // The upper stem row
		$row3 = ""; // The bond/loop row
		$row4 = ""; // The lower stem row
		$row5 = ""; // The bottom out of stem row
		
		// Find the number of stemloops in the structure
		// Normally 1, but some have small 2nd stem at end
		$numMatch = preg_match_all("/(\()\.*?(\))/", $secStruct, $out, PREG_OFFSET_CAPTURE);
		
		// Loop through the stems
		for ($i=0; $i<$numMatch; $i++){
			// Location of the last base before the loop
			$startLoop = $out[1][$i][1] + 1;
			// Location of the first base after the loop
			$endLoop = $out[2][$i][1];
			
			// Where we are on either side of the stem
			$p5 = $startLoop - 1;
			$p3 = $endLoop;
			
			while (1 == 1){
				// Check if we have reached the end of the structure
				if ($p5 < 0 && $p3 >= $len-40){
					break;
				}
				elseif ($p5 < 0){
					// The rest of p3
					$seq3 = substr($seq, $p3+20, $len-$p3-40);
					$space = str_repeat("&nbsp", $len-$p3-40);
					
					$row1 = str_repeat("-", $len-$p3-40).$row1;
					$row2 = $space.$row2;
					$row3 = $space.$row3;
					$row4 = $space.$row4;
					$row5 = $seq3.$row5;
					
					break;
				}
				elseif ($p3 >= $len-40){
					// The rest of p5, add 1 since we index from 0
					$seq5 = substr($seq, 20, $p5+1);
					$space = str_repeat("&nbsp", $p5+1);
					
					$row1 = $seq5.$row1;
					$row2 = $space.$row2;
					$row3 = $space.$row3;
					$row4 = $space.$row4;
					$row5 = str_repeat("-", $p5+1).$row5;
					
					break;
				}
				// If we are not at the end of the miRNA
				// Loads the next structure and base for both sides of the stem
				$struc5 = substr($secStruct, $p5, 1);
				$struc3 = substr($secStruct, $p3, 1);
				
				$seq5 = substr($seq, $p5+20, 1);
				$seq3 = substr($seq, $p3+20, 1);
				
				if ($struc5 == ')'){
					break;
				}
				elseif ($struc3 == '('){
					break;
				}
				elseif ($struc5 == '(' && $struc3 == ')'){
					$row1 = "&nbsp".$row1;
					$row2 = $seq5.$row2;
					$row3 = "|".$row3;
					$row4 = $seq3.$row4;
					$row5 = "&nbsp".$row5;
					
					$p5 = $p5 - 1;
					$p3 = $p3 + 1;
				}
				elseif ($struc5 == '.' && $struc3 == '.'){
					$row1 = $seq5.$row1;
					$row2 = "&nbsp".$row2;
					$row3 = "&nbsp".$row3;
					$row4 = "&nbsp".$row4;
					$row5 = $seq3.$row5;
					
					$p5 = $p5 - 1;
					$p3 = $p3 + 1;
				}
				elseif ($struc5 == '(' && $struc3 == '.'){
					$row1 = '-'.$row1;
					$row2 = "&nbsp".$row2;
					$row3 = "&nbsp".$row3;
					$row4 = "&nbsp".$row4;
					$row5 = $seq3.$row5;
					
					$p3 = $p3 + 1;
				}
				elseif ($struc5 == '.' && $struc3 == ')'){
					$row1 = $seq5.$row1;
					$row2 = "&nbsp".$row2;
					$row3 = "&nbsp".$row3;
					$row4 = "&nbsp".$row4;
					$row5 = '-'.$row5;
					
					$p5 = $p5 - 1;
				}
			}
		}
		//Adds the loop sequence
		$loopLen = $endLoop-$startLoop;
		$loopSeq = substr($seq, $startLoop+20, $loopLen);
		switch ($loopLen){
			case (1):
				$row1 .= "&nbsp";
				$row2 .= "&nbsp";
				$row3 .= substr($loopSeq, 0, 1);
				$row4 .= "&nbsp";
				$row5 .= "&nbsp";
				break;
			
			case (2):
				$row1 .= "&nbsp";
				$row2 .= substr($loopSeq, 0, 1);
				$row3 .= "&nbsp";
				$row4 .= substr($loopSeq, 1, 1);
				$row5 .= "&nbsp";
				break;
				
			case(3):
				$row1 .= "&nbsp"."&nbsp";
				$row2 .= substr($loopSeq, 0, 1)."&nbsp";
				$row3 .= "&nbsp".substr($loopSeq, 1, 1);
				$row4 .= substr($loopSeq, 2, 1)."&nbsp";
				$row5 .= "&nbsp"."&nbsp";
				break;
			case($loopLen%2 == 0):
				$topLen = ($loopLen-2)/2;
				$row1 .= substr($loopSeq, 0, $topLen)."&nbsp";
				$row2 .= str_repeat("&nbsp", $topLen).substr($loopSeq, $topLen, 1);
				$row3 .= str_repeat("&nbsp", $topLen)."&nbsp";
				$row4 .= str_repeat("&nbsp", $topLen).substr($loopSeq, $topLen+1, 1);
				$row5 .= strrev(substr($loopSeq, $topLen+2, $topLen))."&nbsp";
				break;
			default:
				$topLen = ($loopLen-3)/2;
				$row1 .= substr($loopSeq, 0, $topLen)."&nbsp";
				$row2 .= str_repeat("&nbsp", $topLen).substr($loopSeq, $topLen, 1);
				$row3 .= str_repeat("&nbsp", $topLen).substr($loopSeq, $topLen+1, 1);
				$row4 .= str_repeat("&nbsp", $topLen).substr($loopSeq, $topLen+2, 1);
				$row5 .= strrev(substr($loopSeq, $topLen+3, $topLen))."&nbsp";
		}
		
		
		
		// Adds the extended sequence inside a span of class 'extSeq'
		$row1 = "<span class='extSeq' title='20 nts of extra sequence before the stemloop'>".substr( $seq, 0, 20 )."</span>".$row1;
		$row2 = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp".$row2;
		$row3 = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp".$row3;
		$row4 = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp".$row4;
		$row5 = "<span class='extSeq' title='20 nts of extra sequence after the stemloop'>".strrev(substr( $seq, $len-20, 20))."</span>".$row5;
		
		return array($row1, $row2, $row3, $row4, $row5);
	}


?>