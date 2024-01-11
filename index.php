<?php 
require_once ("apps/authentication/utils/database-queries.php");

// Enable/disable admin page controll
session_start ();
$enabledAdmin = false;
if (isset ( $_SESSION ['dockthor_admin_login'] )) {
	$enabledAdmin = true;
}

// ID da sessão
// $session_id = session_id ();

// Application controll
// $job_id = null;
// if (isset ( $_GET ['jobId'] )) {
// 	$job_id = $_GET ['jobId'];
// }

$databaseQueries = new DatabaseQueries();
$_SESSION['VALID_SESSION_USER'] = $databaseQueries->checkIfSessionIsValid(session_id());

$tab = isset ( $_GET ['tab'] ) ? $_GET ['tab']: null;
switch ($tab){
	case 'LOGIN' :
		$login_error = isset ( $_GET ['loginError'] ) ? $_GET ['loginError']: null;
		$page = isset ( $_GET ['page'] ) ? $_GET ['page']: "WELCOME";
		break;
	case 'DOCKING' :
		$page = isset ( $_GET ['page'] ) ? $_GET ['page']: "PROTEIN";
		switch ($page){
			case 'RESULTS':
				$job_id = isset ( $_GET ['jobId'] ) ? $_GET ['jobId']: null;
				break;
		}
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
		
		<title>DockThor</title>
		
		<link rel="icon" href="images/logo_dockthor.png">
		<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
		<link rel="stylesheet" href="utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="css/carousel.css">
		<link rel="stylesheet" href="css/jquery.fileupload.css">
		<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
		<link rel="stylesheet" href="css/style.css">		
		<script src="js/external//jquery-3.1.1.min.js"></script>
		<script src="utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<!-- <script src="https://use.fontawesome.com/b8aca93edd.js"></script> -->
		<script type="text/javascript" src="js/external/fontawesome/b8aca93edd.js"></script>
		<!-- <script src="utils/font-awesome-4.7.0/css/font-awesome.min.css"></script> -->
		<script type="text/javascript" src="js/external/angular.min.js"></script>
		<script type="text/javascript" src="js/external/angular-sanitize.js"></script>
		<script src='https://www.google.com/recaptcha/api.js?hl=en'></script>
		<script type="text/javascript" src="js/external/angular-recaptcha.min.js"></script>
		<script type="text/javascript" src="js/external/ng-file-upload-shim.min.js"></script>	
		<script type="text/javascript" src="js/external/ng-file-upload.min.js"></script>
		<script type="text/javascript" src="js/external/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="js/external/jquery.fileupload.js"></script>
		<script type="text/javascript" src="js/external/jquery.fileupload-process.js"></script>	
		<script type="text/javascript" src="js/external/jquery.fileupload-angular.js"></script>
		<!-- <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet"> -->
		<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
		<script type="text/javascript" src="js/index.js"></script>		
	</head>
	
	<body ng-app="dockthorApp">
		<?php include_once("analyticstracking.php") ?>
		<div ng-cloak class="wrapper" ng-controller="MainController "
			ng-init="selectedTab='<?php echo ($tab != null ? "$tab" : "WELCOME")?>';  job.id=<?php echo (isset($job_id) ? "'$job_id'" : "null")?>; loginError=<?php echo (isset($login_error) ? "$login_error" : "null")?>">
			<?php include ("fragments/header.php");?>
			<span ng-show="selectedTab=='WELCOME'"> <?php include ("fragments/home.php"); ?> </span>
			<span ng-show="selectedTab=='DOCKING'"> <?php include ("apps/docking/index.php"); ?> </span>
			<span ng-show="selectedTab=='REFERENCES'"><?php include ("fragments/references.php"); ?></span>
			<span ng-show="selectedTab=='ABOUT'"><?php include ("fragments/about.php"); ?></span>
			<span ng-show="selectedTab=='HELP'"><?php include ("fragments/help.php"); ?></span>
			<span ng-show="selectedTab=='CONTACT'"><?php include ("fragments/contact.php"); ?></span>
			<span ng-show="selectedTab=='FEATURES'"><?php include ("fragments/new_features.php"); ?></span>
			<!-- <span ng-show="selectedTab=='FAQ'"><?php //include ("fragments/faq.php"); ?></span>-->
			<span ng-show="selectedTab=='RELEASE-NOTES'"><?php include ("fragments/release-notes.php"); ?></span>
			
			<?php if(!$_SESSION['VALID_SESSION_USER']) {?>
				<span ng-show="selectedTab=='LOGIN'"><?php include ("apps/authentication/index.php"); ?></span>
			<?php }?>
			
			<?php if ($enabledAdmin){?>
				<span ng-show="selectedTab=='MONITORING'"><?php 
				   // include ("apps/jobs-monitoring/index.php"); // version 01
				   include ("apps/jobs-monitoring/jobsMonitoringView.php"); // version 02
				 ?></span>
				<span ng-show="selectedTab=='NEWSFEED'"><?php include ("apps/newsfeed/index.php"); ?></span>
				<span ng-show="selectedTab=='REG-EMAILS'"><?php include ("apps/registered-emails/index.php"); ?></span>
				<span ng-show="selectedTab=='ADMLOGOUT'"><?php include ("admin/logout.php"); ?></span>
				<span ng-show="selectedTab=='REG-PROJ'"><?php include ("apps/authentication/reg_projects.php"); ?></span>
			<?php }?>
			
			<?php include ("fragments/footer.php"); ?>
			<?php include ("fragments/terms-of-use.php");?>
			<?php include ("fragments/binding_site.php");?>
			
		</div>
		<div class="copyright" align="center">
			<p>Version 2.0 . Copyright © GMMSB 2019. All Rights Reserved.</p>
		</div>
		
	</body>
	
	<script type="text/javascript" src="apps/docking/prepared-files-app/controller/controller.js"></script>
	
</html>
