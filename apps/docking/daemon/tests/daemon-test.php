<?php
/*
require_once (__DIR__."/../config/globals-daemon.php");
require_once (__DIR__."/../../lib/sinapad-rest/rest-php-adapter.php");

// Production:  http://rest.sinapad.lncc.br:8080/rest
// Fogbow:      http://146.134.227.113:8080/sinapad-rest/
$CONFIG_REST_ROOT_URL = "http://146.134.227.113:8080/sinapad-rest"; 

$CONFIG_REST_USER = "DockThor";

// CSGrid or Fogbow
$CONFIG_REST_SERVICE = "CSGrid"; 

$CONFIG_REST_CERTIFICATE = "/home/dockthor/DockThor.key";

echo "\n# Login \n";
$uuid = rest_login();
var_dump($uuid);

echo "\n# Get resources \n";
$resourcesResult = rest_get_resources($uuid);
var_dump($resourcesResult);
*/

/*
How to test:
sinapad@imartin:~$ /home/sinapad/phpunit /media/sf_eclipse-workspace/DockThor-3.0/apps/docking/daemon/tests/daemon-test.php
*/

require_once (__DIR__."/../config/globals-daemon.php");
require_once (__DIR__."/../../lib/sinapad-rest/rest-php-adapter.php");

use PHPUnit\Framework\TestCase;

class DaemonTest extends TestCase
{
    
    private $jobId;
    
    public function __construct() {
        
        // Production:  http://rest.sinapad.lncc.br:8080/rest
        // Fogbow:      http://146.134.227.113:8080/sinapad-rest/
        $CONFIG_REST_ROOT_URL = "http://146.134.227.113:8080/sinapad-rest"; 
        
        // Portal user
        $CONFIG_REST_USER = "DockThor";
        
        // CSGrid or Fogbow
        $CONFIG_REST_SERVICE = "CSGrid"; 
        
        // Certificate
        $CONFIG_REST_CERTIFICATE = "/home/dockthor/DockThor.key";
        
        // Job id
        $this->jobCSGridId = "Dock@Dock.CBOMFBOB2I";
        
        // Job test folder
        $this->jobRootTestFolder = "gmmsb_tests4fogbow";
        
        // Job test file
        $this->jobTestFile = "test.txt";
        
    }
    
    public function testLogin(){
        echo "\n# Login \n";
        $uuid = rest_login();
        var_dump($uuid);
        $this->assertNotNull($uuid);
        return $uuid;
    }
    
    /**
     * @depends testLogin
     */
    public function testGetResources($uuid){
        echo "\n# Get resources \n";
        $resourcesResult = rest_get_resources($uuid);        
        var_dump($resourcesResult);
        $this->assertTrue($resourcesResult->code=="200");
    }
    
    /**
     * @depends testLogin
     */
    public function testLogout($uuid){
        echo "\n# Logout \n";
        $result = rest_logout($uuid);
        var_dump($result);
        $this->assertTrue($result=="200");
    }    

    /*
    public function testDownloadLog(){
        echo "\n# Download log (automatic login)\n";
        $result = rest_download_log($this->jobCSGridId, "/tmp/job.log", null);
        var_dump($result);
        $this->assertTrue($result=="200");
    }
    */
    

}
?>
