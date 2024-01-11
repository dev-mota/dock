<?php
include_once "../../lib/utils/utils.php";

class BlindDockingLib{
    
    public function __construct(){
        
    }
    
    public function calcBlindDocking($protein_file_name, $session_id){
    
	 $dockingProteinDir = "../../session-files/$session_id/PROTEIN";

         //TODO: pymol required (sudo apt-get install pymol)
	 shell_exec("cd $dockingProteinDir; pymol -cq ../../../blind-docking/makeGridConfFromLigand.py " . $protein_file_name);

	 //syslog(LOG_INFO|LOG_LOCAL0, "cd $dockingProteinDir; pymol -cq ../../../blind-docking/makeGridConfFromLigand.py " . $protein_file_name);
        
        $gridConf = array();
        if ($file = fopen("$dockingProteinDir/grid.conf", "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                
                // Check if has ':'
                if (strpos($line, ':') !== false) {
                    $pieces = explode(": ", $line);
                    $gridConf[$pieces[0]] = explode("\n", $pieces[1])[0]; // removing '/n'
                }
                
            }
            fclose($file);
        }else{
            $gridConf = null;
        }

        return $gridConf;
        
    }
}

