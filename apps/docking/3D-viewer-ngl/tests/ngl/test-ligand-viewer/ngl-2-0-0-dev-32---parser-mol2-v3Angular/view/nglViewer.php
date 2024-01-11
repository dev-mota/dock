<?php

?>

<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>NGL Viewer</title>		
		<link rel="stylesheet" href="../../../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body ng-app="nglViewerApp" ng-cloak>		
        	
		<div ng-controller="nglViewerController" ng-init="init()">
    		
  	  		<div class="container-fluid ">
  	  			
  	  			<div class="row">
  	  				<ul class="list-inline">
  	  					<li><!-- Small empty column --></li>
  	  					<li>
  	  						<label>Input file</label>
  	  					</li>
  	  					<li>
                            	<div class="dropdown">
        							<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        								{{outputPaths[selectedInputFileIndex].inputNoExt}}
                    					<span class="caret"></span>
                  				</button>
                  				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                    <li ng-repeat="(folderKey, folderValue) in outputPaths"><a href="#" ng-click="selectInputFileAndLoadNgl(folderKey)">{{folderValue.inputNoExt}}</a></li>                                                                
                  				</ul>
                				</div>
            				</li>
            				<li><!-- Small empty column --></li>
            				<li>
            					<label>{{pageProperties.pathsDropDownListHeader}}</label>
            				</li>
            				<li>
        						<div class="dropdown">
        							<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    					{{outputPaths[selectedInputFileIndex].paths[selectedFileIndex].nameNoExt}}           					
                    					<span class="caret"></span>
                  				</button>
                  				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                    <li ng-repeat="(fileKey, fileValue) in outputPaths[selectedInputFileIndex].paths"><a href="#" ng-click="selectPathFileAndLoadNgl(selectedInputFileIndex, fileKey)">{{fileValue.nameNoExt}}</a></li>                                                                
                  				</ul>
                    			</div>
                    		</li>  	
  	  				</ul>

  	  			</div>
  	  			
  	  			<div class="row" align="center">
  	  				<div id="viewport" style="width:100%; height:540px;"></div>    					    					
  				</div>
  				  
            	</div>
        	</div>
        	
        	<script type="text/javascript" src='https://cdn.rawgit.com/arose/ngl/v2.0.0-dev.32/dist/ngl.js'></script>	
        	<script type="text/javascript" src="../../../../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../../../../js/external/angular.min.js"></script>        		
        	<script type="text/javascript" src="../controller/nglViewerController.js"></script>
        	
	</body>
</html>


