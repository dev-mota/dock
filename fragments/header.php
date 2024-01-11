<?php
// Enable/disable admin page controll
if (! isset ( $_SESSION )) {
	session_start ();
}
$enabledAdmin = false;
if (isset ( $_SESSION ['dockthor_admin_login'] )) {
	$enabledAdmin = true;
}
?>

<div class="dockthor-header">
	<div class="header-logo">
		<div class="col-md-3">
			<!-- Dockthor Logo -->
			<br>
			<div>
				<a href="#" ng-click="goToHomePage()"> 
					<img width="250px" src="./images/header/logo_teste.png">
					<img class="slogan" src="./images/header/slogan.png">
				</a>
			</div>
			<!-- logo3 -->
		</div>
		<!--     <div class="image-responsive col-md-3 pull-right" style="background: linear-gradient(to left right, #5bc0de, #040404);">
      <img src="./images/header/img_right.png"> -->
		<!--     </div> -->
	</div>
</div>

<!-- <nav class="navbar navbar-default dockthor-navbar"> -->
<!-- 	<div class="container-fluid"> -->
<!-- 		<div class="navbar-header"> -->
<!-- 			<button type="button" class="navbar-toggle collapsed" -->
<!-- 				data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" -->
<!-- 				aria-expanded="false"> -->
<!-- 				<span class="sr-only">Toggle navigation</span> <span -->
<!-- 					class="icon-bar"></span> <span class="icon-bar"></span> <span -->
<!-- 					class="icon-bar"></span> -->
<!-- 			</button> -->
<!-- 		</div> -->
		<ul class="nav nav-pills dockthor-navbar bar-text">
			<li ng-class="{'active' : selectedTab=='WELCOME'}"><a
				class="dockthor-navbar-text" href=""
				ng-click="selectedTab='WELCOME'">Home</a></li>
			<li ng-class="{'active' : selectedTab=='DOCKING'}"><a
				class="dockthor-navbar-text" href=""
				ng-click="selectedTab='DOCKING'">Docking</a></li>
			<li ng-class="{'active' : selectedTab=='REFERENCES'}"><a
				class="dockthor-navbar-text" href=""
				ng-click="selectedTab='REFERENCES'">References</a></li>
			<li ng-class="{'active' : selectedTab=='ABOUT'}"><a
				class="dockthor-navbar-text" href="" ng-click="selectedTab='ABOUT'">About</a>
			</li>
			<li	class="dropdown" ng-class="{'active' : (selectedTab=='HELP' || selectedTab=='CONTACT')}">
			<a href="#" class="dropdown-toggle dockthor-navbar-text" data-toggle="dropdown"
				role="button" aria-haspopup="true" aria-expanded="false" style="white-space:nowrap;" > <span style="margin-right: 6%; float: none;">Support</span> <small><span
					class="glyphicon glyphicon-menu-down" aria-hidden="true"></span></small>
			</a>
				<ul class="dropdown-menu" style="background-color: #1f9797;">
					<li ng-class="{'active' : selectedTab =='FEATURES'}"><a style="color: white;" href="" ng-click="selectedTab='FEATURES'">New Features</a></li>
					<li ng-class="{'active' : selectedTab =='HELP'}"><a style="color: white;" href="" ng-click="selectedTab='HELP'">Help</a></li>
					<li ng-class="{'active' : selectedTab =='CONTACT'}"><a style="color: white;" href="" ng-click="selectedTab='CONTACT'">Contact</a></li>
					<!-- <li ng-class="{'active' : selectedTab =='FAQ'}"><a style="color: white;" href="" ng-click="selectedTab='FAQ'">FAQ</a></li>-->
					<!-- <li ng-class="{'active' : selectedTab =='RELEASE-NOTES'}"><a style="color: white;"  href="" ng-click="selectedTab='RELEASE-NOTES'">Release Notes</a></li> -->
				</ul>
			</li>  
  <?php
		if ($enabledAdmin) {
			?>
            <li ng-show="true" class="dropdown"><a href="#"
				class="dropdown-toggle dockthor-navbar-text btn btn-warning"
				data-toggle="dropdown" role="button" aria-haspopup="true"
				aria-expanded="false"> Admin <span class="caret"></span>
			</a>
				<ul class="dropdown-menu">
					<li ng-class="{'active' : selectedTab=='MONITORING'}">
						<!-- TODO: pegar dinamico os app administrativos de arquivo de configuracao  -->
						<a href="" ng-click="selectedTab='MONITORING'">Jobs Monitor</a>
					</li>
					<li ng-class="{'active' : selectedTab=='NEWSFEED'}">
						<!-- TODO: pegar dinamico os app administrativos de arquivo de configuracao  -->
						<a href="" ng-click="selectedTab='NEWSFEED'">Newsfeed</a>
					</li>
					<li ng-class="{'active' : selectedTab=='REG-EMAILS'}">
						<!-- TODO: pegar dinamico os app administrativos de arquivo de configuracao  -->
						<a href="" ng-click="selectedTab='REG-EMAILS'">Newsfeed Users</a>
					</li>
					<li ng-class="{'active' : selectedTab=='REG-USERS'}">
						<!-- TODO: pegar dinamico os app administrativos de arquivo de configuracao  -->
						<a href="" ng-click="selectedTab='REG-PROJ'">Projects</a>
					</li>					
					<li ng-class="{'active' : selectedTab=='ADMLOGOUT'}">
						<!-- TODO: pegar dinamico os app administrativos de arquivo de configuracao  -->
						<a href="" ng-click="selectedTab='ADMLOGOUT'">Logout</a>
					</li>
				</ul></li>
  <?php }?>
			<?php if(!$_SESSION['VALID_SESSION_USER']) {?>
			
			<li class="nav-login-li" ng-class="{'active' : selectedTab=='LOGIN'}"><a
				class="dockthor-navbar-text" href="" ng-click="selectedTab='LOGIN'"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Login | Register</a>
			</li>
			
			<?php } else {?>
			<li class="nav-login-li"><a
				class="dockthor-navbar-text" href="apps/authentication/logout-action.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a>
			</li>
			<li class="nav-login-li"><a
				class="dockthor-navbar-text" href="">Welcome <?php echo $_SESSION['VALID_SESSION_USER']?>!</a>
			</li>
			<?php }?>
          </ul>
<!-- 	</div> -->
<!-- </nav> -->
