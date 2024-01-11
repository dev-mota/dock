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

if (isset ( $_GET ["type"] ) && isset ( $_GET ["fileName"] )) {
	
	session_start ();
	$session_id = session_id ();
	
	$ip = getUserIp ();
	$date = date ( 'H-i-s, j-m-y' );
	// $ip and $date devem ser guardados no banco de dados
	
	// Download
	
	$file_name = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $_GET ["fileName"] ); // retirando extensão
	switch ($_GET ["type"]) {
		case 'zip' :
			header ( "Location: ../session-files/$session_id/PROTEIN/$file_name.zip" );
			break;
		case 'prep' :
			header ( "Location: ../session-files/$session_id/PROTEIN/$file_name" . "_prep.pdb" );
			break;
		case 'in' :
			$file = "../session-files/$session_id/PROTEIN/$file_name.in";
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$file_name.in");
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: binary");
			
			// read the file from disk
			readfile($file);
			break;
		case 'pdb' :
			header ( "Location: ../session-files/$session_id/PROTEIN/$file_name.pdb" );
			break;
		default :
			break;
	}
}
?>

