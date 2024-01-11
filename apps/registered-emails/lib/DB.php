<?php
/*
 * DB Class
 * This class is used for database related (connect, get, insert, update, and delete) operations
 * with PHP Data Objects (PDO)
 * @author    CodexWorld.com
 * @url       http://www.codexworld.com
 * @license   http://www.codexworld.com/license
 */
class DB {
    // Database credentials
    //private $dbHost     = 'jacurutu.sinapad.lncc.br';
    //private $dbHost     = '146.134.100.26';
    private $dbHost = "mysql.sinapad.lncc.br";
    //private $dbHost = "chimango.sinapad.lncc.br";
    //private $dbUsername = 'root'; // TODO: criar usuario dockthor no mysql 
    //private $dbPassword = 's30@sql';
    private $dbUsername = 'dockthor'; // TODO: criar usuario dockthor no mysql 
    private $dbPassword = 'B@g40ng7f';
    private $dbName     = 'dockthor';
    public $db;
    
    /*
     * Connect to the database and return db connecction
     */
    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            try{
                $conn = new PDO("mysql:host=".$this->dbHost.";dbname=".$this->dbName, $this->dbUsername, $this->dbPassword);
                $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db = $conn;
            }catch(PDOException $e){
		error_log("Failed to connect with MySQL: ". $e);
		// die("Failed to connect with MySQL: " . $e->getMessage());
		die();
            }
        }
    }
	
	/*
     * Insert data into the database
     * @param string name of the table
     * @param array the data for inserting into the table
     */
    public function insert($table,$data){
        if(!empty($data) && is_array($data)){
            $columns = '';
            $values  = '';
            $i = 0;
//             if(!array_key_exists('created',$data)){
//                 $data['date'] = date("Y-m-d H:i:s");
//             }
//             if(!array_key_exists('modified',$data)){
//                 $data['modified'] = date("Y-m-d H:i:s");
//             }

            $columnString = implode(',', array_keys($data));
            $valueString = ":".implode(',:', array_keys($data));
            $sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
            $query = $this->db->prepare($sql);
            foreach($data as $key=>$val){
                $val = htmlspecialchars(strip_tags($val));
                $query->bindValue(':'.$key, $val);
            }
            $insert = $query->execute();
            if($insert){
                $data['id'] = $this->db->lastInsertId();
                return $data;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */
    public function update($table,$data,$conditions){
        if(!empty($data) && is_array($data)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;

            foreach($data as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $val = htmlspecialchars(strip_tags($val));
                $colvalSet .= $pre.$key."='".$val."'";
                $i++;
            }
            if(!empty($conditions)&& is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$value."'";
                    $i++;
                }
            }
            $sql = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
            //error_log("Update SQL: ".$sql, 0);
            $query = $this->db->prepare($sql);
            $update = $query->execute();
            return $update?$query->rowCount():false;
            //$data['id'] = $conditions['id'];
			//error_log("db!");
			//error_log('db - '.print_r($data));
            //return $data;
        }else{
            return false;
        }
    }
    
    /*
     * Delete data from the database
     * @param string name of the table
     * @param array where condition on deleting data
     */
    public function delete($table,$conditions){
        $whereSql = '';
        if(!empty($conditions)&& is_array($conditions)){
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $whereSql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }
        $sql = "DELETE FROM ".$table.$whereSql;
        $delete = $this->db->exec($sql);
        return $delete?$delete:false;
    }
	
	/*
     * If success - returns an array of rows
	 * If fail - return false
     * @param sql string
     */
    public function select($sql){		
        $query = $this->db->prepare($sql);
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);        
        return !empty($data)?$data:false;
    }
	
	/*
     * If success - returns number of rows updated
	 * If fail - return false
     * @param sql string
     */
    public function simpleUpdate($sql){		
        $query = $this->db->prepare($sql);
        $update = $query->execute();
        return $update?$query->rowCount():false;
    }
}
