<?php
require_once '../environment/pdbthorbox-environment.php';
include_once '../lib/utils/utils.php';

class Pdbthorbox {
	private $session_id;
	public function __construct($session_id) {
		putenv ( "MMFF=" . $GLOBALS ["MMFF_PATH"] );
		$this->session_id = $session_id;
	}
	public function prepare($file_name) {
		$result = array();
		$success = false;
		if (isset ( $file_name )) {
			$proteinPath = "../session-files/" . $this->session_id . "/PROTEIN/";
			foreach ( glob ( $proteinPath."*.pdb" ) as $pdb_file ) {

				// syslog(LOG_INFO|LOG_LOCAL0, "DEBUG!!! =): ".$pdb_file);
				// syslog(LOG_INFO|LOG_LOCAL0, "DEBUG!!! =(: ".file_get_contents($pdb_file) );		
				$fileContent = file_get_contents($pdb_file);
				$countAtoms = substr_count($fileContent, "ATOM");
				// syslog(LOG_INFO|LOG_LOCAL0, "DEBUG!!! =/: ".$count);		
				// syslog(LOG_INFO|LOG_LOCAL0, "DEBUG!!! =#: ");		

				if($countAtoms>40){
					if (basename( $pdb_file ) == $file_name) {						
						if(cleanProteinFile($proteinPath,$file_name)){
							$file_name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $file_name ); // retirando extensão
												
							// $whoami = shell_exec('whoami');  syslog(LOG_INFO|LOG_LOCAL0, "who am i? ".$whoami);
							
							$command = "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r " . basename ( $pdb_file );
							syslog(LOG_INFO|LOG_LOCAL0, "pdbthorbox prepare command: ".$command);	
							$pdbthorbox_out = shell_exec ( $command );
							syslog(LOG_INFO|LOG_LOCAL0, "pdbthorbox prepare result: ".json_encode($pdbthorbox_out) );
							
							$pdbthorbox_out_lines = explode("\n", $pdbthorbox_out);
							$chains = array();
							foreach ($pdbthorbox_out_lines as $line){
								if(Utils::startsWith($line, "Chains:")){
									$chain_line_columns = explode(" ", $line);
									foreach ($chain_line_columns as $column){
										if($column != "Chains:" && $column != " " && $column != ""){
											$column = "$file_name_without_extension.$column";
											array_push($chains, $column);
										}
									}
									break;
								}
							}
						} else {
							syslog(LOG_INFO|LOG_LOCAL0, "ERROR - failed to clean protein file pdb");	
							$success = false;
						}
					}
				} else{
					syslog(LOG_INFO|LOG_LOCAL0, "WARNING - pdbthorbox.php - prepare - The file ".$file_name." has ".$countAtoms." ATOM!");	
					$success = false;
				}
				
			}
			if (file_exists ( "../session-files/" . $this->session_id . "/PROTEIN/$file_name_without_extension.in" )) {
				$this->createZipFile ( $file_name );
				$success = true;
			}
			
			$result['SUCCESS'] = $success;
			$result['CHAINS'] = $chains;
				
			return $result;
		}
	}
	
	// public function prepareStaticFileName() {
	// echo ($this->session_id);
	// shell_exec ( "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r initial.pdb" );
	// $this->createZipFile ( "initial.pdb" );
	// return true;
	
	// }
	public function reprepare($file_name) {
		$result = array();
		$success = false;
		if (isset ( $file_name )) {
			$file_name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $file_name ); // retirando extensão
			$chain_file_names = "";
			$number_of_chains = 0;
			foreach ( glob ( "../session-files/" . $this->session_id . "/PROTEIN/$file_name_without_extension.*" ) as $file ) {
				$extension = explode ( ".", basename ( $file ) ) [1];
				if (strlen ( $extension ) == 1) {
					$number_of_chains ++;
					$chain_file_names = "$chain_file_names " . basename ( $file );
				}
			}
			// file_put_contents("/home/egaldino/log.txt", "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r $file_name -n $number_of_chains -rebuild $chain_file_names" );
			shell_exec ( "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r $file_name -n $number_of_chains -rebuild $chain_file_names" );
			$pdbthorbox_out = $name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $file_name ); // retirando extensão
			$pdbthorbox_out_lines = explode("\n", $pdbthorbox_out);
			$chains = array();
			foreach ($pdbthorbox_out_lines as $line){
				if(Utils::startsWith($line, "Chains:")){
					$chain_line_columns = explode(" ", $line);
					foreach ($chain_line_columns as $column){
						if($column != "Chains:" && $column != " " && $column != ""){
							$column = "$file_name_without_extension.$column";
							array_push($chains, $column);
						}
					}
					break;
				}
			}
			
			if (file_exists ( "../session-files/" . $this->session_id . "/PROTEIN/$name_without_extension.in" )) {
				$this->createZipFile ( $file_name );
				$success = true;
			}
			$result['SUCCESS'] = $success;
			$result['CHAINS'] = $chains;
			return $result;
		}
	}
	
	// public function reprepareStaticFileName(){
	// $chain_file_names = "";
	// $number_of_chains = 0;
	// foreach ( glob ( "../session-files/" . $this->session_id . "/PROTEIN/$file_name_without_extension.*" ) as $file ) {
	// $extension = explode ( ".", basename ( $file ) ) [1];
	// if (strlen ( $extension ) == 1) {
	// $number_of_chains ++;
	// $chain_file_names = "initial " . basename ( $file );
	// }
	// }
	// // file_put_contents("/home/egaldino/log.txt", "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r $file_name -n $number_of_chains -rebuild $chain_file_names" );
	// shell_exec ( "cd ../session-files/" . $this->session_id . "/PROTEIN;" . $GLOBALS ["PDBTHORBOX_BIN"] . " -r $file_name -n $number_of_chains -rebuild $chain_file_names" );
	// $this->createZipFile ( $file_name );
	// return true;
	// }
	private function createZipFile($file_name) {
		$pdbthorbox_out_dir = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $file_name ); // retirando extensão
		$pdbthorbox_out = "../session-files/" . $this->session_id . "/PROTEIN/$pdbthorbox_out_dir";
		if (! file_exists ( $pdbthorbox_out )) {
			mkdir ( $pdbthorbox_out );
		}
		
		foreach ( glob ( "../session-files/" . $this->session_id . "/PROTEIN/*" ) as $file ) {
			if ($file == '.' || $file == '..' || basename ( $file ) == $pdbthorbox_out_dir || basename ( $file ) == "resumo.out" || basename ( $file ) == "$pdbthorbox_out_dir.json")
				continue;
			if (basename ( $file ) == $file_name) {
				if (! file_exists ( "$pdbthorbox_out/INPUT" )) {
					mkdir ( "$pdbthorbox_out/INPUT" );
				}
				copy ( $file, "$pdbthorbox_out/INPUT/" . basename ( $file ) );
			} else {
				if (! file_exists ( "$pdbthorbox_out/OUTPUT" )) {
					mkdir ( "$pdbthorbox_out/OUTPUT" );
				}
				if (basename ( $file ) == "$pdbthorbox_out_dir.in" || basename ( $file ) == $pdbthorbox_out_dir . "_prep.pdb") {
					copy ( $file, "$pdbthorbox_out/OUTPUT/" . basename ( $file ) );
				}
			}
		}
		shell_exec ( "cd ../session-files/" . $this->session_id . "/PROTEIN; zip -r $pdbthorbox_out_dir.zip $pdbthorbox_out_dir; rm -rf $pdbthorbox_out_dir" );
		return true;
	}
}

function cleanProteinFile($proteinPath, $file_name){
	$cleanProteinCommand = "							
		grep 'ATOM' $proteinPath/$file_name > $proteinPath/temp.pdb && # Delete all non-ATOM lines
		sed '/^ATOM/ s/./ /17' $proteinPath/temp.pdb > $proteinPath/$file_name && # Remove any character from the 17th column - e.g., characteres related to insertions
		rm $proteinPath/temp.pdb &&
		echo 'success';
	";		
	$result = shell_exec($cleanProteinCommand);
	if($result === "success\n"){
		return true;
	} else {
		syslog(LOG_INFO|LOG_LOCAL0, "ERROR - Failed to cleanProteinFile: ");
		syslog(LOG_INFO|LOG_LOCAL0, "Clean protein command: ".$cleanProteinCommand);
		syslog(LOG_INFO|LOG_LOCAL0, "Clean protein result: ".json_encode($result));		
		return false;
	}	
}

?>