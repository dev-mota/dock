<?php 
require_once '../environment/mmffligand-environment.php';
require_once '../environment/dtstatistic-environment.php';
require_once '../environment/obprop-environment.php';

class MMFFligand {
	private $session_id;
	private $operationDir;
	private $debug;
	
	public function __construct($session_id, $operationDir) {

		openlog("[dockthor][docking][MMFFligand]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

		putenv ( "BABEL_LIBDIR=" . $GLOBALS ["BABEL_LIBDIR"] );
		putenv ( "BABEL_DATADIR=" . $GLOBALS ["BABEL_DATADIR"] );
		putenv ( "BABEL_BIN=". $GLOBALS ["BABEL_BIN"]);
		putenv ( "LD_LIBRARY_PATH=" . $GLOBALS ["LD_LIBRARY_PATH"] );
		putenv ( "PYTHONPATH=" . $GLOBALS ["PYTHONPATH"] );
		//putenv ( "DTSTATISTICS_LIBDIR=" . $GLOBALS ["DTSTATISTICS_LIBDIR"] );
		putenv ( "OBPROP_LIBDIR=" . $GLOBALS ["OBPROP_LIBDIR"] );
		
		$this->session_id = $session_id;
		$this->operationDir = $operationDir;
		$this->debug = false;
	}
	
	public function prepareOneFile($file) {
		
		if($this->debug){
			// syslog(LOG_DEBUG|LOG_LOCAL0, "start prepareOneFile(\$file) ... ".$file->fileIdWithExtension);	
		}		
		
		$success = false;
		$mapFileContent = "";
		
		//$fileToExecute = $fileValue->nameWithExtension;
		$fileToExecute = $file->fileIdWithExtension;
		
		$ext = pathinfo ( $fileToExecute, PATHINFO_EXTENSION );
		
		//$fileId = $fileValue->name;
		$fileId = $file->fileId;
				
		// result directory creation
		$resultDir = "../session-files/$this->session_id/$this->operationDir/OUTPUT";
		if (! file_exists ( $resultDir ) || ! is_dir ( $resultDir )) {
			mkdir ( $resultDir );
		}
		$resultDir .= "/".$fileId;
		if (! file_exists ( $resultDir ) || ! is_dir ( $resultDir )) {
			mkdir ( $resultDir );
		}
		
		$topCount = 0;
		if($ext != 'top') {

			//execute babel
			$babelCommand = "cd ../session-files/$this->session_id/$this->operationDir/; ". $GLOBALS ["BABEL_BIN"]. " INPUT/$fileToExecute -O OUTPUT/$fileId/$fileId"."_.sdf -m -r 2>&1 1> /dev/null";
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "execute babel command: ".json_encode($babelCommand)."...");
			}
			shell_exec($babelCommand); // caution with underline _
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "finish babel command");
			}
				
			// check hidrogen
			$hidrogenOption = "";
			if( isset($file->hidrogen) && $file->hidrogen){
				$hidrogenOption = " -h ";
			}
				
			//execute mmffligand
			$mmffligandCommand = "cd ../session-files/$this->session_id/$this->operationDir/OUTPUT/; ".$GLOBALS ["MMFFLIGAND_BIN"]." -vs $fileId $hidrogenOption -filter";
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "execute mmfcommand command: ".json_encode($mmffligandCommand)."...");
			}
			$resultMmffligand = shell_exec($mmffligandCommand);
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "finish mmfcommand command - ");			
				syslog(LOG_DEBUG|LOG_LOCAL0, "parse mmfcommand result");
			}
			$resultParseArray = $this->parseMmffligandResult($resultMmffligand);
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "finish parse mmfcommand result.");
			}
			// Create log file for mmffligand
			$operationDirLowCase = strtolower($this->operationDir);
			$logFileNameIdPath = "../session-files/$this->session_id/$this->operationDir/".$operationDirLowCase."_log.txt";
			$logFile = fopen($logFileNameIdPath, 'a');
			fwrite($logFile, $resultMmffligand);
			fclose($logFile);
			
			//Invalid structure:
			$file->invalidStructures = $resultParseArray['invalidStructures'];
			
			//Valid structure:
			//update file with valid structure
			$validStructureCommand = "cd ../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/; ls -1 *.top | wc -l";
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "execute valid structure command: ".json_encode($validStructureCommand));
			}
			$topCount = shell_exec($validStructureCommand);
			if($this->debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "finish valid structure command");
			}
			$topCount = preg_replace('~[\r\n]+~', '', $topCount);//removing \n
			
			//Status
			$file->errorMessage = $resultParseArray['errorMessage'];
			
			//check result
			if(strcmp($resultParseArray['mmffligandResult'],"success")==0){
				
				//for map file (original file name and random file name id)
				$mapFileContent = "$file->originalName,$fileId\n";
				
				$file->mmffligandOperationStatus = 'success';
				$file->errorMessage = '';
				
			}else{
				$file->mmffligandOperationStatus = 'failed';
				$file->state = 'failed';
								
			}
				
		} else if($ext == 'top') {

			$inputFilePath = "../session-files/$this->session_id/$this->operationDir/INPUT/$fileToExecute";
			
			//$outpuFilePath = "../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/$fileToExecute";
			
			$fileNameForOutput = $file->fileId."_1.".$file->fileExtension;
			$outpuFilePath = "../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/$fileNameForOutput";			
			// syslog(LOG_INFO|LOG_LOCAL0, "DEBUG MMF: $fileToExecute     ".json_encode($outpuFilePath));

			if( strpos(file_get_contents($inputFilePath), '$TOTAL_INTRAMOLECULAR_INTERACTIONS') !== false) {
				copy($inputFilePath, $outpuFilePath);
				$topCount = 1; // if is a top file, 1 top has =)

				$file->state = 'prepared';
				$file->mmffligandOperationStatus = 'success';
				$file->errorMessage = '';
				$file->invalidStructures = 0;    // MODISA
				$mapFileContent = "$file->originalName,$fileId\n";   // MODISA
			} else{
				$file->errorMessage = 'Invalid MMFFLigand topology file';
				$file->state = 'content-error';
			}
			
		} else {
			$result['SUCCESS'] = false;
			return $result;
		}
		
		// Update file messages
		$file->validStructure = $topCount;
		
		// Check if file is success,failed ou partial failed
		if( isset($file->validStructure) && isset($file->invalidStructures) ){
			if( ($file->validStructure >= 0) && ($file->invalidStructures == 0) ){
				$file->state = 'prepared';			
			}else if(($file->validStructure == 0) && ($file->invalidStructures >= 0)){
				$file->state = 'failed';			
			}else if(($file->validStructure >= 0) && ($file->invalidStructures >= 0)){
				$file->state = 'partialFailed';
			}
		}
				
		// Create/update mapfile
		$operationDirLowCase = strtolower($this->operationDir);
		$mapFileNameIdPath = "../session-files/$this->session_id/$this->operationDir/".$operationDirLowCase."_mapfile.csv";		
		$header = "";
		if(!file_exists($mapFileNameIdPath)){
			$header = '"Original file name","Coded name"'."\n";
		}
		$mapFile = fopen($mapFileNameIdPath, 'a');
		fwrite($mapFile, $header.$mapFileContent);
		fclose($mapFile);
		
		// obprop
		/*
		if($ext != 'top') {   // MODISA TODO run obprop only for non-top files
			$dtstatisticHidrogen = "";
			if($this->operationDir == 'LIGAND'){
				if(isset($file->hidrogen) && $file->hidrogen){
					$dtstatisticHidrogen = "new";
				}
				
				$obpropCommand = "cd ../session-files/$this->session_id/$this->operationDir/OUTPUT/; chmod +x ".$GLOBALS['OBPROP_LIBDIR']." ; ".$GLOBALS['OBPROP_LIBDIR']." $fileId $dtstatisticHidrogen";
				if($this->debug){
					syslog(LOG_DEBUG|LOG_LOCAL0, "obprop command start ".$obpropCommand);
				}
				$obpropCommandResult = shell_exec($obpropCommand);
				if($this->debug){
					syslog(LOG_DEBUG|LOG_LOCAL0, "obprop command finish");
				}
			}
		}*/
		
		if($this->debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "finish prepareOneFile(\$file) ... ".$file->fileIdWithExtension);
		}
		
		//result response
		$result['file'] = $file;
		$result['SUCCESS'] = true;
		return $result;
	}
	
