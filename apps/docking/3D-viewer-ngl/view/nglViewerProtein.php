<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>NGL Viewer</title>		
		<link rel="stylesheet" href="../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body ng-app="nglViewerApp" ng-cloak>		
        	
		<div ng-controller="nglControllerProtein" ng-init="init()">
    		
  	  		<div class="container-fluid">
  	  			
				<div class="row" align="center">
					<div ng-include="'fragment-note-controls.html'"></div>
				</div>
				
  	  			<div class="row" align="center">
					
					<div id="viewport" style="width:100%; height:540px;"></div>    					    					
  				</div>
  				  
            	</div>
        	</div>
        	
        	<script type="text/javascript" src='../lib/ngl/v2.0.0-dev.32/dist/ngl.js'></script>
        	<script type="text/javascript" src="../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../js/external/angular.min.js"></script>        		
        	<script type="text/javascript" src="../controller/nglControllerProtein.js"></script>
        	
	</body>
</html>