<?php

// $configs = include('protonation-state-config.php');

class ProteinParser {

	private $debug;
	private $pathFiles;
	private $pdbFileName;
	private $pdbFileNameNoExtension;
	private $atomsArray; 
	private $chainFileNamesArray;

	public function __construct($pathFiles,$pdbFileRandomId,$chainFileNamesArray){
		
		//Protonation States Config
		$this->atomsArray = include('protonation-state-config.php');
		
		//syslog
		openlog('dockingapp', LOG_NDELAY, LOG_USER);
		$this->debug = false; //TODO get from properties file
		
		$this->log("##################### Protein Parser debug init ################################", "DEBUG");
		
		$this->pathFiles = $pathFiles;
		$this->log("pathFiles: ".$pathFiles, "DEBUG");
		
		$this->pdbFileName = $pdbFileRandomId;
		$this->log("pdbFileRandomId: ".$pdbFileRandomId, "DEBUG");
		
		$this->chainFileNamesArray = $chainFileNamesArray;
		foreach ($chainFileNamesArray as $fileName){
			$this->log("chainFileName: ".$fileName, "DEBUG");
		}		
		
		if($this->pdbFileName==null){
			$this->log("ERROR: no pdb files was found: ".$pathFiles, "ERROR");
			exit();
		}
		
		$this->pdbFileNameNoExtension = substr($this->pdbFileName, 0,strpos($this->pdbFileName,'.'));
		$this->log("pdbFileNameNoExtension: ".$this->pdbFileNameNoExtension,"DEBUG");
		
	}
	
	function __destruct() {
		closelog(); //close sylog
	}
	
	/**
	 * @param String: .pdb file path
	 * @param String: file name:
	 * Return String: Return pdb file name (ignoring _prep.pdb),
	 * if has more than one pdb file, return the one with last created date.
	 */
	function getPdbFile($pathFiles){
	
		$files = scandir($pathFiles);
	
		$pdbFiles = array();
		foreach($files as $name){
			if( (substr($name,-9)!="_prep.pdb") && (substr($name,-4)==".pdb")){
				$time = filemtime($pathFiles . '/' . $name);
				$pdbFiles[$name] = $time;
			}
		}
		//var_dump($pdbFiles);
		if(count($pdbFiles)>=1){
			arsort($pdbFiles);
			return array_keys($pdbFiles)[0];
		}else{
			return null;
		}
	}

	/**
	 * TODO Create log class (with contants "DEBUG","ERROR",etc.)
	 * @param String $msg
	 * @param String $type (ERROR|DEBUG)
	 */
	public function log($msg,$type){
		
		if($type=="ERROR"){
			//syslog
			
			syslog(LOG_ERR, $msg);
				
			//console tests
// 			echo "$msg - Location: ";
// 			array_walk(debug_backtrace(),create_function('$a,$b', 'print "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ";'));
// 			echo "\n";
		}else if($type=="DEBUG"){// for development, only
				if($this->debug){
					//syslog
					syslog(LOG_DEBUG, $type.": ".$msg);
					
					//console tests
					//echo "$msg\n";
				}
		}
	}

	/**
	 * Parse pdb file to array
	 * @param File: pdb file
	 * @return Array
	 */
	private function parsePdbFile($pdbFile){
		$this->log("Parse Pdb File","DEBUG");

		$resultArray = array();

		//read line by line from pdb file
		while (!feof($pdbFile)) {

			//read the line
			$line = fgets($pdbFile); // (do not use "$file" more than once!)

			// line start with "ATOM" string
			if(substr( $line, 0, 4 ) === "ATOM"){ // string substr ( string $string , int $start [, int $length ] )

				// transform the line in array
				$lineArray = preg_split('/\s+/', $line);
				/* Ex.:
				 Array
				 (
				 [0] => ATOM
				 [1] => 2935
				 [2] => OXT
				 [3] => ASP
				 [4] => I
				 [5] => 24
				 [6] => 28.447
				 [7] => 10.333
				 [8] => -6.142
				 [9] => 1.00100.00
				 [10] => O
				 [11] =>
				 )
				 */

				// get pdb 4th column for first level result array (ex.: A, E, I, etc.)
				$chain = $lineArray[4];
				$atom  = $lineArray[3];
				$index = $lineArray[5];

				// using array index to guarantee unique values
				if(!isset($resultArray[$chain])){
					$resultArray[$chain] = [];
				} else{
					if(!isset($resultArray[$chain][$atom])){
						$resultArray[$chain][$atom] = [];
					}else{ 
						if(!isset($resultArray[$chain][$atom][$index])){
							$resultArray[$chain][$atom][$index] = [];
						}
					}
				}

			}
		}

		// 		echo "### FIRST and SECOND LEVEL ARRAY (parsed from .pdb file)\n";
		// 		print_r($resultArray);
		fclose($pdbFile);

		return $resultArray;
	}
	
