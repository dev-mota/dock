<!-- Controller example:

            /** Docking NGL App */
			$scope.srcViewerDockingNgl = "";
			$scope.$watchGroup(['proteinInput', 'cofactorInput'] , function(newValues, oldValues) {
			
				if($scope.$parent.proteinInput != null){					
					$scope.loadNgl();						
				}
				
			});
			$scope.loadNgl = function(){
				console.log("DockingController open3DModalNGL loadNgl - prepare to run - selectedTab="+$scope.$parent.selectedTab+", proteinInput=" + $scope.$parent.proteinInput + ", cofactorInput=" + $scope.$parent.cofactorInput);
				
				// Checking cofactor
				var use_cofactor = false;
				if($scope.$parent.cofactorInput != null){
					use_cofactor = true;							
				}
					
				// Used to build a dummy var, only to force the angular to reload (https://stackoverflow.com/questions/30830406/how-to-refresh-iframe-url?rq=1)
				var d = new Date(); 	
				var t = d.getTime();
				
				// Build url
				$iframeUrl = "apps/docking/3D-viewer-ngl/view/nglViewerDocking.php?use_protein=true&use_cofactor="+use_cofactor+"&use_ligand=false&combine_type='docking'"+"&dummyVar="+ t;
				console.log("DockingController open3DModalNGL watchGroup - Loading iframe: "+$iframeUrl);
					
				// Load url
				// $('#nglViewerAutomaticIframeDocking').attr('src', $iframeUrl);
				$scope.srcViewerDockingNgl = $iframeUrl;
			}
			$scope.nglDockingTotalGridPointValid = true; 
			$window.addEventListener('message', function(e) {				
				if( (e.data['total_grid_points'] != undefined) && (e.data['total_grid_points']['valid']==false)  ){
					console.log("NGL Docking - total grid points is not valid: "+ e.data['total_grid_points']['points']);
					$scope.nglDockingTotalGridPointValid = false;					
				}	
		    });
-->

<!-- View example:

                <div class="embed-responsive embed-responsive-4by3">
					<iframe class="embed-responsive-item" ng-src="{{srcViewerDockingNgl}}" allowfullscreen></iframe>
                </div>	
-->

<!-- <!DOCTYPE html>  --> 
<!-- <html lang="en" > -->

<!-- 	<head> -->
<!-- 		<meta charset="UTF-8"> -->
<!-- 		<title>Docking NGL Viewer</title>		 -->
<!-- 		<link rel="stylesheet" href="../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css"> -->
		<link rel="stylesheet" href="apps/docking/3D-viewer-ngl/css/style.css">

<!-- 	</head> -->
	
