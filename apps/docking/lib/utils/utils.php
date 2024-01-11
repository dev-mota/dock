<?php
class Utils {
	
	public function __construct(){	
		
	}
	
	public static function startsWith($complete_string, $string_to_search) {
		$length = strlen ( $string_to_search );
		return (substr ( $complete_string, 0, $length ) === $string_to_search);
	}
	public static function endsWith($complete_string, $string_to_search) {
		$length = strlen ( $string_to_search );
		if ($length == 0) {
			return true;
		}
		
		return (substr ( $complete_string, - $length ) === $string_to_search);
	}
	
	public function clearDir($dirPath) {
		if (! is_dir ( $dirPath )) {
			throw new InvalidArgumentException ( "$dirPath must be a directory" );
		}
		if (substr ( $dirPath, strlen ( $dirPath ) - 1, 1 ) != '/') {
			$dirPath .= '/';
		}
		$files = glob ( $dirPath . '*', GLOB_MARK );
		foreach ( $files as $file ) {
			if (is_dir ( $file )) {
				$this->clearDir( $file );
				rmdir ( $file );
			} else {
				unlink ( $file );
			}
		}
		return true;
	}
	
	public function resetLigandStructureDir($session_id){
		$session_dir = $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/session-files/$session_id";
		if(!is_dir($session_dir)){
			mkdir($session_dir);
		}
		
		$ligand_dir = $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/session-files/$session_id/LIGAND/";
		if(!is_dir($ligand_dir)){
			mkdir ( "$ligand_dir");
		}else {
			$this->clearDir($ligand_dir);
		}
		
		mkdir ( "$ligand_dir/INPUT");
		mkdir ( "$ligand_dir/OUTPUT");	
		
	}
	
	public function resetCofactorStructureDir($session_id){
		$session_dir = $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/session-files/$session_id";
		if(!is_dir($session_dir)){
			mkdir($session_dir);
		}
		
		$cofactor_dir = $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/session-files/$session_id/COFACTOR/";
		if(!is_dir($cofactor_dir)){
			mkdir ( "$cofactor_dir");
		}else {
			$this->clearDir($cofactor_dir);
		}
	
		mkdir ( "$cofactor_dir/INPUT");
		mkdir ( "$cofactor_dir/OUTPUT");
	
	}
	
	public function generateFileId(){
		return substr ( md5 ( microtime () ), rand ( 0, 26 ), 10 );
	}
	
	public function saveFile($path_to, $fileNameIdWithExtension,$tmpFilePath) {
		if (! file_exists ( $path_to ) || ! is_dir ( $path_to )) {
			mkdir ( $path_to );
		}
		// 	else {
		//		clearDir ( $path_to );
		// 	}
		//move_uploaded_file ( $file ['tmp_name'], "$path_to/" . $fileNameIdWithExtension );
		return move_uploaded_file ( $tmpFilePath, "$path_to/" . $fileNameIdWithExtension );
	}
	
	public function convertInToPDB($parentPath, $inFileName){
		//convert .in to _prep.pdb
		$file_name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $inFileName ); // retirando extensão
		
