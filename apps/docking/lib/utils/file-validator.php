<?php
class FileValidator {
	public static function validatePDB($file_path) {
		$validation_result = array ();
		$validation_result ['VALID'] = true;
		$validation_result ['ERROR_MESSAGE'] = '';
		
		if (file_exists ( $file_path )) {
			if (self::isEmpty ( $file_path )) {
				$validation_result ['VALID'] = false;
				$validation_result ['ERROR_MESSAGE'] = 'Empty file';
			} else {
				if(!self::isPDB($file_path)){
					$validation_result ['VALID'] = false;
					$validation_result ['ERROR_MESSAGE'] = 'File type not supported';
				}
			}
		} else {
			$validation_result ['VALID'] = false;
			$validation_result ['ERROR_MESSAGE'] = 'Upload error';
		}
		return $validation_result;
	}
	
	public static function isEmpty($file_path) {
		$is_empty = false;
		$size = filesize ( $file_path );
		$content = file_get_contents ( $file_path );
		if ($size == 0 || $content == '') {
			$is_empty = true;
		}
		return $is_empty;
	}
	
	public static function sizeAllowed($fileSize) {
		
		$mb = 2;
		$max =  $mb*1024*1024;
		
		if($fileSize>$max){
			return "File too large. The maximum allowed is ".$mb." MB";			
		}else{
			return true;			
		}
	}
	
	public static function isPDB($file_path){
		$isPDB = false;
		$ext = pathinfo($file_path, PATHINFO_EXTENSION);
		if ($ext == 'pdb') {
			$isPDB = true;
		}
		return $isPDB;
	}
	
	public static function checkHasDuplicated($path){
	
// 		$files = glob ( "$path/" . '*', GLOB_MARK );		
// 		for ($i = 0; $i < count($files); $i++) {			
// 			for ($j = $i+1; $j < count($files); $j++) {					
// 				//$fileItemName = basename($file).PHP_EOL;					
// 			}			
// 		}

// 		foreach ( $files as $file ) {
// 			$fileItemName = basename($file).PHP_EOL;
// 			$result = shell_exec("diff $tmpFilePath $path/$fileItemName");
// 			if($result == null){
// 				move_uploaded_file ( $tmpFilePath, "$path/" . $fileNameIdWithExtension);
// 			}
// 		}
	}
}

?>