	private function parsePdbToIndex($pdbFileName){
		
		$pdbFileFullPath = "$this->pathFiles$pdbFileName";
		$this->log("Parse Pdb File ($pdbFileFullPath)","DEBUG");
		
		$pdbFile = fopen($pdbFileFullPath, "r");
		
		$tempArray = array();
		$count = 0;
		
		//read line by line from pdb file
		while (!feof($pdbFile)) {
		
			//read the line
			$line = fgets($pdbFile); // (do not use "$file" more than once!)
		
			if(substr( $line, 0, 4 ) === "ATOM"){ // string substr ( string $string , int $start [, int $length ] )
		
				// transform the line in array
				$lineArray = preg_split('/\s+/', $line);
				$chain = $lineArray[4];
				$atom  = $lineArray[3];
				$index = $lineArray[5];				
				
				$tempArray[$chain][$index] = $atom;
				$count++;
			}
		}
		
		//remove first and last for each chain
		foreach ($tempArray as $chainTempKey=>$chainTempValue){
			unset($tempArray[$chainTempKey][key($tempArray[$chainTempKey])]); // remove first array element
			array_pop($tempArray[$chainTempKey]); // remove last array element
		}		
		
		$resultArray = array();
 		foreach ($tempArray as $chainKey => $chainValue){
 			foreach ($chainValue as $indexKey=>$indexValue){
 				$resultArray[$chainKey][$indexValue][$indexKey] = null;
 			}
 		}
		//print_r($resultArray);
		
		fclose($pdbFile);		
		return $resultArray;
	}

	/**
	 */
	private function parseChainFiles($chainFileNamesArray){

		$fileChainsOneArray = array();

		foreach ($chainFileNamesArray as $chainFileName){
			
			$chainArraySingle = array();
			$this->log("Parse Chain File: $chainFileName","DEBUG");
			
			$chainFileNameFull = $this->pathFiles.$chainFileName;
			$chainFile = fopen($chainFileNameFull, "r");			

			$lineNumber = 0;
			$chainIndex = 1;
			
			while (!feof($chainFile)) {
				
				$chainLine = fgets($chainFile); // note: do not use "$chainFile" more than once in each looping (while)
				if($lineNumber>=5){ // jump five lines
					
					$chainLineArray = preg_split('/\s+/', $chainLine);
					
					$size = count($chainLineArray);
					//echo "$size $chainLine\n";
					
					foreach ($chainLineArray as $key=>$atom){
						if(!empty($atom)){
							$chainArraySingle[$chainIndex] = $atom;
							//$fileChainsOneArray[$chainName][substr($atom, 0, 3)][$chainIndex]["state"] = ["value"=>$atom];
							$chainIndex++;
						}
					}
					
				}
				$lineNumber++;
				
			}
			
			unset($chainArraySingle[1]); // remove first array element
			unset($chainArraySingle[$chainIndex-1]); // remove last array element 
			
			$chainName = substr($chainFileName, -1);
			foreach ($chainArraySingle as $key=>$value){
				$indexTemp = $key;
				$atomTemp = substr($value, 0, 3);
				$fileChainsOneArray[$chainName][$atomTemp][$indexTemp]["state"] = ["value"=>$value];
			}
			
			fclose($chainFile);			
		}		
		return $fileChainsOneArray;
	}
	
