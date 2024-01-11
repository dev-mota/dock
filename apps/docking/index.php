<script type="text/javascript" src="apps/docking/js/index.js"></script>
<div ng-controller="DockThorController">
	<!-- Static navbar -->
	<br>
	<nav>
		<div class="container-fluid docking-page-container" >
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed"
					data-toggle="collapse" data-target="#navbar" aria-expanded="false"
					aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
			</div>
			<div>
				<ul class="nav nav-pills nav-tabs nav-justified dockthor-pills bar-text">
					<li ng-class="{'active' : selectedTab=='PROTEIN'}"><a class="dockthor-navbar-text" href=""
						ng-click="selectedTab='PROTEIN'">Protein</a></li>
					<li ng-class="{'active' : selectedTab=='COFACTORS'}"><a class="dockthor-navbar-text" href=""
						ng-click="selectedTab='COFACTORS'">Cofactors</a></li>
					<li ng-class="{'active' : selectedTab=='LIGAND'}"><a class="dockthor-navbar-text" href=""
						ng-click="selectedTab='LIGAND'">Ligand</a></li>
					<li ng-class="{'active' : selectedTab=='DOCKING'}" ng-show="proteinInput != null && ligandInput != null && !emptyLigand && !emptyProtein"><a class="dockthor-navbar-text" href=""
						ng-click="selectedTab='DOCKING'">Docking</a></li>
					<li ng-class="{'disabled' : true}" ng-show="proteinInput == null || ligandInput == null || emptyLigand || emptyProtein"><a class="dockthor-navbar-text" href="">Docking</a></li>
<!-- 					<li ng-class="{'active' : selectedTab=='DOCKING'}"><a class="dockthor-navbar-text" href="" -->
<!-- 						ng-click="selectedTab='DOCKING'">Docking</a></li> -->
					
					<li ng-class="{'active' : selectedTab=='RESULTS'}" ng-show="$parent.job.id != null"><a class="dockthor-navbar-text" href=""
						ng-click="selectedTab='RESULTS'">Results</a></li>
					<li ng-class="{'disabled' : true}" ng-hide="$parent.job.id != null"><a class="dockthor-navbar-text" href="">Results</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
		<!--/.container-fluid -->
	</nav>

	<div ng-init="selectedTab=(job.id != null ? 'RESULTS' : 'PROTEIN')">
		<span ng-show="selectedTab=='PROTEIN'"> <?php include ("apps/docking/pages/protein.php"); ?> </span>
		<span ng-show="selectedTab=='LIGAND'"> <?php include ("apps/docking/pages/ligand.php"); ?> </span>
		<span ng-show="selectedTab=='COFACTORS'"> <?php include ("apps/docking/pages/cofactors.php"); ?> </span>
		<span ng-show="selectedTab=='DOCKING'"> <?php include ("apps/docking/pages/docking.php"); ?> </span>
		<span ng-show="selectedTab=='RESULTS'"> <?php include ("apps/docking/pages/results.php"); ?> </span>
	</div>	
	
</div>
