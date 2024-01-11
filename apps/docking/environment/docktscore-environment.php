<?php

openlog("[dockthor-log][docktscore-environment.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$conf_array = parse_ini_file ( dirname ( __FILE__ ) . "/../conf/docktscore.ini" );

$GLOBALS ["DOCKTSCORE_SCRIPT"] = $conf_array ['DOCKTSCORE_SCRIPT'];
$GLOBALS ["BUILD_BEST_CSV_SCRIPT"] = $conf_array ['BUILD_BEST_CSV_SCRIPT'];
$GLOBALS ["BUILD_SPLIT_MOL2_PY"] = $conf_array ['BUILD_SPLIT_MOL2_PY'];
$GLOBALS ["BUILD_BEST_SNAP_SCRIPT"] = $conf_array ['BUILD_BEST_SNAP_SCRIPT'];
$GLOBALS ["BUILD_BEST_MOL2_SCRIPT"] = $conf_array ['BUILD_BEST_MOL2_SCRIPT'];
?>
