<?php
function getUserIp() {
	if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) // se possível, obtém o endereço ip da máquina do cliente
{
		$ip = $_SERVER ['HTTP_CLIENT_IP'];
	} elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) // verifica se o ip está passando pelo proxy
{
		$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER ['REMOTE_ADDR'];
	}
	return $ip;
}

$ip = getUserIp ();
$date = date ( 'H-i-s, j-m-y' );
//$ip and $date devem ser guardados no banco de dados

// Download
header ( "Location: tutorials/Basic_tutorial_DockThor_1.0_6.pdf" ); // novo tutorial 28/04/2014
?>

