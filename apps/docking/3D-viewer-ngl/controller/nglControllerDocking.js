var app = angular.module("nglCombineViewerApp", []); 

app.controller("nglControllerDocking", [ '$scope','$http', '$window', '$q', function($scope, $http, $window, $q) {
	
	/* Page controll vars */	
	$scope.path = null;
	$scope.protein = [];
	$scope.structure = null;
	$scope.maxTotalGridPoints = 950000;
	$scope.proteinIsTestFile = false;
	$scope.maxDiscretization = 0.5;
	
	/* Min max grid size */
	$scope.gridRestrictionsDefault = {			
			gridCenter : {
				x_min: -300,
				x_max: 300,					
				y_min: -300,
				y_max: 300,										
				z_min: -300,
				z_max: 300
			},
			gridSize: {
				x_min: 10,
				x_max: 40,					
				y_min: 10,
				y_max: 40,										
				z_min: 10,
				z_max: 40
			}
	};
	$scope.gridRestrictions = angular.copy($scope.gridRestrictionsDefault);
	
	/* Used to restore default grid values  */
	$scope.gridDefault = {
			userDefined : {
				gridCenter : {
					x: 0,
					y: 0,
					z: 0
				},
				gridSize : {
					x: 20,
					y: 20,
					z: 20
				},
				rstep : 0.25
			},
			test : {
				gridCenter : {
					//x: 16,
					//y: 6,
					//z: 20
					x: 10.387,
					y: 18.119,
					z: 8.484										
				},
				gridSize : {
					//x: 22,
					//y: 22,
					//z: 22
					x: 20,
					y: 20,
					z: 20
				},
				rstep : 0.25
			}
	};
	
	/* Start and define grid values */
	$scope.grid = {};
	$scope.grid = angular.copy($scope.gridDefault.userDefined);
	
	/* Total Grid point Variable */	
	$scope.points = null;
	
	/* Variables used to controll the css for Grid and Size buttons (selected or not) */	
	$scope.isBindingSiteUserDefinedSelected = true;
	$scope.isBindingSiteBlindDockingSelected = false;
	$scope.isBindingSiteTestSelected = false;
	
	/** NGL Vars */ 		
	$scope.shapeComp = null;
	$scope.structure = null;
	$scope.shape = new NGL.Shape("shape", { disableImpostor: true, radialSegments: 10 });
	$scope.representation = null;

	$scope.init = function($useCofactor, $proteinIsTestFile){
		
		console.log("nglCombineViewerApp.nglControllerDocking.init($useCofactor="+$useCofactor+", $proteinIsTestFile="+$proteinIsTestFile+")");
		$scope.proteinIsTestFile = $proteinIsTestFile;
		
		$scope.loadProtein().then(function(){			
			$scope.resetToUserDefined().then(function() { // Set default button, values and min/max
				$scope.loadCofators($useCofactor).then(function() {			
					$scope.createDockingBox().then(function() {							
						// Centering (not work well in safary)
						// $scope.stage.setFocus(0); 
						$scope.stage.keyControls.run("r");
					}, function(data) {
						console.error('createDockingBox failed: '+data);
					});	
				}, function(data) {
					console.error('loadCofators failed: '+data);
				});					
			}, function(data) {
				console.error('resetToUserDefined failed'+data);
			});
		}, function(data) {
			console.error('loadProtein failed'+data);
		});
		
	};
	
	/** Function to controll the css for Grid and Size buttons (selected or not) */	
	$scope.resetToUserDefined = function(){
		
		var deferred = $q.defer();
		
		$scope.grid = angular.copy($scope.gridDefault.userDefined);
		$scope.gridRestrictions = angular.copy($scope.gridRestrictionsDefault);

		// $scope.createDockingBox();
		
		$scope.isBindingSiteUserDefinedSelected = true;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = false;
		
		deferred.resolve("resetToUserDefined resolved");		
		return deferred.promise;
	};
	
	$scope.resetToUserDefinedButton = function(){
		$scope.resetToUserDefined().then(function() {
			$scope.createDockingBox();
		}, function(data) {
			console.error('resetToUserDefinedButton failed'+data);
		});	
	};
	
	/** Function to controll the css for Grid and Size buttons (selected or not) */
	$scope.setTestDocking = function(){
		
		$scope.grid = angular.copy($scope.gridDefault.test);
		$scope.gridRestrictions = angular.copy($scope.gridRestrictionsDefault);
		
		$scope.createDockingBox();
		
		$scope.isBindingSiteUserDefinedSelected = false;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = true;
	};
	
	/** Function to controll the css for Grid and Size buttons (selected or not) */
	$scope.setBlindDocking = function(){
		
		$http.post(
			'../action/nglViewerAction.php',
			{					
				'action': 'calcBlindDocking',
				'prepName' : $scope.protein.fileName
			}
		).success(function(response){
			
			console.log("setBlindDocking:");
			console.log(response.data.grid);
			
			/** Center */
			
			$scope.grid.gridCenter.x = response.data.grid.X;
			$scope.grid.gridCenter.y = response.data.grid.Y;
			$scope.grid.gridCenter.z = response.data.grid.Z;
			
			/** Size */
			
			$scope.grid.gridSize.x = response.data.grid.gridSize*2;
			$scope.grid.gridSize.y = response.data.grid.gridSize*2;
			$scope.grid.gridSize.z = response.data.grid.gridSize*2;
			
			/** rstep */
			
			$scope.grid.rstep = response.data.grid.rstep;			
			// console.log($scope.grid); // DEBUG
			
			/** 
			 * Set global restriction for grid size
			 * Parse ex.:  split the string "-9.0570;33.2790" to array  ["-9.0570","33.2790"]
			 */
			
			$scope.gridRestrictions.gridCenter.x_min = response.data.grid['centerX_min-max'].split(";")[0]; 
			$scope.gridRestrictions.gridCenter.x_max = response.data.grid['centerX_min-max'].split(";")[1];
			$scope.gridRestrictions.gridCenter.y_min = response.data.grid['centerY_min-max'].split(";")[0];
			$scope.gridRestrictions.gridCenter.y_max = response.data.grid['centerY_min-max'].split(";")[1];
			$scope.gridRestrictions.gridCenter.z_min = response.data.grid['centerZ_min-max'].split(";")[0];
			$scope.gridRestrictions.gridCenter.z_max = response.data.grid['centerZ_min-max'].split(";")[1];
			
			/** Create box*/
			
			$scope.createDockingBox();

			/** Controll the css buttons */
			
			$scope.isBindingSiteUserDefinedSelected = false;
			$scope.isBindingSiteBlindDockingSelected = true;
			$scope.isBindingSiteTestSelected = false;
				
		});	
		
	};
	
	/** Watch vars for calc "Total Grid Points" */
	
	$scope.$watchGroup([
			"grid.gridCenter.x", 
			"grid.gridCenter.y", 
			"grid.gridCenter.z",
			"grid.gridSize.x", 
			"grid.gridSize.y", 
			"grid.gridSize.z",
			"grid.rstep"] , function(newValue, oldValue) {
		
		
		$scope.points = 
			Math.floor(( ($scope.grid.gridSize.x) / $scope.grid.rstep)+1) * 
			Math.floor(( ($scope.grid.gridSize.y) / $scope.grid.rstep)+1) *
			Math.floor(( ($scope.grid.gridSize.z) / $scope.grid.rstep)+1);
		$scope.sendMessageToParent();
	});
		
	$scope.sendMessageToParent = function(){
		
		// Array to be sent out to iframe
		$message = [];
		
		// Used to enable or not the dock dutton
		$message.total_grid_points = [];
		$message.total_grid_points.points = $scope.points;
		if($scope.points <= $scope.maxTotalGridPoints){
			$message.total_grid_points.valid = true;
		}else{
			$message.total_grid_points.valid = false;
		}
		
		// Message object (center, size and rstep)
		$message.grid = angular.copy($scope.grid);
		$message.grid.gridCenter.x = parseFloat($message.grid.gridCenter.x); 
		$message.grid.gridCenter.y = parseFloat($message.grid.gridCenter.y);
		$message.grid.gridCenter.z = parseFloat($message.grid.gridCenter.z);
		
		$message.grid.gridSize.x = parseFloat($message.grid.gridSize.x)/2;
		$message.grid.gridSize.y = parseFloat($message.grid.gridSize.y)/2;
		$message.grid.gridSize.z = parseFloat($message.grid.gridSize.z)/2;
		
		// Check error
		if(	!$scope.checkTotalGridPointsHasError() &&
			!$scope.checkDiscretizationHasError() &&
			!$scope.checkGridSizeXHasError() &&
			!$scope.checkGridSizeYHasError() &&
			!$scope.checkGridSizeZHasError() &&
			!$scope.checkGridCenterXHasError() &&
			!$scope.checkGridCenterYHasError() &&
			!$scope.checkGridCenterZHasError() 			
		){
			console.log("sendMessageToParent success");
			$message.grid.hasError = false;			
		} else {
			console.error("sendMessageToParent failed");
			$message.grid.hasError = true;			
		}
		
		// Post messaqge
		window.parent.postMessage($message, '*');
		
	};
	
	$scope.loadProtein = function(){		
		
		var deferred = $q.defer();

		$http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': 'protein', 
					'step': 'docking'
				}				
		).success(function(data, status){
			
	        if(status == "200" && data.status == "OK"){
	        	
				console.log("nglControllerDocking loadProtein success! ");
				
				var outputPaths = data.data;
				
				$scope.path = outputPaths[0].paths[0].path;
				
				$scope.addProteinRepresentation($scope.path).then(function() {
					
					// Add protein info
					$scope.protein.path = $scope.path;
					$scope.protein.pathArray = $scope.path.split('/');
					$scope.protein.fileName = $scope.protein.pathArray[$scope.protein.pathArray.length-1];
					
					// $scope.stage.setFocus(0);
					// $scope.stage.keyControls.run("r");	
					
					deferred.resolve("addProteinRepresentation resolved");
				
				}, function(data) {
					console.error('addProteinRepresentation failed'+data);
				});	
	        			        		
	        } else {
				console.error("nglControllerDocking getFilePaths ERROR: response error!");
	        }
		}).error(function(data, status, headers, config){
			console.error("nglControllerDocking getFilePaths ERROR: critical error! data="+data+", status="+status+", headers="+headers+", config="+config);
		});
		return deferred.promise;
	};

	$scope.loadCofators = function($useCofactor){
		
		var deferred = $q.defer();
		
		if($useCofactor){
			$http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': 'cofactor', 
					'step': 'docking'
				}				
			).success(function(data, status, headers, config){
				
				if(status == "200" && data.status == "OK"){
					
					console.log("nglControllerDocking loadCofators success! ");
					
					var outputPaths = data.data;
					
					var cofactorCount = 0;
					for (var i = 0; i < outputPaths.length; i++) {
						for (var j = 0; j < outputPaths[i].paths.length; j++) {
							cofactorCount++;
						}
					}
					
					var count = 0;
					for (var structureIndex = 0; structureIndex < outputPaths.length; structureIndex++) {
						for (var pathIndex = 0; pathIndex < outputPaths[structureIndex].paths.length; pathIndex++) {
							$scope.path = outputPaths[structureIndex].paths[pathIndex].path;
							$scope.addRepresentation($scope.path).then(function() {
								count++;
								if(count==cofactorCount){
									deferred.resolve("loadCofators resolved");									
								}
							}, function(data) {
								console.error('addRepresentation failed'+data);
							});
							
						}
					}	 
											
				} else {
					console.error("nglControllerDocking getFilePaths ERROR: response error! Data:"+data+", headers"+headers+ ", config"+config);
				}
			}).error(function(data, status, headers, config){
				console.error("nglControllerDocking getFilePaths ERROR: critical error! Data:"+data+", headers"+headers+ ", config"+config);
			});
		} else {
			deferred.resolve("loadCofators resolved");			
		}
		return deferred.promise;
	};
	
	/** 
	 * Every time this function is caled, all the custom shapes (box, a rrow, etc.)
	 * will be deleted and created again with the new values (positions and sizes)
	 * */
	$scope.createDockingBox = function (){
		
		var deferred = $q.defer();
		
		/** Delete shapes (box, arrows, etc.) */ 
		$scope.stage.eachComponent( function( comp ){
			if(comp.parameters.name == "shape"){ // "shape" is the default name
				$scope.stage.removeComponent(comp);
			}		
		});
		
		/** Create the box and add it to shape */
		$scope.boxBuffer = new NGL.BoxBuffer({
			  position: new Float32Array([ $scope.grid.gridCenter.x, $scope.grid.gridCenter.y, $scope.grid.gridCenter.z]),
			  color: new Float32Array([ 0, 0, 0 ]), // 0, 0, 0 = rgb black
			  size: new Float32Array([ $scope.grid.gridSize.x ]),
			  heightAxis: new Float32Array([ 0, $scope.grid.gridSize.y, 0]), 
			  depthAxis: new Float32Array([ 0, 0, $scope.grid.gridSize.z]),
			  name: "mybox"
		});
		
		$scope.shape.addBuffer($scope.boxBuffer);		
		$scope.shapeComp = $scope.stage.addComponentFromObject($scope.shape);
		
		/** Create and add arrows and texts */
		
		/*
		 * http://nglviewer.org/ngl/api/class/src/geometry/shape.js~Shape.html#instance-method-addArrow
		 * addArrow(position1: Vector3 | Array, position2: Vector3 | Array, color: Color | Array, radius: Float, name: String): Shape
		 * Sample: 
		 * $scope.shape.addArrow([ 10, 0, 0 ], [ -10, 0, 0 ], [ 1, 0, 0 ], 1, "X"); // 1, 0, 0 = rgb red
		 * $scope.shape.addArrow([ 0, 10, 0 ], [ 0, -10, 0 ], [ 0, 1, 0 ], 0.1, "Y"); // 0, 1, 0 = rgb green
		 * $scope.shape.addArrow([ 0, 0, 10 ], [ 0, 0, -10 ], [ 0, 0, 1 ], 0.1, "Z"); // 0, 0, 1 = rgb blue 
		 */		
		
		/* Colors for arrow and texts */
		var red = [ 1, 0, 0 ]; 	// rgb red 
		var green = [ 0, 1, 0 ]; 	// rgb green
		var blue = [ 0, 0, 1 ];	// rgb blue
		
		/* Red X arrow and text */
		var xPosition1 = [ ($scope.grid.gridSize.x/2) + parseInt($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, $scope.grid.gridCenter.z ];
		var xPosition2 = [ (($scope.grid.gridSize.x/2)*-1) + parseInt($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, $scope.grid.gridCenter.z ];
		$scope.shape.addArrow(xPosition1, xPosition2, red, 0.2, "X");
		$scope.shape.addText(xPosition1, red, 5, "X");
		
		/* Green Y arrow and text */
		var yPosition1 = [ $scope.grid.gridCenter.x, (($scope.grid.gridSize.y/2)*-1) + parseInt($scope.grid.gridCenter.y), $scope.grid.gridCenter.z ]; 
		var yPosition2 = [ $scope.grid.gridCenter.x, ($scope.grid.gridSize.y/2)      + parseInt($scope.grid.gridCenter.y), $scope.grid.gridCenter.z ];
		$scope.shape.addArrow(yPosition1, yPosition2, green,  0.2, "Y");
		$scope.shape.addText(yPosition1, green, 5, "Y");
		
		/* Blue Z arrow and text */
		var zPosition1 = [ ($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, (($scope.grid.gridSize.z/2)*-1) + parseInt($scope.grid.gridCenter.z)];
		var zPosition2 = [ ($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, (($scope.grid.gridSize.z/2)) + parseInt($scope.grid.gridCenter.z) ];
		$scope.shape.addArrow(zPosition1, zPosition2, blue, 0.2, "Z");
		$scope.shape.addText(zPosition1, blue, 5, "Z");
		
		/** Finalize (add representation) */
		$scope.shapeComp.addRepresentation("label", {opacity: 0.4}); // for texts
		$scope.shapeComp.addRepresentation("buffer", { wireframe: true, opacity: 0.3}); // box and arrows

		deferred.resolve("createDockingBox resolved");
		return deferred.promise;
	};
	
	$scope.changeGrid = function(){

		/*
		if($scope.grid.gridCenter.x == undefined){
			$scope.grid.gridCenter.x = "-";
		}
		*/
		
		$scope.isBindingSiteUserDefinedSelected = true;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = false;
		
		$scope.createDockingBox();	
		
	};
	
	$scope.addProteinRepresentation = function(path){
		//console.log("nglControllerDocking addProteinRepresentation ("+$scope.combineType+"): " +path);		
		var deferred = $q.defer();
		
		$scope.stage.loadFile(path).then(function (o) {
			// struc = o
			// o.autoView()
			// $scope.stage.keyControls.run("r");
			// $scope.stage.setFocus(0);
			
			cartoonRepr = o.addRepresentation("cartoon",{ 
					visible: true, 
					color: "chainid" 
			});
			licoriceRepr = o.addRepresentation("licorice");
			backboneRepr = o.addRepresentation("backbone", {
				visible: false, 
				colorValue: "lightgrey", 
				radiusScale: 2 
			});
			spacefillRepr = o.addRepresentation("spacefill", { 
				sele: ligandSele, 
				visible: true 
			});
			neighborRepr = o.addRepresentation("ball+stick", { 
				sele: "none", 
				aspectRatio: 1.1, 
				colorValue: "lightgrey", 
				multipleBond: "symmetric" 
			});
			ligandRepr = o.addRepresentation("ball+stick", { 
				multipleBond: "symmetric", 
				colorValue: "grey", 
				sele: "none", 
				aspectRatio: 1.2, 
				radiusScale: 2.5
			});
			contactRepr = o.addRepresentation("contact", { 
				sele: "none", 
				radiusSize: 0.07, 
				weakHydrogenBond: false, 
				waterHydrogenBond: false, 
				backboneHydrogenBond: true 
			});
			pocketRepr = o.addRepresentation("surface", { 
				sele: "none", 
				lazy: true, 
				visibility: true, 
				clipNear: 0, 
				opaqueBack: false, 
				opacity: 0.0, 
				color: "hydrophobicity", 
				roughness: 1.0, 
				surfaceType: "av"
			});
			labelRepr = o.addRepresentation("label", {
				sele: "none",
				color: "#333333",
				yOffset: 0.2,
				zOffset: 2.0,
				attachment: "bottom-center",
				showBorder: true,
				borderColor: "lightgrey",
				borderWidth: 0.25,
				disablePicking: true,
				radiusType: "size",
				radiusSize: 0.8,
				labelType: "residue",
				labelGrouping: "residue"
			});
			deferred.resolve("addProteinRepresentation complete");
		}); // --return
		return deferred.promise;
	};
	
	$scope.addRepresentation = function(path){
		
		//console.log("nglControllerDocking addRepresentation... " +path);
		
		return $scope.stage.loadFile(path).then(function (o) {
		  o.addRepresentation(
			"ball+stick", 
			{ 
				multipleBond: "symmetric",
				color: "element"
			});		  
		  // o.autoView();
		  // $scope.stage.keyControls.run("r");
		});
		
	};
	
	// Create NGL Stage object
	$scope.stage = new NGL.Stage( "viewport" );

	// Handle window resizing
	window.addEventListener( "resize", function( event ){
	    $scope.stage.handleResize();
	}, false );

	// Code for example: interactive/ligand-viewer
	$scope.stage.setParameters({
		backgroundColor: "white"
	});
	
	/** NGL */

	function createElement (name, properties, style) {
		var el = document.createElement(name);
		Object.assign(el, properties);
		Object.assign(el.style, style);
		return el;
	};

	// create tooltip element and add to document body
	var tooltip = document.createElement("div");
	Object.assign(tooltip.style, {
		display: "none",
		position: "fixed",
		zIndex: 10,
		pointerEvents: "none",
		backgroundColor: "rgba( 0, 0, 0, 0.6 )",
		color: "lightgrey",
		padding: "8px",
		fontFamily: "sans-serif"
	});
	document.body.appendChild(tooltip);

	// remove default hoverPick mouse action
	$scope.stage.mouseControls.remove("hoverPick");

	// listen to `hovered` signal to move tooltip around and change its text
	$scope.stage.signals.hovered.add(function (pickingProxy) {
		if (pickingProxy) {
			if (pickingProxy.atom || pickingProxy.bond) {
				var atom = pickingProxy.atom || pickingProxy.closestBondAtom;
				var vm = atom.structure.data["@valenceModel"];
				if (vm && vm.charge) {
					/*
					tooltip.innerHTML = 
						`
						${pickingProxy.getLabel()}<br/>
						<hr/>
						Atom: ${atom.qualifiedName()}<br/>
						vm charge: ${vm.charge[atom.index]}<br/>
						vm implicitH: ${vm.implicitH[atom.index]}<br/>
						vm totalH: ${vm.totalH[atom.index]}<br/>
						vm geom: ${vm.idealGeometry[atom.index]}</br>
						formal charge: ${atom.formalCharge === null ? "?" : atom.formalCharge}<br/>
						aromatic: ${atom.aromatic ? "true" : "false"}<br/>
						`
					*/
					tooltip.innerHTML = 
						`
						${pickingProxy.getLabel()}<br/>
						<hr/>
						Atom: ${atom.qualifiedName()}<br/>
						aromatic: ${atom.aromatic ? "true" : "false"}<br/>
						`;
				} else {
					tooltip.innerHTML = `${pickingProxy.getLabel()}`;
				}      
			} else {
				tooltip.innerHTML = `${pickingProxy.getLabel()}`;
			}
			var mp = pickingProxy.mouse.position;
			tooltip.style.bottom = window.innerHeight - mp.y + 3 + "px";
			tooltip.style.left = mp.x + 3 + "px";
			tooltip.style.display = "block";
		} else {
			tooltip.style.display = "none";
		}
	});

	$scope.stage.signals.clicked.add(function (pickingProxy) {
		if (pickingProxy && (pickingProxy.atom || pickingProxy.bond)) {
			console.log(pickingProxy.atom || pickingProxy.closestBondAtom);
		}
	});

	var ligandSele = "( not polymer or not ( protein or nucleic ) ) and not ( water or ACE or NH2 )";

	var cartoonRepr, backboneRepr, spacefillRepr, neighborRepr, ligandRepr, contactRepr, pocketRepr, labelRepr;

	var struc;	

	// function showFull () {
	$scope.showFull = function() {
		
		console.log("showFull ...");
		
		backboneRepr.setParameters({ radiusScale: 2 });
		spacefillRepr.setVisibility(true);
		licoriceRepr.setVisibility(true);

		backboneRepr.setVisibility(false);
		ligandRepr.setVisibility(false);
		neighborRepr.setVisibility(false);
		contactRepr.setVisibility(false);
		pocketRepr.setVisibility(false);
		labelRepr.setVisibility(false);

		struc.autoView(2000);
	};
	
	// Disable mouse scroll (http://nglviewer.org/ngl/api/file/src/controls/mouse-controls.js.html)
	$scope.stage.mouseControls.remove( "scroll" );	
	
	// Disable drag and rotate component
	$scope.stage.mouseControls.remove( "drag-ctrl-shift-right" );
	$scope.stage.mouseControls.remove( "drag-ctrl-shift-left" );
	
	$scope.checkTotalGridPointsHasError = function(){
		if(
		   (isNaN($scope.points)) || // check if is not a number
		   ($scope.points > $scope.maxTotalGridPoints)		   
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkDiscretizationHasError = function(){
		if(
		   ($scope.grid.rstep < 0) || ($scope.grid.rstep > $scope.maxDiscretization)   
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridSizeXHasError = function(){
		if(
			($scope.grid.gridSize.x == undefined) ||
			($scope.grid.gridSize.x < $scope.gridRestrictions.gridSize.x_min) ||
			($scope.grid.gridSize.x > $scope.gridRestrictions.gridSize.x_max)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridSizeYHasError = function(){
		if(
			($scope.grid.gridSize.y == undefined) ||
			($scope.grid.gridSize.y < $scope.gridRestrictions.gridSize.y_min) ||
			($scope.grid.gridSize.y > $scope.gridRestrictions.gridSize.y_may)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridSizeZHasError = function(){
		if(
			($scope.grid.gridSize.z == undefined) ||
			($scope.grid.gridSize.z < $scope.gridRestrictions.gridSize.z_min) ||
			($scope.grid.gridSize.z > $scope.gridRestrictions.gridSize.z_maz)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridCenterXHasError = function(){
		if(
			isNaN($scope.grid.gridCenter.x)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridCenterYHasError = function(){
		if(
			isNaN($scope.grid.gridCenter.y)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.checkGridCenterZHasError = function(){
		if(
			isNaN($scope.grid.gridCenter.z)
		){
			return true;
		} else {
			return false;
		}
	};
	
	$scope.replaceNaNValue = function(value){
		if(isNaN(value)){
			return '-';
		}else{
			return value;
		}
	};
	
	$scope.reload = function(){
		console.log("Reload iframe ngl tab docking!");
		window.location.reload();
	};

}])
.directive('stringToNumber', function() {
	return {
		require: 'ngModel',
		link: function(scope, element, attrs, ngModel) {
			ngModel.$parsers.push(function(value) {
				return '' + value;
			});
			ngModel.$formatters.push(function(value) {
				return parseFloat(value, 10);
			});
	    	}
	};
});
