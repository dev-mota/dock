<?php
class DatabaseQueries {
	
	private $conn; 
	
	function __construct (){
	    	$mysql_server = "";
		$mysql_db = "";
		$mysql_user = "";
		$mysql_password = "";
		
// 		/mysqli_report(MYSQLI_REPORT_ALL);
		$this->conn = new mysqli($mysql_server, $mysql_user, $mysql_password, $mysql_db);
		$this->conn->set_charset("utf8");
		
		if ($this->conn->connect_error) {
			//die("Connection failed: " . $conn->connect_error);
		}
	}
	
	public function subscribeUserToNews($email){
		$sql = "SELECT id FROM email_subscription WHERE email = ?;";
		$stmt = $this->conn->prepare ( $sql );
		$stmt->bind_param ( "s", $email);
		$stmt->execute ();
		$stmt->store_result ();
		$stmt->bind_result($id);
		while ( $stmt->fetch () ) {
			return true;
		}
		
		$sqlInsert = "INSERT INTO email_subscription (email) VALUES (?);";
		$stmtInsert = $this->conn->prepare ( $sqlInsert );
		$stmtInsert->bind_param ( "s", $email);
		return $stmtInsert->execute ();
	}
}

?>
