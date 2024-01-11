<?php
$datasets_conf_array = parse_ini_file ( dirname ( __FILE__ ) . "/../conf/datasets.ini" );
$GLOBALS ["DATASETS_DIR"] = $datasets_conf_array ['DATASETS_DIR'];
?>
