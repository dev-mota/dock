<?php 

$use_cofactor = false;
if(isset($_GET["use_cofactor"])){
    $use_cofactor = $_GET["use_cofactor"];
}

$protein_is_test_file = false;
if(isset($_GET["protein_is_test_file"])){
    $protein_is_test_file = $_GET["protein_is_test_file"];
}

?>
<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
        
		<title>Docking NGL Viewer</title>		
		<link rel="stylesheet" href="../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/style.css">

	</head>
	
	<body ng-app="nglCombineViewerApp" ng-cloak>		
        	
		<div ng-controller="nglControllerDocking" ng-init="init(<?= $use_cofactor?>, <?= $protein_is_test_file?>)">
    		
			<div class="panel panel-default">
  	  			
				<div class="panel-heading">
				
					<div class="container-fluid">
      	  				<div class="row">
      						<div class="col-xs-12">
      							<ul class="list-inline">
              	  					<li>
                                        <button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteUserDefinedSelected}" class="btn btn-default docking-pre-config-button" ng-click="resetToUserDefinedButton()">User Defined</button>                                        	                                    	
                        			</li>
									<li>
										<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteBlindDockingSelected}" class="btn btn-default docking-pre-config-button" ng-click="setBlindDocking()">Blind Docking</button>
									</li>                            		
									<li>
										<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteTestSelected}" class="btn btn-default docking-pre-config-button" ng-click="setTestDocking()" ng-show="proteinIsTestFile">Test</button>
									</li>                                    
                                    <!--
                                    <li>
										<button type="button" class="btn btn-warning" ng-click="reload()">Reload</button>
									</li>
                                    -->
              	  				</ul>
      						</div>
      	  				</div>
      	  			</div>
      	  			
  	  			</div>
  	  			
  	  			<div class="panel-body">
  	  			
  	  				<div class="container-fluid">
						
						<div class="row">
					
      						<div class="col-xs-5">
      						
                                <label>Grid center:</label>
                                <form class="form-horizontal">
                                    <div class="form-group">
                                        <label for="nameGridCenterX" class="col-xs-1 control-label">X</label>
                                        <div class="col-xs-5">
                                            <input type="range" ng-model="grid.gridCenter.x" ng-value="grid.gridCenter.x" class="form-control" min="{{gridRestrictions.gridCenter.x_min}}" max="{{gridRestrictions.gridCenter.x_max}}"  ng-change="changeGrid()">
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridCenterXHasError()}">
                                            <input type="number" ng-model="grid.gridCenter.x" ng-value="grid.gridCenter.x" class="form-control" name="quantity" min="{{gridRestrictions.gridCenter.x_min}}" max="{{gridRestrictions.gridCenter.x_max}}" ng-change="changeGrid()" string-to-number>	
                                        </div>        											        											
                                    </div>
                                    <div class="form-group">
                                        <label for="nameGridCenterY" class="col-xs-1 control-label">Y</label>
                                        <div class="col-xs-5">
                                            <input type="range" ng-model="grid.gridCenter.y" ng-value="grid.gridCenter.y" class="form-control" min="{{gridRestrictions.gridCenter.y_min}}" max="{{gridRestrictions.gridCenter.y_max}}"  ng-change="changeGrid()">
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridCenterYHasError()}">
                                            <input type="number" ng-model="grid.gridCenter.y" ng-value="grid.gridCenter.y" class="form-control"  name="quantity" min="{{gridRestrictions.gridCenter.y_min}}" max="{{gridRestrictions.gridCenter.y_max}}" ng-change="changeGrid()" string-to-number>	
                                        </div>        											        											
                                    </div>
                                    <div class="form-group">
                                        <label for="nameGridCenterZ" class="col-xs-1 control-label">Z</label>
                                        <div class="col-xs-5" >
                                            <input type="range" ng-model="grid.gridCenter.z" ng-value="grid.gridCenter.z" class="form-control" min="{{gridRestrictions.gridCenter.z_min}}" max="{{gridRestrictions.gridCenter.z_max}}"  ng-change="changeGrid()">
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridCenterZHasError()}">
                                            <input type="number" ng-model="grid.gridCenter.z" ng-value="grid.gridCenter.z" class="form-control"  name="quantity" min="{{gridRestrictions.gridCenter.z_min}}" max="{{gridRestrictions.gridCenter.z_max}}" ng-change="changeGrid()" string-to-number>	
                                        </div>        											        											
                                    </div>        																				
                                </form>
    							  							
      						</div>
  						
      						<div class="col-xs-5">
      						
                                <label>Grid size:</label>
                                <form class="form-horizontal">
                                    
                                    <div class="form-group">
                                        <label for="nameGridSizeX" class="col-xs-1 control-label">X</label>
                                        <div class="col-xs-5">
                                            <input type="range" ng-model="grid.gridSize.x" ng-value="grid.gridSize.x" ng-class="form-control" min="{{gridRestrictions.gridSize.x_min}}" max="{{gridRestrictions.gridSize.x_max}}" ng-change="changeGrid()" string-to-number>
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridSizeXHasError()}">
                                            <input type="number" ng-model="grid.gridSize.x" ng-value="grid.gridSize.x" class="form-control" name="quantity" min="{{gridRestrictions.gridSize.x_min}}" max="{{gridRestrictions.gridSize.x_max}}" ng-change="changeGrid()" string-to-number>
                                        </div> 
                                    </div>        										
                                     
                                    <div class="form-group">
                                        <label for="nameGridSizeY" class="col-xs-1 control-label">Y</label>
                                        <div class="col-xs-5">
                                            <input type="range" ng-model="grid.gridSize.y" ng-value="grid.gridSize.y" class="form-control" min="{{gridRestrictions.gridSize.y_min}}" max="{{gridRestrictions.gridSize.y_max}}"  ng-change="changeGrid()">
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridSizeYHasError()}">
                                            <input type="number" ng-model="grid.gridSize.y" ng-value="grid.gridSize.y" class="form-control" ng-class="{'has-error' : checkGridSizeYHasError()}" name="quantity" min="{{gridRestrictions.gridSize.y_min}}" max="{{gridRestrictions.gridSize.y_max}}" ng-change="changeGrid()" string-to-number>	
                                        </div> 
                                    </div>
                                    <div class="form-group">
                                        <label for="nameGridSizeZ" class="col-xs-1 control-label">Z</label>
                                        <div class="col-xs-5">
                                            <input type="range" ng-model="grid.gridSize.z" ng-value="grid.gridSize.z" class="form-control" min="{{gridRestrictions.gridSize.z_min}}" max="{{gridRestrictions.gridSize.z_max}}"  ng-change="changeGrid()">
                                        </div>
                                        <div class="col-xs-5" ng-class="{'has-error' : checkGridSizeZHasError()}">
                                            <input type="number" ng-model="grid.gridSize.z" ng-value="grid.gridSize.z" class="form-control" ng-class="{'has-error' : checkGridSizeZHasError()}" name="quantity" min="{{gridRestrictions.gridSize.z_min}}" max="{{gridRestrictions.gridSize.z_max}}" ng-change="changeGrid()" string-to-number>	
                                        </div> 
                                    </div>
                                                                            
                                </form>
                                
      						</div>
							  
							<!-- 
								col-xs- Extra small devices Phones (<768px)	
								col-sm- Small devices Tablets (≥768px)	
								col-md- Medium devices Desktops (≥992px)
								col-lg-	Large devices Desktops (≥1200px) 
							-->
      						<div class="col-xs-2">
      							
        							<form>
        								<div class="form-group">
        									<label for="nameRstep">Discretization:</label>
                                            <div ng-class="{'has-error' : checkDiscretizationHasError()}">
                                                <input ng-model="grid.rstep" class="form-control" id="rstepInput" type="number" step="0.01" string-to-number>
                                            </div>
        								</div>																		
        							</form>
            						
    								<form>
        								<div class="form-group">
        									<label for="namePoints">Total Grid Points:</label>
											<div ng-class="{'has-error' : checkTotalGridPointsHasError()}">
                                                <input class="form-control" style="background: #d3d7cf; color: #777777" ng-value="replaceNaNValue(points)" disabled>
                                            </div>
        								</div>																		
        							</form>
            						
      						</div>
  					
  						</div>
  	  					
  					    <div class="row">
      						<div ng-include="'fragment-note-controls.html'"></div>
  						</div>	
  				
      					<div class="row" >  	  						
      						<div id="viewport" style="width:100%; height:500px;"></div>
      					</div>
      					
					</div>  									  					    					
  				</div>
			</div>	  
		</div>
		
		<script type="text/javascript" src='../lib/ngl/v2.0.0-dev.32/dist/ngl.js'></script>	
        <script type="text/javascript" src="../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../js/external/angular.min.js"></script>        		
        <script type="text/javascript" src="../controller/nglControllerDocking.js"></script>
                	
	</body>
	
</html>


