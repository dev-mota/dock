<?php
include '../LigandRotbParser.php';

//$fileName = "1a1e_ligand_rnum.top";
$fileName = "ligand_cd39d8905f_1.top";

$ligandRotbParser = new LigandRotbParser();
copy(
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/backup/".$fileName, 
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/".$fileName);

////////////////////////////////////////////////////////////
echo "Step 1 - generate json\n";
$jsonResultStep1 = $ligandRotbParser->generateJsonFromTopFile(
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/".$fileName,
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/");

if($jsonResultStep1!=false){
	echo "Success!\n";
	echo "$jsonResultStep1\n";
}else{
	echo "Failed!\n";
}

////////////////////////////////////////////////////////////
echo "Step 2 - generate edited json\n";
$arrayElementsToBeRemoved = [1,3];
$jsonResultStep2 = $ligandRotbParser->generateCopyJsonEdited(
		$jsonResultStep1, 
		$arrayElementsToBeRemoved,
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/");

if($jsonResultStep2!=false){
	echo "Success!\n";
	echo "$jsonResultStep2\n";
}else{
	echo "Failed!\n";
}

////////////////////////////////////////////////////////////
echo "Step 3 - update top\n";
$resultStep3 = $ligandRotbParser->updateTopFile(
		$jsonResultStep2,
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/".$fileName,
		"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/".$fileName);







