<?php

$pathJobs = "/var/www.new/dockthorV2/apps/docking/daemon/jobs";

$allJobDirs = preg_grep ( '/^([^.])/', scandir ( $pathJobs ) ); // exclusão de "." e ".."

$allJobDirs = array_values ( $allJobDirs ); // reordena inidice para começar de 0 mesmo após a exclusão de "." e ".."
$totalJobs = count($allJobDirs);

echo "totalJobs: $totalJobs\n";

$jobs = array();
foreach ( $allJobDirs as $portalId ) {
    
    if(file_exists("$pathJobs/$portalId/properties.json")){
        $jobProperties = json_decode ( file_get_contents ( "$pathJobs/$portalId/properties.json" ), true );
    }
    $jobs[$portalId] = $jobProperties;
    
}

$jobsCount = count($jobs);
echo "jobsCount: $jobsCount\n";

usort($jobs, function($a, $b) {
    $t1 = strtotime($b[key($b)]['portal-submission-date']);
    $t2 = strtotime($a[key($a)]['portal-submission-date']);
    return $t1 - $t2;
});
    
    
    
    
    ?>