	private function parseChainFilesToSingleArray($pdbArray){
	
		$fileChainsOneArray = array();
	
		// for each result first level (A, E, I,) will be used to get each chain file
		// Obs.: the chain file extension has the same name of first level result (.A, .E, .I, etc)
		foreach ($pdbArray as $chainKey => $value){
	
			//$fileChainsOneArray[$chainKey][] = ""; // index 0 will be empty
			$fileChainsOneArray[$chainKey][] = "";
	
			// load chain files (.A or .E or .I or etc).
			$chainFilePath = $this->pathFiles."/".$this->pdbFileNameNoExtension.".".$chainKey;
	
			if(!file_exists($chainFilePath)){
				$this->log("FAILED - The file doest exist:".$chainFilePath,"ERROR");
				return null;
			}
	
			$chainFile = fopen($chainFilePath, "r");
	
			// parse the file
			$lineNumber = 0;
			while (!feof($chainFile)) {
	
				$chainLine = fgets($chainFile); // note: do not use "$file" more than once in each looping (while)
	
	
				if($lineNumber<5){
					$fileChainsOneArray[$chainKey][0] = $fileChainsOneArray[$chainKey][0].$chainLine; // description
	
				} else if($lineNumber>=5){ // 5th line
	
					$chainLineArray = preg_split('/\s+/', $chainLine);
					foreach ($chainLineArray as $key=>$value){
						if(!empty($value)){
							$fileChainsOneArray[$chainKey][] = $value;
							//echo $elements[substr($value,0,2)]
						}
					}
				}
				$lineNumber++;
			}
			fclose($chainFile);
		}
	
		// 		echo "### CHAIN ARRAY(Parsed from .A/.E/.I file(s))\n";
		// 		print_r($fileChainsOneArray);
	
		return $fileChainsOneArray;
	}
	
	private function mergeChainsAndPdb($chainsArray, $pdbArray){
		
		reset($pdbArray); //reinicia o ponteiro, faz apontar para o primeiro elemento
		
		foreach ($chainsArray as $chainKey=>$chainValue){
			//echo "$chainKey \n";
			
			foreach ($chainValue as $atomKey=>$atomValue){
				//echo "\t$atomKey \n";
				
				foreach ($atomValue as $indexKey=>$indexValue){
					$pdbIndex = key($pdbArray[$chainKey][$atomKey]);
					//echo "\t\t$indexKey ($pdbIndex)\n";
					next($pdbArray[$chainKey][$atomKey]);
					$chainsArray[$chainKey][$atomKey][$indexKey]["state"]["index"] = $pdbIndex;

					//check if has options and fill it
					$changeOptions = array();
					foreach ($this->atomsArray as $atomOpt){
						foreach ($atomOpt as $state){							
							if (substr($atomKey, 0, 3) == substr($state, 0, 3)){
								$changeOptions[] = $state;
							}
						}
					}
					$chainsArray[$chainKey][$atomKey][$indexKey]["options"] = $changeOptions;
				}

			}
		}
		return $chainsArray;
	}
	
	/**
	 * Merge pdb array and chains array to final array
	 * @param Array $processedPdbArray
	 * @param Array $fileChainsOneArray
	 * @return Array
	 */
	private function mergePdbArrayAndChainsArray($processedPdbArray,$fileChainsOneArray){

		$this->log("mergePdbArrayAndChainsArray","DEBUG");
// 		echo "$fileChainsOneArray[1]";
// 		print_r($fileChainsOneArray);
// 		print_r($processedPdbArray);
		
		foreach ($processedPdbArray as $chainKey => $chainValue){

			
			
			foreach ($chainValue as $atomKey => $atomValue){

// 				echo "$chainKey \t $atomKey  ";

				$chainFileOcurrences  = array();
				foreach($fileChainsOneArray[$chainKey] as $key => $value){					
					if (substr($value, 0, 3) === $atomKey){
						$chainFileOcurrences[] = ["index"=>$key,"value"=>$value];
					}
				}
// 				var_dump($chainFileOcurrences);
				
				$i = 0;
				foreach ($atomValue as $indexKey => $indexValue){

// 					echo "\n$chainKey \t $atomKey \t $indexKey \t state=[$atomKey,$chainFileOcurrences[$i]] ";
 					
					
					$processedPdbArray[$chainKey][$atomKey][$indexKey]["state"] = ["value"=> $chainFileOcurrences[$i]["value"],"index"=>$chainFileOcurrences[$i]["index"]];
										
					//check if has options and fill it
					$changeOptions = array();
					foreach ($this->atomsArray as $atom){
						foreach ($atom as $state){
							if (substr($atomKey, 0, 3) == substr($state, 0, 3)){
								$changeOptions[] = $state;
							}
						}
					}
					
					// Inpedindo que o estado de propanacao do inicio (aquele com termino "I" | $ini) e do fim (aquele com termino "T" | $term),
					// tenham opcoes
					$term = count($fileChainsOneArray[$chainKey])-1;
					$ini = 1; // Obs.: Indice 0 é o titulo do chain (ex.: "Cadeia E Cadeia ; Numero de aminoacidos...")
					$currentIndex = $processedPdbArray[$chainKey][$atomKey][$indexKey]["state"]["index"]; 
					if( ($term==$currentIndex) || ($ini==$currentIndex)){
						//sem estados
						$processedPdbArray[$chainKey][$atomKey][$indexKey]["options"] = [];
					}else{
						//com estados
						$processedPdbArray[$chainKey][$atomKey][$indexKey]["options"] = $changeOptions;
					}					
					
					$i++;					
				}				
			}
		}

		// 		echo "### THRID LEVEL ARRAY (parsed and combined from previos arrays) \n";
		//		print_r($processedPdbArray);

		return $processedPdbArray;
	}

