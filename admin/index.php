<?php
// for dinamic src include
$projectName = explode('/', $_SERVER['REQUEST_URI'])[1]; // ex.: DockThor-3.0

// login success controll
session_start();
$loginFailed = false;
if(isset($_SESSION['dockthor_admin_login_failed']) and $_SESSION['dockthor_admin_login_failed']==true ){
	unset ($_SESSION['dockthor_admin_login_failed']);
	$loginFailed = true;	
}

?>
<!DOCTYPE html>
<html>

	<head>
		<meta charset="UTF-8">
		<title>DockThor Admin</title>
		
		<!-- Angular (include page, ng-cloak)-->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.8/angular.min.js"></script>
		
		<!-- Bootstrap (only design)-->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
		<!-- css -->
		<link rel="stylesheet" href="/<?php echo $projectName?>/css/style.css">
	</head>

	<body ng-app="">
		<div ng-cloak class="wrapper" class="container">
			<div ng-include="'/<?php echo $projectName?>/fragments/header-simple.php'"></div>
			<br><br><br>
			<div class="container">
				<form method="post" action="action.php" id="formlogin" name="formlogin" >
					<!-- bootstrap controll (fieldsets, field size(col-xs-4)) -->
					<div class="form-group col-xs-3">
						<fieldset id="fie">
							<label>Login</label>
							<input class="form-control" type="text" name="login" id="login"  /><br />
							<label>Password</label>
							<input class="form-control" type="password" name="pass" id="pass" /><br />
							<!-- bootstrap controll (fieldset, button) -->
							<input class="form-control btn btn-primary" type="submit" value="Login"  />
<?php if($loginFailed){?>
							<div class="alert alert-warning">
							  <strong>Warning!</strong> Username or password is incorrect.
							</div>
<?php } ?>
						</fieldset>
					</div>
				</form>
			</div>

		</div>
	</body>

</html>
