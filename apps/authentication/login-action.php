<?php 
require_once ("utils/database-queries.php");

if ((isset ( $_POST ['inputEmail'] ) && $_POST ['inputEmail'] != "") && (isset ( $_POST ['inputPassword'] ) && $_POST ['inputPassword'] != "")) {
	$error_msg = null;
	
	$adminUsername = "dockthor";
	$adminPass = "bdcf36x754uhz";
	
	$userEmail = $_POST ['inputEmail'];
	$userPassword = $_POST ['inputPassword'];
	
	$ldap_server = "ldap.sinapad.lncc.br";
	$dominio = "dc=sinapad,dc=lncc,dc=br"; //Dominio local ou global
	$userAdmin = "cn=$adminUsername,$dominio";
// 	$user = "mail=$username,$dominio";
	$ldap_porta = "389";
	$ldap_pass   = "$adminPass";
	$ldapconAdmin = ldap_connect($ldap_server, $ldap_porta) or die("Could not connect to LDAP server.");
	if ($ldapconAdmin){
		// binding to ldap server
		//$ldapbind = ldap_bind($ldapconn, $user, $ldap_pass);
		$bind = ldap_bind($ldapconAdmin, $userAdmin, $ldap_pass);
		// verify binding
		if ($bind) {
			$result = ldap_search($ldapconAdmin,$dominio, "(mail=$userEmail)") or die ("Error in search query: ".ldap_error($ldapconAdmin));
			$data = ldap_get_entries($ldapconAdmin, $result);
			
			if($data['count'] == 1){
				ldap_close($ldapconAdmin);
				
				$username = $data[0]['cn'][0];				
				$ldapcon = ldap_connect($ldap_server, $ldap_porta) or die("Could not connect to LDAP server.");
				
				$user = "cn=$username,$dominio";
				$bind = ldap_bind($ldapcon, $user, $userPassword);
				if ($bind) {
					$databaseQueries = new DatabaseQueries();
					$databaseQueries->updateUsername($username, $userEmail);
					$databaseQueries->authenticateUser($userEmail);
				} else{
					$error_msg = "Invalid password. Please try again.";
				}
			} else {
				$error_msg = "Username not found. Please try again or contact us at dockthor@lncc.br";
			}
		} else {
			$error_msg = "Authentication error. Please try again later.";
		}
	}
	if($error_msg != null){
		header ( "Location: ../../index.php?tab=LOGIN&loginError='$error_msg'" );
	} else {
		header ( "Location: ../../index.php" );
	}
} else {
	header ( "Location: ../../index.php?tab=LOGIN&loginError='User or password invalid!'" );
}

?>
