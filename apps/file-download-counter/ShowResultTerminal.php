<?php

require_once 'lib/DownloadCounter.php';

$downloadCounter = new DownloadCounter();
$result = $downloadCounter->showDownloadCount();
print_r($result);