<?php

$pathJobs = "/var/www.new/dockthorV2/apps/docking/daemon/jobs";

$allJobsDirs = preg_grep ( '/^([^.])/', scandir ( $pathJobs ) ); // exclusão de "." e ".."

// 5 primeiro itens
$jobs_dirs_spliced = array_slice($allJobsDirs,0,5);

var_dump($jobs_dirs_spliced);

//$jobs_dirs = array_values ( $jobs_dirs );
// var_dump($jobs_dirs);

//$jobs = array();
//foreach ( $jobs_dirs as $portal_id ) {
//        $job_properties = json_decode ( file_get_contents ( $pathJobs. "/$portal_id/properties.json" ), true );
//        $jobs[$portal_id] = $job_properties;
//}

// var_dump($jobs);

//$jobsJson = json_encode ( $jobs, JSON_PRETTY_PRINT );

// var_dump($jobsJson);


?>
