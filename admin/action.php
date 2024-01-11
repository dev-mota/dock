<?php 
// session_start inicia a sessão
session_start();

// as variáveis login e senha recebem os dados digitados na página anterior
$login = $_POST['login'];
$pass = $_POST['pass'];

if($login == "dockthor" && $pass == "P3xwh@c2") {
	if(isset($_SESSION['dockthor_admin_login_failed'])){
		unset ($_SESSION['dockthor_admin_login_failed']);
	}
	$_SESSION['dockthor_admin_login'] = $login;
	header('location:../index.php');
}else{
	$_SESSION['dockthor_admin_login_failed'] = true;
	unset ($_SESSION['dockthor_admin_login']);	
	header('location:index.php');
}

?>
