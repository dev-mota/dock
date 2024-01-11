// var app = angular.module("nglCombineViewerApp", []); 

app.controller("nglControllerDocking", [ '$scope','$http', '$window', '$rootScope', function($scope, $http, $window, $rootScope) {
	
	// NGL.setDebug(true);

	$scope.$parent.$parent.$watch('selectedTab' , function(newValue, oldValue) {
		if($scope.$parent.$parent.selectedTab == 'DOCKING'){
			console.log("nglControllerDocking watch $scope.$parent.$parent.selectedTab=='DOCKING' ("+ $scope.$parent.$parent.selectedTab+")");
			$scope.init();
		}
	});
	
	/* Page controll vars */	
	$scope.path = null;
	$scope.protein = [];
	$scope.structure = null;
	$scope.combineType = null;
	$scope.maxTotalGridPoints = 900000;
	
	/* Min max grid size */
	$scope.gridRestrictions = {
			gridCenter : {
				x_min: 0,
				x_max: 0,					
				y_min: 0,
				y_max: 0,										
				z_min: 0,
				z_max: 0
			}
	}
	
	/* Used to restore default grid values  */
	$scope.gridDefault = {
			userDefined : {
				gridCenter : {
					x: 0,
					y: 0,
					z: 0
				},
				gridSize : {
					x: 10,
					y: 10,
					z: 10
				},
				rstep : 0.25
			},
			test : {
				gridCenter : {
					x: 16,
					y: 6,
					z: 20										
				},
				gridSize : {
					x: 22,
					y: 22,
					z: 22					
				},
				rstep : 0.25
			}
	};
	
	/* Start and define grid values */
	$scope.grid = {};
	angular.copy($scope.gridDefault.userDefined, $scope.grid);
	
	/* Total Grid point Variable */	
	$scope.points = null;
	
	/* Variables used to controll the css for Grid and Size buttons (selected or not) */	
	$scope.isBindingSiteUserDefinedSelected = true;
	$scope.isBindingSiteBlindDockingSelected = false;
	$scope.isBindingSiteTestSelected = false;
	
	/** Init function */
	
	$scope.init = function(){
		
		// Create NGL Stage object
		$scope.stage = new NGL.Stage( "viewport" );
		
		// Code for example: interactive/ligand-viewer
		$scope.stage.setParameters({
			backgroundColor: "white"
		})
		
		// remove default hoverPick mouse action
		$scope.stage.mouseControls.remove("hoverPick")
		
		// Disable mouse scroll (http://nglviewer.org/ngl/api/file/src/controls/mouse-controls.js.html)
		$scope.stage.mouseControls.remove( "scroll" );	
		
		// Disable drag and rotate component
		$scope.stage.mouseControls.remove( "drag-ctrl-shift-right" );
		$scope.stage.mouseControls.remove( "drag-ctrl-shift-left" );
		
		// listen to `hovered` signal to move tooltip around and change its text
		$scope.stage.signals.hovered.add(function (pickingProxy) {
			if (pickingProxy) {
				if (pickingProxy.atom || pickingProxy.bond) {
					var atom = pickingProxy.atom || pickingProxy.closestBondAtom
					var vm = atom.structure.data["@valenceModel"]      
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
							`
					} else {
						tooltip.innerHTML = `${pickingProxy.getLabel()}`
					}      
				} else {
					tooltip.innerHTML = `${pickingProxy.getLabel()}`
				}
				var mp = pickingProxy.mouse.position
				tooltip.style.bottom = window.innerHeight - mp.y + 3 + "px"
				tooltip.style.left = mp.x + 3 + "px"
				tooltip.style.display = "block"
			} else {
				tooltip.style.display = "none"
			}
		})

		$scope.stage.signals.clicked.add(function (pickingProxy) {
			if (pickingProxy && (pickingProxy.atom || pickingProxy.bond)) {
				console.log(pickingProxy.atom || pickingProxy.closestBondAtom)
			}
		})
		
		$scope.shape = new NGL.Shape("shape", { disableImpostor: true, radialSegments: 10 });
		
		$scope.combineType = 'docking';	
		$scope.getFilePaths('protein');			
		//$scope.getFilePaths('cofactor');
		$scope.createDockingBox();		
		
	}
	
	/** Functions to controll the css for Grid and Size buttons (selected or not) */
	
	$scope.resetToUserDefined = function(){
		
		angular.copy($scope.gridDefault.userDefined, $scope.grid);
		
		$scope.createDockingBox();
		
		$scope.isBindingSiteUserDefinedSelected = true;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = false;
	}
	
	$scope.setTestDocking = function(){
		
		angular.copy($scope.gridDefault.test, $scope.grid);
		
		$scope.createDockingBox();
		
		$scope.isBindingSiteUserDefinedSelected = false;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = true;
	}
	
	$scope.setBlindDocking = function(){
		
		$http.post(
			'apps/docking/3D-viewer-ngl/action/nglViewerAction.php',
			{					
				'action': 'calcBlindDocking',
				'prepName' : $scope.protein.fileName
			}
		).success(function(response){
			
			console.log("setBlindDocking:")
			console.log(response.data.grid);
			
			/** Center */
			
			$scope.grid.gridCenter.x = response.data.grid.X;
			$scope.grid.gridCenter.y = response.data.grid.Y;
			$scope.grid.gridCenter.z = response.data.grid.Z;
			
			/** Size */
			
			$scope.grid.gridSize.x = response.data.grid.gridSize;
			$scope.grid.gridSize.y = response.data.grid.gridSize;
			$scope.grid.gridSize.z = response.data.grid.gridSize;
			
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
		
	}
	
	/** Watch vars for calc "Total Grid Points" */
	
	$scope.$watchGroup(["grid.gridSize.x", "grid.gridSize.y", "grid.gridSize.z", "grid.rstep"] , function(newValue, oldValue) {
		
		// console.log("nglControllerDocking watchGroup: "+JSON.stringify($scope.grid.gridSize));
		
		// Old (jsmol)
		// $scope.points = Math.floor((2*$scope.xGridSizeSliderNum/$scope.rstep)+1)*Math.floor((2*$scope.yGridSizeSliderNum/$scope.rstep)+1)*Math.floor((2*$scope.zGridSizeSliderNum/$scope.rstep)+1);
		
		// New
		//console.log("size x: "+$scope.grid.gridSize.x);
		//console.log("rstep : "+$scope.grid.rstep);		
		//console.log( (2*$scope.grid.gridSize.x/$scope.grid.rstep)+1 );
		
		$scope.points = Math.floor(($scope.grid.gridSize.x/$scope.grid.rstep)+1)*Math.floor(($scope.grid.gridSize.y/$scope.grid.rstep)+1)*Math.floor(($scope.grid.gridSize.z/$scope.grid.rstep)+1);
		
		$scope.sendMessageToParent();		
	});
		
	$scope.sendMessageToParent = function(){
		// Used to enable or not the dock dutton
		$totalGridResponse = [];
		$totalGridResponse['total_grid_points'] = [];
		$totalGridResponse['total_grid_points']['points'] = $scope.points;
		if($scope.points <= $scope.maxTotalGridPoints){
			$totalGridResponse['total_grid_points']['valid'] = true;
		}else{
			$totalGridResponse['total_grid_points']['valid'] = false;
		}
		window.parent.postMessage($totalGridResponse, '*');
	}
	
	/** NGL Vars */ 
	
	$scope.shapeComp = null;
	$scope.structure = null;
	$scope.representation = null;
	
	/** Service functions */
	
	$scope.getFilePaths = function($structureType){
		
		$http.post(
				'apps/docking/3D-viewer-ngl/action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': $structureType, 
					'step': $scope.combineType
				}				
		).success(function(data, status, headers, config){
			
	        if(status == "200" && data.status == "OK"){
	        	
	        		console.log("nglControllerDocking ("+$scope.combineType+")getFilePaths success! ");
	        		
	        		var outputPaths = data.data;
	        		// console.log(outputPaths);
	        		
	        		if($structureType=='protein'){
	        			$scope.path = outputPaths[0].paths[0].path;
	        			// $scope.loadStructure($scope.path);
	        			$scope.loadStructure("apps/docking/session-files/05jtmpp8d4iap8sveb6p0cgjp3/DOCKING/PROTEIN/1caq_prep.pdb");
	        			
	        			
	        			// Add protein info
	        			$scope.protein.path = $scope.path;
	        			$scope.protein.pathArray = $scope.path.split('/');
	        			$scope.protein.fileName = $scope.protein.pathArray[$scope.protein.pathArray.length-1];
	        			
	        			// Set blind docking as default and set min max for grid size
	        			$scope.setBlindDocking();
	        			
	        		}else if($structureType=='ligand' || $structureType=='cofactor'){
	        			
		        		for (var structureIndex = 0; structureIndex < outputPaths.length; structureIndex++) {
		        			for (var pathIndex = 0; pathIndex < outputPaths[structureIndex].paths.length; pathIndex++) {
		        				$scope.path = outputPaths[structureIndex].paths[pathIndex].path;
		        				$scope.addRepresentation($scope.path);
						}	        			
					}
	        			
	        		}
	        			        		
	        } else if (data.status == "NOT_PREPARED_YET"){
	        		// console.log('nglSimpleViewerController loadNgl WARNING: not prepared yet!');
	        } else {
	        	
	        		console.log("nglControllerDocking getFilePaths ERROR: response error!");
	        }
		}).error(function(data, status, headers, config){
			console.log("nglControllerDocking getFilePaths ERROR: critical error!");
		});
	}
	
	/** 
	 * Every time this function is caled, all the custom shapes (box, a rrow, etc.)
	 * will be deleted and created again with the new values (positions and sizes)
	 * */
	$scope.createDockingBox = function (){
		
		/** Delete shapes (box, arrows, etc.) */ 
		if($scope.representation != undefined){
			$scope.stage.removeComponent($scope.shapeComp);
		}		
		
		/** Create the box and add it to shape */
		$scope.boxBuffer = new NGL.BoxBuffer({
			  position: new Float32Array([ $scope.grid.gridCenter.x, $scope.grid.gridCenter.y, $scope.grid.gridCenter.z]),
			  color: new Float32Array([ 0, 0, 0 ]), // 0, 0, 0 = rgb black
			  size: new Float32Array([ $scope.grid.gridSize.x ]),
			  heightAxis: new Float32Array([ 0, $scope.grid.gridSize.y, 0]), 
			  depthAxis: new Float32Array([ 0, 0, $scope.grid.gridSize.z]),
			  name: "mybox"
		})
		
		$scope.shape.addBuffer($scope.boxBuffer)		
		$scope.shapeComp = $scope.stage.addComponentFromObject($scope.shape)
		
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
		var green = [ 0, 1, 0 ] 	// rgb green
		var blue = [ 0, 0, 1 ] 	// rgb blue
		
		/* Red X arrow and text */
		var xPosition1 = [ ($scope.grid.gridSize.x/2) + parseInt($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, $scope.grid.gridCenter.z ];
		var xPosition2 = [ (($scope.grid.gridSize.x/2)*-1) + parseInt($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, $scope.grid.gridCenter.z ]
		$scope.shape.addArrow(xPosition1, xPosition2, red, 0.2, "X");
		$scope.shape.addText(xPosition1, red, 5, "X");
		
		/* Green Y arrow and text */
		var yPosition1 = [ $scope.grid.gridCenter.x, (($scope.grid.gridSize.y/2)*-1) + parseInt($scope.grid.gridCenter.y), $scope.grid.gridCenter.z ]; 
		var yPosition2 = [ $scope.grid.gridCenter.x, ($scope.grid.gridSize.y/2)      + parseInt($scope.grid.gridCenter.y), $scope.grid.gridCenter.z ]
		$scope.shape.addArrow(yPosition1, yPosition2, green,  0.2, "Y");
		$scope.shape.addText(yPosition1, green, 5, "Y");
		
		/* Blue Z arrow and text */
		var zPosition1 = [ ($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, (($scope.grid.gridSize.z/2)*-1) + parseInt($scope.grid.gridCenter.z)];
		var zPosition2 = [ ($scope.grid.gridCenter.x), $scope.grid.gridCenter.y, (($scope.grid.gridSize.z/2)) + parseInt($scope.grid.gridCenter.z) ];
		$scope.shape.addArrow(zPosition1, zPosition2, blue, 0.2, "Z");
		$scope.shape.addText(zPosition1, blue, 5, "Z");
		
		/** Finalize (add representation) */
		$scope.representation = $scope.shapeComp.addRepresentation("label", {opacity: 0.4}) // for texts
		$scope.representation = $scope.shapeComp.addRepresentation("buffer", { wireframe: true, opacity: 0.3}) // box and arrows
		
		$scope.stage.keyControls.run("r");
		
	}
	
	$scope.changeGrid = function(){
		
		/** Controll the css buttons */
		
		$scope.isBindingSiteUserDefinedSelected = true;
		$scope.isBindingSiteBlindDockingSelected = false;
		$scope.isBindingSiteTestSelected = false;
		
		$scope.createDockingBox();
	}
	
	$scope.loadStructure = function(path){
		console.log("nglControllerDocking loadStructure ("+$scope.combineType+"): " +path);		
		struc = undefined
		$scope.stage.setFocus(0)
		
		return $scope.stage.loadFile(path).then(function (o) {
			struc = o
			// o.autoView()
			
			$scope.stage.keyControls.run("r");
			cartoonRepr = o.addRepresentation("cartoon",{ 
					visible: true, 
					color: "chainid" 
			})
			licoriceRepr = o.addRepresentation("licorice")
			backboneRepr = o.addRepresentation("backbone", {
				visible: false, 
				colorValue: "lightgrey", 
				radiusScale: 2 
			})
			spacefillRepr = o.addRepresentation("spacefill", { 
				sele: ligandSele, 
				visible: true 
			})
			neighborRepr = o.addRepresentation("ball+stick", { 
				sele: "none", 
				aspectRatio: 1.1, 
				colorValue: "lightgrey", 
				multipleBond: "symmetric" 
			})
			ligandRepr = o.addRepresentation("ball+stick", { 
				multipleBond: "symmetric", 
				colorValue: "grey", 
				sele: "none", 
				aspectRatio: 1.2, 
				radiusScale: 2.5
			})
			contactRepr = o.addRepresentation("contact", { 
				sele: "none", 
				radiusSize: 0.07, 
				weakHydrogenBond: false, 
				waterHydrogenBond: false, 
				backboneHydrogenBond: true 
			})
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
			})
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
			})
		}) // --return
	}
	
	$scope.addRepresentation = function(path){
		
		console.log("nglControllerDocking addRepresentation... " +path);
		
		$scope.stage.loadFile(path).then(function (o) {
		  o.addRepresentation(
			"ball+stick", 
			{ 
				multipleBond: "symmetric",
				color: "element"
			});		  
		  // o.autoView();
		  $scope.stage.keyControls.run("r");
		})	
		
	}
	
	
	// Handle window resizing
	window.addEventListener( "resize", function( event ){
	    $scope.stage.handleResize();
	}, false );

	/** NGL */

	function createElement (name, properties, style) {
		var el = document.createElement(name)
		Object.assign(el, properties)
		Object.assign(el.style, style)
		return el
	}

	// create tooltip element and add to document body
	var tooltip = document.createElement("div")
	Object.assign(tooltip.style, {
		display: "none",
		position: "fixed",
		zIndex: 10,
		pointerEvents: "none",
		backgroundColor: "rgba( 0, 0, 0, 0.6 )",
		color: "lightgrey",
		padding: "8px",
		fontFamily: "sans-serif"
	})
	document.body.appendChild(tooltip)

	

	var ligandSele = "( not polymer or not ( protein or nucleic ) ) and not ( water or ACE or NH2 )"

	var pocketRadius = 0
	var pocketRadiusClipFactor = 1

	var cartoonRepr, backboneRepr, spacefillRepr, neighborRepr, ligandRepr, contactRepr, pocketRepr, labelRepr

	var struc
	var neighborSele
	// var sidechainAttached = false

	// function showFull () {
	$scope.showFull = function() {
		
		console.log("showFull ...");
		
		backboneRepr.setParameters({ radiusScale: 2 })
		spacefillRepr.setVisibility(true)
		licoriceRepr.setVisibility(true)

		backboneRepr.setVisibility(false)
		ligandRepr.setVisibility(false)
		neighborRepr.setVisibility(false)
		contactRepr.setVisibility(false)
		pocketRepr.setVisibility(false)
		labelRepr.setVisibility(false) 

		struc.autoView(2000)
	}

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