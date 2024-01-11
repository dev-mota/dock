<?php
$mmffligand_conf_array = parse_ini_file ( dirname ( __FILE__ ) . "/../conf/mmffligand.ini" );
$GLOBALS ["BABEL_LIBDIR"] = $mmffligand_conf_array ['BABEL_LIBDIR'];
$GLOBALS ["BABEL_DATADIR"] = $mmffligand_conf_array ['BABEL_DATADIR'];
$GLOBALS ["LD_LIBRARY_PATH"] = $mmffligand_conf_array ['LD_LIBRARY_PATH'];
$GLOBALS ["PYTHONPATH"] = $mmffligand_conf_array ['PYTHONPATH'];
$GLOBALS ["MMFFLIGAND_BIN"] = $mmffligand_conf_array ['MMFFLIGAND_BIN'];
$GLOBALS ["BABEL_BIN"] = $mmffligand_conf_array ['BABEL_BIN'];
?>