<!-- 	<body ng-app="nglCombineViewerApp" ng-cloak>		 -->
        	
		<div ng-controller="nglControllerDocking" ng-init="">
    		
			<div class="panel panel-default">
  	  			
				<div class="panel-heading" ng-show="combineType=='docking'">
				
					<div class="container-fluid">
      	  				<div class="row">
      						<div class="col-xs-12">
      							<ul class="list-inline">
              	  					<li>
                                        	<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteUserDefinedSelected}" class="btn btn-default docking-pre-config-button" ng-click="resetToUserDefined()">User Defined</button>                                        	                                    	
                        				</li>
                        				<li>
        									<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteBlindDockingSelected}" class="btn btn-default docking-pre-config-button" ng-click="setBlindDocking()">Blind Docking</button>
                                		</li>                            		
                                		<li>
        									<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteTestSelected}" class="btn btn-default docking-pre-config-button" ng-click="setTestDocking()">Test</button>
                                		</li>                            		                                		  	
              	  				</ul>
      						</div>
      	  				</div>
      	  			</div>
      	  			
  	  			</div>
  	  			
  	  			<div class="panel-body">
  	  			
  	  				<div class="container-fluid">
						
						<div class="row" ng-show="combineType=='docking'">
					
      						<div class="col-xs-5">
      						
        							<label>Grid center:</label>
        							<form class="form-horizontal">
        								<div class="form-group">
        									<label for="nameGridCenterX" class="col-sm-1 control-label">X</label>
        									<div class="col-sm-5 col-md-8">
        										<input type="range" ng-model="grid.gridCenter.x" ng-value="grid.gridCenter.x" class="form-control" min="{{gridRestrictions.gridCenter.x_min}}" max="{{gridRestrictions.gridCenter.x_max}}"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridCenter.x" ng-value=grid.gridCenter.x class="form-control"  name="quantity" min="{{gridRestrictions.gridCenter.x_min}}" max="{{gridRestrictions.gridCenter.x_max}}" ng-change="changeGrid()" string-to-number>	
        									</div>        											        											
        								</div>
        								<div class="form-group">
        									<label for="nameGridCenterY" class="col-sm-1 control-label">Y</label>
        									<div class="col-sm-5 col-md-8">
        										<input type="range" ng-model="grid.gridCenter.y" ng-value="grid.gridCenter.y" class="form-control" min="{{gridRestrictions.gridCenter.y_min}}" max="{{gridRestrictions.gridCenter.y_max}}"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridCenter.y" ng-value="grid.gridCenter.y" class="form-control"  name="quantity" min="{{gridRestrictions.gridCenter.y_min}}" max="{{gridRestrictions.gridCenter.y_max}}" ng-change="changeGrid()" string-to-number>	
        									</div>        											        											
        								</div>
        								<div class="form-group">
        									<label for="nameGridCenterZ" class="col-sm-1 control-label">Z</label>
        									<div class="col-sm-5 col-md-8" >
        										<input type="range" ng-model="grid.gridCenter.z" ng-value="grid.gridCenter.z" class="form-control" min="{{gridRestrictions.gridCenter.z_min}}" max="{{gridRestrictions.gridCenter.z_max}}"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridCenter.z" ng-value="grid.gridCenter.z" class="form-control"  name="quantity" min="{{gridRestrictions.gridCenter.z_min}}" max="{{gridRestrictions.gridCenter.z_max}}" ng-change="changeGrid()" string-to-number>	
        									</div>        											        											
        								</div>        																				
        							</form>
    							  							
      						</div>
  						
      						<div class="col-xs-5">
      						
        							<label>Grid size:</label>
        							<form class="form-horizontal">
        								
        								<div class="form-group">
        									<label for="nameGridSizeX" class="col-sm-1 control-label">X</label>
        									<div class="col-sm-5 col-md-8">
        										<input type="range" ng-model="grid.gridSize.x" ng-value="grid.gridSize.x" class="form-control" min="0" max="180"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridSize.x" ng-value="grid.gridSize.x" class="form-control"  name="quantity" min="0" max="180" ng-change="changeGrid()" string-to-number>	
        									</div> 
        								</div>        										
        								 
        								<div class="form-group">
        									<label for="nameGridSizeY" class="col-sm-1 control-label">Y</label>
        									<div class="col-sm-5 col-md-8">
        										<input type="range" ng-model="grid.gridSize.y" ng-value="grid.gridSize.y" class="form-control" min="0" max="180"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridSize.y" ng-value="grid.gridSize.y" class="form-control"  name="quantity" min="0" max="180" ng-change="changeGrid()" string-to-number>	
        									</div> 
        								</div>
        								<div class="form-group">
        									<label for="nameGridSizeZ" class="col-sm-1 control-label">Z</label>
        									<div class="col-sm-5 col-md-8">
        										<input type="range" ng-model="grid.gridSize.z" ng-value="grid.gridSize.z" class="form-control" min="0" max="180"  ng-change="changeGrid()">
        									</div>
        									<div class="col-sm-5 col-md-3">
        										<input type="number" ng-model="grid.gridSize.z" ng-value="grid.gridSize.z" class="form-control"  name="quantity" min="0" max="180" ng-change="changeGrid()" string-to-number>	
        									</div> 
        								</div>
        																		
        							</form>
                                
      						</div>
      						
      						<div class="col-xs-1 col-sm-2">
      							
        							<form>
        								<div class="form-group">
        									<label for="nameRstep">Discretization:</label>
        									<input ng-model="grid.rstep" class="form-control" ng-class="{'has-error' : 0 > grid.rstep}" id="rstepInput" type="number" step="0.01" string-to-number>        									            									 
        								</div>																		
        							</form>
            						
    							
    								<form>
        								<div class="form-group">
        									<label for="namePoints">Total Grid Points:</label>
<!--         									<span ng-class="{'has-error' : points > 900000}" class="label label-default points-label-ngl">{{points}}</span> -->
										<input ng-class="{'has-error' : points > maxTotalGridPoints}" class="form-control" style="background: #d3d7cf; color: #777777" value="{{points}}" disabled>
        								</div>																		
        							</form>
            						
      						</div>
  					
  						</div>
  	  					
<!--   					    <div class="row"> -->
<!--       						<div ng-include="'apps/docking/3D-viewer-ngl/view/fragment-note-controls.html'"></div> -->
<!--   						</div>	 -->
  				
      					<div class="row" >  	  						
      						<div id="viewport" style="width:100%; height:500px;"></div>      						
      					</div>
      					
					</div>  									  					    					
  				</div>
			</div>	  
		</div>
		
		<script type="text/javascript" src='apps/docking/3D-viewer-ngl/lib/ngl/v2.0.0-dev.32/dist/ngl-v2.js'></script>	
<!--         	<script type="text/javascript" src="../../../../js/external/jquery-3.1.1.min.js"></script> -->
<!-- 		<script type="text/javascript" src="../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script> -->
<!-- 		<script type="text/javascript" src="../../../../js/external/angular.min.js"></script>        		 -->
        	<script type="text/javascript" src="apps/docking/3D-viewer-ngl/controller/nglControllerDocking.js"></script>
        	
<!-- 	</body> -->
	
<!-- </html> -->


