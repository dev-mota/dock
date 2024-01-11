<script type="text/javascript" src="js/admin-logout.js"></script>

<div ng-controller="AdminLogoutController">
<div class="page-container" align="center">
	<div class="row">
		<h1>Logout?</h1>
	</div>
	<div class="row">
		<button type="button" class="btn btn-default dockthor-button" ng-click="logout()">Yes</button>
		<button type="button" class="btn btn-default dockthor-button" ng-click="goToHomePage()">No</button>
	</div>
</div>	
</div>
<!-- <a href="" ng-click="selectedTab='WELCOME'">Logout</a>
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel">-->