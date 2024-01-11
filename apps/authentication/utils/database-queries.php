<?php
class DatabaseQueries {
	
	private $conn; 
	
	function __construct (){
	    	$mysql_server = "mysql.sinapad.lncc.br";
		$mysql_db = "dockthor";
		$mysql_user = "dockthor";
        	$mysql_password = "p23Uo@1W";
		
// 		/mysqli_report(MYSQLI_REPORT_ALL);
		$this->conn = new mysqli($mysql_server, $mysql_user, $mysql_password, $mysql_db);
		$this->conn->set_charset("utf8");
		
		if ($this->conn->connect_error) {
			//die("Connection failed: " . $conn->connect_error);
		}
	}

	public function checkConnection(){
		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			return false;
		} else {
			printf ("System status: %s\n", $this->conn->stat() );
			return true;
		}
	}

	public function updateUsername($username, $userEmail){
		$updateSQL = "UPDATE `user` SET ldap_username = ? WHERE email = ?;";
		$stmtUpdate = $this->conn->prepare ( $updateSQL );
		$stmtUpdate->bind_param ( "ss", $username, $userEmail);
		$stmtUpdate->execute ();
		return true;
	}
	
	
	public function authenticateUser($email){
		session_start();
		
		$sql = "SELECT email FROM `user` WHERE email = ?;";
		$stmt = $this->conn->prepare ( $sql );
		$stmt->bind_param ( "s", $email);
		$stmt->execute ();
		$stmt->store_result ();
		$stmt->bind_result($user);
		while ( $stmt->fetch () ) {
			$insert_sql = "INSERT INTO auth_session (email, `session`) VALUES (?, ?);";
			$stmtInsert = $this->conn->prepare ( $insert_sql );
			$stmtInsert->bind_param ( "ss", $email, session_id());
			$stmtInsert->execute ();
			return true;
		}
		return false;
	}
	
	public function logoutUser() {
		session_start();
		$sql = "SELECT id FROM auth_session WHERE `session` = ?;";
		$stmt = $this->conn->prepare ( $sql );
		$stmt->bind_param ( "s", session_id());
		$stmt->execute ();
		$stmt->store_result ();
		$stmt->bind_result($id);
		while ( $stmt->fetch () ) {
			$sqlDelete = "DELETE FROM auth_session WHERE id = ?;";
			$stmtDelete = $this->conn->prepare ( $sqlDelete );
			$stmtDelete->bind_param ( "s", $id);
			$stmtDelete->execute ();
		}
	}
	
	public function checkIfSessionIsValid($session){
		$sql = "SELECT u.ldap_username FROM auth_session a INNER JOIN `user` u ON u.email = a.email WHERE `session` = ?;";
		$stmt = $this->conn->prepare ( $sql );
		$stmt->bind_param ( "s", $session);
		$stmt->execute ();
		$stmt->store_result ();
		$stmt->bind_result($user);
		while ( $stmt->fetch () ) {
			return $user;
		}
		return false;
	}
}

?>
