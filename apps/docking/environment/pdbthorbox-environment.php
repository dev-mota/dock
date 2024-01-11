<?php
$mmffligand_conf_array = parse_ini_file ( dirname(__FILE__) . "/../conf/pdbthorbox.ini" );
$GLOBALS ["MMFF_PATH"] = $mmffligand_conf_array ['MMFF_PATH'];
$GLOBALS ["PDBTHORBOX_BIN"] = $mmffligand_conf_array ['PDBTHORBOX_BIN'];
?>