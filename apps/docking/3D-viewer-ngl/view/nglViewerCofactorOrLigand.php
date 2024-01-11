<!-- 

NGL Viewer for Ligands and Cofactors

 -->
<?php 

$structureType = $_GET ["structureType"]; // cofactor or ligand

?> 

<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>NGL Viewer</title>		
		<link rel="stylesheet" href="../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
		<script type="text/javascript" src="../../../../js/external/fontawesome/b8aca93edd.js"></script>
		<style>
		.scrollable-menu {
            height: auto;
            max-height: 200px;
            overflow-x: hidden;
        }
		[ng-cloak]
		{
		  display: none !important;
		}
		</style>
	</head>
	
	<body ng-cloak ng-app="nglViewerApp">		
        	
		<div ng-controller="nglControllerCofactorOrLigand" ng-init="init('<?= $structureType ?>')">
    		
  	  		<div class="container-fluid">
				
				<div ng-show="outputPaths==undefined" class="row" align="center">
					<br>
					<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px;"></i>
					<p>
						<i>loading ngl viewer...</i>
					</p>
				</div>
				
				<div class="row">
					
					<div ng-show="outputPaths!=undefined && outputPaths.length>0">
						
						<div align="center">
							<div ng-include="'fragment-note-controls.html'"></div>
							<br>
						</div>
						
						<ul class="list-inline">
							<li>
								<!-- Small empty column -->
							</li>
							<li>
								<label>Input file</label>
							</li>
							<li>
								<div class="dropdown">
									
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										{{outputPaths[selectedInputFileIndex].input}}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li ng-repeat="(folderKey, folderValue) in outputPaths | orderBy:'inputNoExt'"><a href="#" ng-click="loadNgl(folderKey, 0)">{{folderValue.inputNoExt}}</a></li>                                                                
									</ul>
								</div>
							</li>
							<li>
								<!-- Small empty column -->
							</li>
							<li>
								<label>Molecule</label>
							</li>
							<li>
								<div class="dropdown">
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										{{outputPaths[selectedInputFileIndex].paths[selectedFileIndex].nameNoExt}}           					
										<span class="caret"></span>
									</button>
									<!--  Sem scroll
									<ul class="dropdown-menu wrapper" aria-labelledby="dropdownMenu1">
										<li ng-repeat="(fileKey, fileValue) in outputPaths[selectedInputFileIndex].paths"><a href="#" ng-click="loadNgl(selectedInputFileIndex, fileKey)">{{fileValue.nameNoExt}}</a></li>                                                                
									</ul>
									-->
									<ul class="dropdown-menu scrollable-menu" role="menu">
										<li ng-repeat="(fileKey, fileValue) in outputPaths[selectedInputFileIndex].paths"><a href="#" ng-click="loadNgl(selectedInputFileIndex, fileKey)">{{fileValue.nameNoExt}}</a></li>
									</ul>
								</div>							
							</li>  	
						</ul>						
					</div>
					
					<!-- DO NOT REMOVE THIS CODE - Used to warning if top file convertion (scripts) has some problem 
					<div ng-show="outputPaths[selectedInputFileIndex].input.split('.')[1] == 'top'" align="center">
						<br><br><br>
						<div class="alert alert-warning" role="alert"><i>Attention: the visualization of the 3D structures from files with the .top format may contain inaccurate connectivities</i></div>
					</div>
					-->
					
					<div align="center">
						<div id="viewport" style="width:100%; height:540px;"></div>    					    					
					</div>
					
				</div>
				
        	</div>
			
        	<script type="text/javascript" src='../lib/ngl/v2.0.0-dev.32/dist/ngl.js'></script>	
        	<script type="text/javascript" src="../../../../js/external/jquery-3.1.1.min.js"></script>
			<script type="text/javascript" src="../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
			<script type="text/javascript" src="../../../../js/external/angular.min.js"></script>        		
        	<script type="text/javascript" src="../controller/nglControllerCofactorOrLigand.js"></script>
	</body>
</html>