// 	//$this->operationDir = "LIGAND" OR "COFACTOR"
// 	public function prepareMultFiles($files) {
// 		$result = array();
// 		$success = false;
		
// 		// Create mapfile
// 		$mapFile = fopen("../session-files/$this->session_id/$this->operationDir/mapfile.csv", 'w');
// 		$mapFileContent = '"Original file name","Coded name"'."\n";
		
// 		// LIST FILES
// 		//$sessionFileRow = exec("cd ../session-files/$this->session_id/LIGAND/INPUT/; find . -maxdepth 1 -type f",$fileOutput,$sessionFileError);
// 		//while(list(,$sessionFileRow) = each($fileOutput)){
		
// 		$mmffligandOperationStatus = array();
// 		foreach ($files as $fileKey => $fileValue){
// 			$mmffligandOperationStatus[$fileKey] = 'STARTING';
			
// 			//$fileToExecute = substr($sessionFileRow, 2);
// 			$fileToExecute = $fileValue->nameWithExtension;			
			
// 			$ext = pathinfo ( $fileToExecute, PATHINFO_EXTENSION );
			
// 			//$fileId = preg_replace ( '/\\.[^.\\s]{2,4}$/', '', $fileToExecute );
// 			$fileId = $fileValue->name;
			
// 			// result directory creation
// 			$resultDir = "../session-files/$this->session_id/$this->operationDir/OUTPUT";
// 			if (! file_exists ( $resultDir ) || ! is_dir ( $resultDir )) {
// 				mkdir ( $resultDir );
// 			}
// 			$resultDir .= "/".$fileId;
// 			if (! file_exists ( $resultDir ) || ! is_dir ( $resultDir )) {
// 				mkdir ( $resultDir );
// 			}
			