		//shell_exec ( "cd $parentPath; awk 'NF>0{printf(". '"ATOM  %5s %-4s %3s %1s%4s    %8.3f%8.3f%8.3f %5.2f %5.2f          %2s  \n",$1, lenght($13)>3 ? $13 : " "$13 ,$14,$15,$16,$3,$4,$5,$12,$2,substr($13,1,1))}' . "' $inFileName > $file_name_without_extension" . "_prep.pdb" );
        //shell_exec ( "cd $parentPath; awk 'NF>0{printf(". '"ATOM  %5s %4s %3s %1s%4s    %8.3f%8.3f%8.3f%6s%6s          %2s  \n",$1,$13,$14,$15,"1",$3,$4,$5,"1.00","0.00",substr($13,1,1))}' . "' $inFileName > $file_name_without_extension" . "_prep.pdb" );
		//shell_exec ( "cd $parentPath; awk 'NF>0{printf(". '"ATOM  %5s %4s %3s %1s%4s    %8.3f%8.3f%8.3f%6s%6s          %2s  \n",$1,$13,$14,$15,$16,$3,$4,$5,"1.00","0.00",substr($13,1,1))}' . "' $inFileName > $file_name_without_extension" . "_prep.pdb" );
		
		
		//shell_exec ( "cd $parentPath; awk 'NF>0{printf(". '"ATOM  %5s %-4s %3s %1s%4s	%8.3f%8.3f%8.3f %5.2f %5.2f      	%2s  \n",$1, length($13)>3 ? $13 : " "$13 ,$14,$15,$16,$3,$4,$5,$12,$2,substr($13,1,1))}' . "' $inFileName > $file_name_without_extension" . "_prep.pdb" );
		// Novo awk
		$command = "cd $parentPath; awk 'NF>0{printf(". '"ATOM  %5s %-4s %3s %1s%4s    %8.3f%8.3f%8.3f %5.2f %5.2f          %2s  \n",$1, length($13)>3 ? $13 : " "$13 ,$14,$15,$16,$3,$4,$5,$12,$2,substr($13,1,1))}' . "' $inFileName > $file_name_without_extension" . "_prep.pdb";
		shell_exec($command);
		// syslog(LOG_INFO|LOG_LOCAL0, "convertInToPDB command:$command");
		
		return $file_name_without_extension . "_prep.pdb";
	}
	
	public function convertTopToPDB($parentPath, $topFileName){
		$file_name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $topFileName ); // retirando extensão
		$mmff_pdb_file_name = $file_name_without_extension . "_mmff.pdb";
		//convert .in to _prep.pdb
// 		shell_exec ( "cd $parentPath; name=`echo $topFileName | cut -d '.' -f 1`; awk -v 'RS=\n\n' '1;{exit}' $1 >" . '${name}_conv.tmp;' . " awk 'NF>2{printf(" . '"ATOM  %5s  %-3s %3s %1s%4s    %8.3f%8.3f%8.3f%6.2f   %3s          %2s  \n", $2,$1,"MOL","X","1",$5,$6,$7,$4,$3,$1)}'. "' " . '${name}_conv.tmp > ${name}_mmff.pdb;rm ${name}_conv.tmp');
		$cmd = "cd " . $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/lib/utils/scripts; ./conversaoTopPDB.sh $parentPath/$topFileName $parentPath/$mmff_pdb_file_name";
		syslog(LOG_INFO|LOG_LOCAL0, "convertTopToPDB command:$cmd");
		$output = shell_exec($cmd);
		syslog(LOG_INFO|LOG_LOCAL0, "convertTopToPDB output:$output");
		return $mmff_pdb_file_name;
	}
	
	public function checkEqualFiles($files,$dirPath){
		
		$response = array ();
		$response ["equalFiles"] = array();
		$tempFiles = array();
		
		
		$countI = count($files);
		for ($i = 0; $i < $countI; $i++) {
			$md5 = md5(file_get_contents($dirPath.$files[$i]->fileIdWithExtension));
			$tempFiles["$md5"][] = $files[$i];
			// 			for ($j = ($i+1); $j < count($files); $j++) {
			// 				$pathA = $session_dir."/LIGAND/INPUT/".$files[$i]->fileIdWithExtension;
			// 				$pathB = $session_dir."/LIGAND/INPUT/".$files[$j]->fileIdWithExtension;
			// 				$resultDiff = shell_exec("diff $pathA $pathB");
			// 				if($resultDiff == null){
			// 					$response ["equalFiles"][] = [[$files[$i]->originalName,$files[$i]->fileIdWithExtension],[$files[$j]->originalName,$files[$j]->fileIdWithExtension]];
			// 				}
		
			// 			}
		}
		
		$response ["isfound"] = false;
		
		foreach ($tempFiles as $key => $value) {
			$count = count($value);
			if($count==1){
				unset($tempFiles[$key]);
			}else{
				$response ["isfound"] = true;
			}
		}
		
		$response ["equalFiles"] = $tempFiles;
		return $response;
	}
	
}
?>
