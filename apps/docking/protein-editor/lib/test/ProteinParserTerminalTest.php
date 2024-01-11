<?php
include '../ProteinParser.php';

$proteinParser = new ProteinParser(
		"/home/iuri/workspace/DockThor-3.0/apps/docking/protein-editor/lib/test/session-files-test/",
		"protein_2e509693b8.pdb",
		array("protein_2e509693b8.E","protein_2e509693b8.I"));

echo "# 1 - generateJson:\n";
$result = $proteinParser->generateJsonFromChainFilesAndPdbFile();
if($result!=null){
	echo "Success!\n";
}else{
	echo "Failed!\n";
}

// echo "# 2 - edit json manually\n";

// echo "3 - updateChainFiles:";
// $result = $proteinParser->updateChainFiles();
// echo $result ? "success!\n" : "failed!\n";