// 			if($ext != 'top') {				
// 				if($this->operationDir == "LIGAND"){
// 					//execute babel			
// 					$babelCommand = "cd ../session-files/$this->session_id/$this->operationDir/; ". $GLOBALS ["BABEL_BIN"]. " INPUT/$fileToExecute OUTPUT/$fileId/$fileId"."_.sdf -m";
// 					shell_exec($babelCommand); // caution with underline _
// 				} else {
// 					copy("../session-files/$this->session_id/$this->operationDir/INPUT/$fileToExecute", "../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/$fileToExecute");
// 				}
				
// 				// check hidrogen
// 				$hidrogenOption = "";
// 				if( isset($fileValue->hidrogen) && $fileValue->hidrogen){
// 					$hidrogenOption = " -h ";
// 				}
				
// 				//execute mmffligand
// 				$mmffligandCommand = "cd ../session-files/$this->session_id/$this->operationDir/OUTPUT/; mmffligand -vs $fileId $hidrogenOption -filter";
// 				shell_exec($mmffligandCommand);
				
// 				//check if the result file was created by mmffligand
// 				foreach (glob("../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/*") as $arquivo) {
// 					$mmffligandOperationStatus[$fileKey] = 'SUCCESS';
// 					break;
// 				}
				
// 				//include in mapfile content (original file name and random file name id)
// 				$mapFileContent .= "$fileValue->originalName,$fileId\n";	
// 			} else {
// 				copy("../session-files/$this->session_id/$this->operationDir/INPUT/$fileToExecute", "../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/$fileToExecute");
// 				$mmffligandOperationStatus[$fileKey] = 'SUCCESS';
// 			}
			
// 			//update file with valid structure
// 			$validStructureCommand = "cd ../session-files/$this->session_id/$this->operationDir/OUTPUT/$fileId/; ls -1 *.top | wc -l";
// 			$topCount = shell_exec($validStructureCommand);
// 			$topCount = preg_replace('~[\r\n]+~', '', $topCount);//removing \n
// 			$files[$fileKey]->validStructure = $topCount;
// 		}
		
// 		$mmffligandExecutedToAllFiles = true;
// 		foreach ($mmffligandOperationStatus as $fileKey => $status){
// 			if($status != 'SUCCESS'){
// 				$mmffligandOperationStatus[$fileKey] = 'ERROR';
// 				$mmffligandExecutedToAllFiles = false;
				
// 				if(!isset($result['ERROR_FILES'])){
// 					$result['ERROR_FILES'] = array();
// 				}
				
