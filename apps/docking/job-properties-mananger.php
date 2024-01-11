<?php 

class JobPropertiesMananger {
	
	private static $jobs_directory_path = "";
	
	private function __construct()
	{
		self::$jobs_directory_path = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/";
	}
	
	public static function getInstance() {
		static $instance = null;
		if ($instance === null) {
			$instance = new JobPropertiesMananger();
		}
		return $instance;
	}
	
	public function saveJobProperties($portal_id, array $job_properties)
	{
		return file_put_contents ( self::$jobs_directory_path . "/$portal_id/properties.json", json_encode ( $job_properties, JSON_PRETTY_PRINT ) );
	}

	public function getJobProperties($portal_id)
	{
		return json_decode ( file_get_contents ( self::$jobs_directory_path . "/$portal_id/properties.json" ), true );
	}	
	
	public function hasJobPropertyFile($portal_id){
	    return file_exists(self::$jobs_directory_path . "/$portal_id/properties.json");
	}

}

?>