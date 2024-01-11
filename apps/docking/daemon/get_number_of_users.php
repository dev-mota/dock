<?php

$interested_year = date('Y',strtotime('01/01/2018'));
$unique_emails = array();

foreach (glob("/var/www.new/dockthorV2/apps/docking/daemon/jobs/*") as $job) {

//	$path_parts = pathinfo($job);
	$path_parts = explode("/",$job);
	$filename = $path_parts[count($path_parts)-1];
//    	$filename = $path_parts['filename'];
//    	$extension = $path_parts['extension'];
	echo $filename;
        //$data = date ("d/m/Y H:i:s", filemtime($job));
	$year = date ("Y", filemtime($job));
//	echo '*** DATE of '.$job.':'.$year.'\n interested year: '.$interested_year;

	$teste = strpos($filename,"gmmsb");//+strpos('GMMSB',$filename)+strpos('gmsb',$filename);
	echo '==='.$teste.'===';

	if( is_dir($job) && ($year==$interested_year) && !(strpos($filename,"gmmsb")!==false) && !(strpos($filename,"GMMSB")!==false) && !(strpos($filename,"gmsb")!==false)){
		echo "passei no if";
		$properties = json_decode(file_get_contents("$job/properties.json"),true);
    		$emails = $properties[$filename]['email'];
		var_dump($emails);		
    		foreach( $emails as $email ){
			if (!in_array($email,$unique_emails)){
				$unique_emails []=$email;
			}
		}
	}
}

echo "\n\n#### Result: ".count($unique_emails)."\n";
echo "FIM\n"

?>