// 				array_push($result['ERROR_FILES'], $fileKey);
// 			}
// 		}
		
		
// 		if($mmffligandExecutedToAllFiles){
// 			fwrite($mapFile, $mapFileContent);
// 			fclose($mapFile);
				
// 			//create result zip file
// 			$this->createZipDir($this->operationDir);
			
// 			$success = true;
// 		} else {
// 			$success = false;
// 		}
// 		$result['files'] = $files;
// 		$result['SUCCESS'] = $success;
// 		return $result;
// 	}
	
	private function createZipDir() {		
		shell_exec ( "cd ../session-files/" . $this->session_id . "; zip -r $this->operationDir/$this->operationDir.zip $this->operationDir");
		return true; 
	}

	private function createZipFile($file_name) {
		$mmffligand_out_dir = preg_replace ( '/\\.[^.\\s]{2,4}$/', '', $file_name ); // retirando extensão
		$mmffligand_out_dir = "../session-files/" . $this->session_id . "/$this->operationDir/$mmffligand_out_dir";
		if (! file_exists ( $mmffligand_out_dir )) {
			mkdir ( $mmffligand_out_dir );
		}
		
		foreach ( glob ( "../session-files/" . $this->session_id . "/$this->operationDir/*" ) as $file ) {
			if ($file == '.' || $file == '..' || $file == $mmffligand_out_dir)
				continue;
			if (basename ( $file ) == $file_name) {
				if (! file_exists ( "$mmffligand_out_dir/INPUT" )) {
					mkdir ( "$mmffligand_out_dir/INPUT" );
				}
				copy ( $file, "$mmffligand_out_dir/INPUT/" . basename ( $file ) );
			} else {
				if (! file_exists ( "$mmffligand_out_dir/OUTPUT" )) {
					mkdir ( "$mmffligand_out_dir/OUTPUT" );
				}
				if (basename ( $file ) == "$mmffligand_out_dir.top" || basename ( $file )) {
					copy ( $file, "$mmffligand_out_dir/OUTPUT/" . basename ( $file ) );
				}
			}
		}
		shell_exec ( "cd ../session-files/" . $this->session_id . "/$this->operationDir; zip -r " . basename($mmffligand_out_dir) . ".zip " . basename ($mmffligand_out_dir) . "; rm -rf " . basename ($mmffligand_out_dir) );
		return true;
	}
	
	private function parseMmffligandResult($stringResult){
		
 		$message['mmffligandResult'] = "Some error occurred";
		
		$arr = explode("\n", $stringResult);
		$runnedMmffligand = false; 

		$totalOfInvalidStructure = 0; // Case dont have "Total of invalid" line
		
		$message['errorMessage'] = "";
		
		for ($i = 0; $i < count($arr); $i++) {
			
			// Means error, ex.: ligand_3c4c7e_1.sdf: invalid molecule (i.e. MW > 1500 or rotatable bonds > 40).
			if(substr( $arr[$i], 0, 7 ) === "ligand_"){
				
				$errorMessageArray = explode(":", $arr[$i]);
				
				//structure id
				$pos = strrpos($errorMessageArray[0], "_");
				$id = substr( $errorMessageArray[0], $pos+1, -4 );
				
				$message['errorMessage'] = $id.":".$errorMessageArray[1].$message['errorMessage']."; ";
				
				$totalOfInvalidStructure++;
			} 
			// For any situation
			else if(substr( $arr[$i], 0, 16 ) === "Total of invalid"){
				$errorMessageArray = explode(": ", $arr[$i]);
				$message['invalidStructures'] = $errorMessageArray[1];
				
				//las check for error:
				if($message['invalidStructures']=="0"){
					$message['mmffligandResult'] == "Some problem occurred";					
				}
			}
			
			if(substr( $arr[$i], 0, 5 ) === "0 ..."){
				$runnedMmffligand = true;
			}
			
		}
		
		if(!isset($message['invalidStructures'])){
			$message['invalidStructures'] = $totalOfInvalidStructure;
		}
		
		if($runnedMmffligand){
			$message['mmffligandResult'] = "success";
		}
		
// 		shell_exec ("rm ../session-files/$this->session_id/$this->operationDir/OUTPUT/invalid.txt");
		
		return $message;
	}
}

?>
