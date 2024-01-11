<?php
session_start ();
$session_id = session_id ();

$postdata = file_get_contents ( "php://input" );
$request = json_decode ( $postdata );

if(isset($request->params->prepName)){
	$dockingProteinDir = "../session-files/$session_id/DOCKING/PROTEIN";
	//TODO: pymol required (sudo apt-get install pymol)
	shell_exec("cd $dockingProteinDir; pymol -cq ../../../../blind-docking/makeGridConfFromLigand.py " . $request->params->prepName);

	$gridConf = array();
	if ($file = fopen("$dockingProteinDir/grid.conf", "r")) {
		while(!feof($file)) {
			$line = fgets($file);
			$pieces = explode(": ", $line);
			$gridConf[$pieces[0]] = explode("\n", $pieces[1])[0];
		}
		fclose($file);
	}
	echo json_encode($gridConf);
}

?>