	private function updateSingleChainsArrayWithResultArrayEditted ($jsonArray, $chainFilesAsArray){
		// Looping pdb array, compare changed states and update chains array
		
		foreach ($jsonArray as $chainKey => $chainValue){
			//echo "$chainKey\n";

			foreach ($chainValue as $atomKey => $atomValue){
				//echo "\t$atomKey\n";

				foreach ($atomValue as $indexKey => $indexValue){
					//echo "\t\t$indexKey\n";
					$jsonState = $indexValue["state"]["value"];
					$jsonIndex = $indexKey;

					$chainState = $chainFilesAsArray[$chainKey][$jsonIndex];

					//echo "\t\t\t$jsonState $jsonIndex ($chainState)\n";

					// if found
					if($jsonState != $chainState){
						//echo "found $chainState to $jsonState\n";
						$chainFilesAsArray[$chainKey][$jsonIndex] = $jsonState;
					}
				}
			}
		}

				//echo "### MODIFIED CHAINS ARRAY (edited bt user) \n";
				//print_r($chainFilesAsArray);

		return $chainFilesAsArray;
	}

	private function createChainFilesFromChainsArray($processedChainFilesAsArray){

		//print_r($processedChainFilesAsArray);
		foreach ($processedChainFilesAsArray as $chainKey=>$chainValue){
			// 			echo "\n====CHAIN======\n";
			// 			echo "$chainKey ";
				
			$chainFileAsString = "";
				
			$cont = 0;
			foreach ($chainValue as $atomKey => $atomValue){
				//  				echo "\t$atomKey $atomValue\n";
				if($atomKey==0){
					$chainFileAsString .= $atomValue;
				}else{
					// the value
					$chainFileAsString .= $atomValue;
						
					// fix spaces (max size 4)
					$cont++;

					$atomValueSize = strlen($atomValue);
					if($atomValueSize==1){
						$chainFileAsString .= "     ";
					}else if($atomValueSize==2){
						$chainFileAsString .= "    ";
					}else if($atomValueSize==3){
						$chainFileAsString .= "   ";
					}else if($atomValueSize==4){
						$chainFileAsString .= "  ";
					}else if($atomValueSize==5){
						$chainFileAsString .= " ";
					}					
						
					if($cont==10){
						$chainFileAsString .= "\n";
						$cont = 0;
					}
				}
			}
				
			//echo $chainFileAsString;
			// Create chain file path
			$chainFilePath = $this->pathFiles.$this->pdbFileNameNoExtension.'.'.$chainKey;
			// Create chain file
			$fp = fopen($this->pathFiles.$this->pdbFileNameNoExtension.'.'.$chainKey, 'w');
			fwrite($fp, $chainFileAsString);
			fclose($fp);
				
		}
		return true;
	}
	
	private function removeInitialAndFinalElement($resultWithPDBIndex){
		
		
		
		foreach ($resultWithPDBIndex as $keyChain=>$valueChain){
			foreach ($valueChain as $keyAtom=>$valueAtom){
				foreach ($valueAtom as $keyIndex=>$keyValue){
					if($keyIndex == 1){
						$resultWithPDBIndex[$keyChain][$keyAtom][$keyIndex]['options'] = [];
					}
				}
			}
		}
		
		return $resultWithPDBIndex;
	}
	
