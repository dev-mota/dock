<?php
$conf_array = parse_ini_file ( dirname ( __FILE__ ) . "/../conf/dtstatistics.ini" );
$GLOBALS ["DTSTATISTISCS_LIBDIR"] = $conf_array ['DTSTATISTISCS_LIBDIR'];

?>
