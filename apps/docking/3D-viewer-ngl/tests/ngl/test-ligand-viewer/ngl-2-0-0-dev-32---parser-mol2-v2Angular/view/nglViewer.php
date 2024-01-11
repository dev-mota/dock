<?php 
?>

<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>NGL Viewer</title>		
		<link rel="stylesheet" href="../../../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body ng-app="nglViewerApp">		
        	
		<div ng-controller="nglViewerController" ng-init="init()">
    		
  	  		<div class="container-fluid">
  	  			
  				<div class="row">
    					
    					<div class="col-sm-2">
    						<div class="dropdown" ng-repeat="(folderKey, folderValue) in outputPaths">
    							<button style="width: 140px !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                					{{folderValue.name}}
                					<span class="caret"></span>
              				</button>
              				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" ng-model="fileModelSelected" ng-init="fileModelSelected=null">
                                <li ng-repeat="fileModel in folderValue.paths | orderBy:sorterFunc"><a href="#" ng-click="loadNgl(fileModel)">{{fileModel.name}}</a></li>                                                                
              				</ul>
            				</div>     				            				
    					</div>
    					
    					<div class="col-sm-9">
    						<div align="center"><p><i>{{fileModelSelected.name}}</i></p></div>
    						<div id="viewport" style="width:100%; height:400px;"></div>
    					</div>
    					
  				</div>  
            	</div>
        	</div>
        	
        	<script type="text/javascript" src='https://cdn.rawgit.com/arose/ngl/v2.0.0-dev.32/dist/ngl.js'></script>	
        	<script type="text/javascript" src="../../../../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../../../../js/external/angular.min.js"></script>        		
        	<script type="text/javascript" src="./../controller/nglViewerController.js"></script>
        	
	</body>
</html>