	/**
	 * Parse chains files first, then pdb files. The result is a JSON file
	 * @return json string or null (success/fail)
	 */
	public function generateJsonFromChainFilesAndPdbFile(){
	
		$this->log("# Generate json from chain files and pdb files","DEBUG");
	
		$pdbFullPath = $this->pathFiles.$this->pdbFileName;
	
		if(file_exists($pdbFullPath)){
			$pdbFile = fopen($pdbFullPath, "r");
			
			/** Parse pdb file to array, exp:
			[E] => Array
			(
					[VAL] => Array
					(
							[15] =>
							[57] =>		
			*/						
			$pdbIndexes = $this->parsePdbToIndex($this->pdbFileName);
			//print_r($pdbIndexes);			
			//exit();			
			
			/** Parse chains files to one array, exp: 
			[E] => Array
		        (
		            [LYS] => Array
		                (
		                    [2] => Array
		                        (
		                            [state] => Array
		                                (
		                                    [value] => LYS
		                                )
		
		                        )
			*/
			$chainsArray = $this->parseChainFiles($this->chainFileNamesArray);
			//print_r($chainsArray);
			//exit();
			
			/** Merge chains and pdb, exp: 
			[E] => Array
		        (
		            [LYS] => Array
		                (
		                    [2] => Array
		                        (
		                            [state] => Array
		                                (
		                                    [value] => LYS
		                                    [index] => 16 <---add--------------------
		                                )		
		                            [options] => Array <----add-------------------
		                                (
		                                    [0] => LYS
		                                    [1] => LYSN
		                                )
		
		                        )
			*/			
			$result = $this->mergeChainsAndPdb($chainsArray, $pdbIndexes);
			//print_r($result);
			//exit();		
			
			// Sorting (second level, exp.: ALA, ARG, ASN, etc.)
			$this->log("Sorting second level array, exp.: ALA, ARG, ASN, etc.","DEBUG");
			ksort($result);
			foreach ($result as $key=>$value){
				ksort($result[$key]);							
			}
			//print_r($result);
			
			//remove first and end element (I, T)
			$finalResult = $this->removeInitialAndFinalElement($result);
			//print_r($finalResult);
			//exit();
			
			// Write final result array to JSON file
			$fp = fopen($this->pathFiles.$this->pdbFileNameNoExtension.'.json', 'w');
			$jsonString = json_encode($finalResult,JSON_PRETTY_PRINT);
			fwrite($fp, $jsonString);
			fclose($fp);
			
			return $jsonString;
		}else{
			$this->log("FAILED - The file doest exist:".$pdbFullPath,"ERROR");
			return false;
		}
		
	}

	/**
	 * Update chain files from eddited json.
	 * @return json string or null (success/fail)
	 */
	public function updateChainFiles(){
		$this->log("updateChainFiles", "DEBUG");
		
		$jsonFile = $this->pathFiles . '/' . $this->pdbFileNameNoExtension.".json";

		if(!file_exists($jsonFile)){
			$this->log("FAILED - The file doest exist:".$jsonFile,"ERROR");
			return false;
		}else{
			
			$jsonFilePath = $this->pathFiles.$this->pdbFileNameNoExtension.'.json';
			
			if(!file_exists($jsonFilePath)){
				$this->log("FAILED - The file doest exist:".$jsonFilePath,"ERROR");
				return false;
			}
			// get editted json file by user
			$resultJsonEditted = file_get_contents($jsonFilePath);
			
			// transform json to array
			$resultArrayEditted = json_decode($resultJsonEditted,true);
			//print_r($pdbArray);
			//exit();
			
			// transform chain files in one single array (this will be used to write the final chains files)
			$chainFilesSingleArray = $this->parseChainFilesToSingleArray($resultArrayEditted);
			//print_r($chainFilesAsArray);
			//exit();
			
			// update single chains array with editted json by user
			$chainFilesSingleArrayUpdated = $this->updateSingleChainsArrayWithResultArrayEditted($resultArrayEditted,$chainFilesSingleArray);
			//print_r($processedChainFilesAsArray);
			//exit();
			
			// create chain files from single chains array
			$result = $this->createChainFilesFromChainsArray($chainFilesSingleArrayUpdated);
				
			return true;
		}

	}
	
	/**
	 * Update editted json to file (apply)
	 * @param string json content
	 * @return boolean (succes/fail)
	 */
	public function uploadEdittedJson($jsonString){

		try {
			// Write new JSON file
			$jsonFile = $this->pathFiles.$this->pdbFileNameNoExtension.'.json';
			$fp = fopen($jsonFile, 'w');
			fwrite($fp, $jsonString);
			fclose($fp);
			return true;
		} catch (Exception $e) {
			$this->log("FAILED - Coundt save:".$jsonFile." - ".$e->getMessage(),"ERROR");
			return false;
		}
		
	}
}
