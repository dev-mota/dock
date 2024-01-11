<?php

require __DIR__.'/../../../lib/SimpleConnectDb.php';

class DownloadCounter{

    private $connection = null;
    
    public function __construct(){
        $instance = SimpleConnectDb::getInstance();
        $this->connection = $instance->getConnection();        
    }

    public function showDownloadCount(){

        $sql = "
            SELECT * FROM file_download;
        ";

        $data = array();
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {            
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } 

        $this->connection->close();
        return $data;
    }

    public function registerDownload($filePath){
        
        $sql = "
            INSERT INTO file_download (file_path) VALUES (?) ON DUPLICATE KEY UPDATE download_count=download_count+1;
        ";
        
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bind_param( "s", $filePath);
		$stmt->execute();
		$stmt->store_result ();

        $stmt->close();
        $this->connection->close();

        return true;
    }

}