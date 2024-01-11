<?php

class LigandRotbParser {

 	private $debug;
 	private $destinyFullPath;
 	
	public function __construct(){
		
		//syslog init
		openlog('dockingapp-ligand-rotb-editor', LOG_NDELAY, LOG_USER);		
		
		//conf ini
		$ini_array = parse_ini_file("../../conf/conf.ini", true);
		if($ini_array['debug']==1){
			$this->debug = true;
		}else{
			$this->debug = false;			
		}
		
		$this->log("##################### Ligand Rotb Parser debug init ################################", "DEBUG");
	}
	
	function __destruct() {
		closelog(); //close sylog
	}
	
	/**
	 * 
	 */
	public function generateJsonFromTopFile($topFileNameFullPath){

		$this->log("generateJsonFromTopFile", "DEBUG");
		
		if(file_exists($topFileNameFullPath)){
			
			$this->log("topFileNameFullPath: ".$topFileNameFullPath, "DEBUG");			
			$topFileArray = $this->parseTopFileToArray($topFileNameFullPath);
			
			if(isset($topFileArray)){				
				$jsonString = json_encode($topFileArray, JSON_PRETTY_PRINT);				
				return $jsonString;
			}else{
				return false;
			}
			
		}else{
			$this->log("FAILED - File doest exist:".$topFileNameFullPath, "ERROR");
			return false;
		}
		
	}
	
	public function generateCopyJsonEdited($originalJson, $arrayElementsToBeRemoved, $destinyFullPath){
		
		$originalArray = json_decode($originalJson, true);
		
		//print_r($originalArray);
		foreach ($arrayElementsToBeRemoved as $index){
			unset($originalArray[$index]); // remove item at index 0
		}
		
		//reindex from 1
		//$originalArray['elements'] = array_combine(range(1, count($originalArray['elements'])), array_values($originalArray['elements']));
		
		//$originalArray['quantity'] = $elementsQuantity;
		//print_r($originalArray);
		
		$jsonString = json_encode($originalArray, JSON_PRETTY_PRINT);
		return $jsonString;
	}
	
	public function updateTopFile($editedJson, $originTopFileNameFullPath, $destinyTopFileNameFullPath){
		$this->log("updateTopFile", "DEBUG");
		$isNotSelectdTorsionsFlag = true;
		//echo $editedJson;
		
		// Remove false elements (edited by user to be removed) 
		$editedArray = json_decode($editedJson, true);
		foreach ($editedArray as $key => $value) {
			if(!$value[2]){ // true|false
				unset($editedArray[$key]);
			}
		}
		//print_r($editedArray);
		
		$i = 1;
		if(file_exists($originTopFileNameFullPath)){
				
			$this->log("topFileNameFullPath: ".$originTopFileNameFullPath, "DEBUG");
			$topFile = fopen($originTopFileNameFullPath, "r");
			$newTop = "";
			
			//read line by line from pdb file
			while (!feof($topFile)) {
			
				//read the line
				$line = fgets($topFile); // (do not use "$topFile" more than once!)
				//echo "$line\n";
				//transform line to array (word by word)
				$lineArray = preg_split('/\s+/', $line);
					
				if($lineArray[0] == '$SELECTED_TORSIONS'){
					$isNotSelectdTorsionsFlag = false;
					//$newTop .= $lineArray[0]."\t".$editedArray['quantity']."\n";
					$newTop .= $lineArray[0]."\t".count($editedArray)."\n";
					foreach ($editedArray as $key=>$value){
						$newTop .= $i."\t".$value[0]."\t".$value[1]."\n";
						$i++;
					}				
					$newTop .= "\n";
				} else if($isNotSelectdTorsionsFlag){
					$newTop .= $line;
				} else if($lineArray[0] == '$NONBONDED_INTERACTIONS'){
					$isNotSelectdTorsionsFlag = true;
					$newTop .= $line;
				} 
					
			}
			
			fclose($topFile);
			
			//print_r($newTop);
			// Write final result array to JSON file
			$fp = fopen($destinyTopFileNameFullPath, 'w');
			fwrite($fp, $newTop);
			fclose($fp);
			return true;
			
			
		}else{
			$this->log("FAILED - File doest exist:".$originTopFileNameFullPath, "ERROR");
			return false;
		}
	}
	
	private function parseTopFileToArray($topFileName){
	
		$this->log("parseTopFileToArray","DEBUG");
	
		$topFile = fopen($topFileName, "r");
	
		$resultArray = new ArrayObject();
		$selectdTorsionsFlag = false;
		
		$totalAtomsArray = array();
		$totalAtomsFlag = false;
		$i = 0;
		
		//read line by line from pdb file
		while (!feof($topFile)) {
	
			//read the line
			$line = fgets($topFile); // (do not use "$file" more than once!)
	
			//transform line to array (word by word)
			$lineArray = preg_split("/[\s,]+/",  trim($line));
			//print_r($lineArray);
			//when selectdTorsionsFlag is true, and $lineArray[0] != '', means the the all torsion was readed
			
			
			///////////////// TOTAL_ATOMS
			if($lineArray[0] == '$TOTAL_ATOMS'){
				//echo "TOTAL_ATOMS\n";
				$totalAtomsFlag = true;
			}
			if($lineArray[0] == '$CON'){
				$totalAtomsFlag = false;
			}
			if($totalAtomsFlag){
				$atom = $lineArray[0];
				if(!empty($atom)){
					if($atom!='$TOTAL_ATOMS'){
						//echo "total atom flag: $atom\n";
						array_push($totalAtomsArray,$atom);
					}
				}
				
			}
			
			/////////////////// SELECTED_TORSIONS
			if($lineArray[0] == '$SELECTED_TORSIONS'){
				$selectdTorsionsFlag = true;
				
			} else if($lineArray[0] == '$NONBONDED_INTERACTIONS'){
				$selectdTorsionsFlag = false;
				
			} else if($selectdTorsionsFlag && count($lineArray)==3){
				//print_r($lineArray);		
				
				$index1 = intval($lineArray[1]);
				$index2 = intval($lineArray[2]);
				
				$resultArray[$i] = new ArrayObject([$lineArray[1],$lineArray[2],true,$totalAtomsArray[$index1-1],$totalAtomsArray[$index2-1]]);
				$i++;				
			}
			
			
			
		}	
		//print_r($totalAtomsArray);
		//print_r($resultArray);
		fclose($topFile);
		return $resultArray;
	}
	
	/**
	 * TODO Create log class (with contants "DEBUG","ERROR",etc.)
	 * @param String $msg
	 * @param String $type (ERROR|DEBUG)
	 */
	public function log($msg,$type){
	
		if($type=="ERROR"){
			syslog(LOG_ERR, $msg);
			
		}else if($type=="DEBUG"){
			if($this->debug){
				//syslog
				syslog(LOG_DEBUG, $type.": ".$msg);
					
				//console tests
				//echo "$msg\n";
			}
		}
	}
}
