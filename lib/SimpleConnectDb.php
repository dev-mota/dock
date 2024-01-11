<?php

require __DIR__.'/../conf/database_parameters.php';

class SimpleConnectDb {

    private static $instance = null;
    private $connection;
    
    private function __construct()
    {
        global $dbHost, $dbUser, $dbPassword, $dbName, $dbCharset;
        $this->connection = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
        $this->connection->set_charset($dbCharset);
    }
    
    public static function getInstance()
    {
        if(!self::$instance){
            self::$instance = new SimpleConnectDb();
        }        
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->connection;
    }    
    
}