<?php
require_once ("utils/database-queries.php");

$databaseQueries = new DatabaseQueries ();
$databaseQueries->logoutUser();
header ( "Location: ../../index.php" );
?>