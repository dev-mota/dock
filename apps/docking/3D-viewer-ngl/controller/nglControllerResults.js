var app = angular.module("nglViewerApp", []); 

app.controller("nglControllerResults", [ '$scope','$http', '$q', function($scope, $http, $q) {
		
	console.log("nglControllerResults");
		
	/* Pagination */
	
	$scope.qntPerPage = 5;
	$scope.selectedPage = 0;
	
	$scope.getQntPages = function(){
		return Math.ceil($scope.resultElements.length/$scope.qntPerPage); // Round up
	};
	
	$scope.showPaginationElement = function(index){
		if(
		   ( index>=( $scope.selectedPage * $scope.qntPerPage) ) &&
		   ( index< ( $scope.selectedPage * $scope.qntPerPage) + $scope.qntPerPage) ) {
			return true;
		} else {
			return false;
		}
	};
	
	$scope.next = function(){
		var lastPageIndex = $scope.getQntPages() - 1;
		if( ($scope.resultElements.length>$scope.qntPerPage)&&($scope.selectedPage<lastPageIndex)){
			$scope.selectedPage++;
		}		
	};
	
	$scope.previous = function(){
		if($scope.selectedPage>0){
			$scope.selectedPage--;
		}		
	};
	
	$scope.jumpTo = function(index){
		$scope.selectedPage = index;
	};
	
	/* Page controll vars */	
	$scope.path = null;
	$scope.protein = [];
	$scope.sessionId = null;
	$scope.sessionResultPath = null;
	$scope.resultElementSelected = null;
	
	/* Total Grid point Variable */	
	$scope.points = null;
	
	/* Will be filled in init(). The data comes from results.php with ligand log */
	$scope.resultElements = [];
	
	/** NGL Vars */ 	
	$scope.structure = null;
	$scope.shapeComp = null;
	$scope.shape = new NGL.Shape("shape", { disableImpostor: true, radialSegments: 10 });
	
	/* Bootstrap tooltip */
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	/* Ligand reference file */
	$scope.referenceFilePath = null;

	$scope.init = function(jobId, originalResultElements, referenceFileName){
		
		console.log("nglControllerResults init: jobId="+jobId+", elements="+originalResultElements+", referenceFileName="+referenceFileName);
		
		$scope.jobId = jobId;

		// Transform string elements to object elements
		$scope.resultElements = $scope.parseResultElements(originalResultElements);		
		console.log("Analise result elements as objects:")
		console.log($scope.resultElements);

		// Set default radio buuton selected
		$scope.resultElementSelected =  $scope.resultElements[0].elements[0];

		$scope.setSessionVars().then(function() { 
			$scope.loadProtein().then(function() { 
				$scope.loadCofators().then(function() { 
					$scope.loadLigands().then(function() {						
						$scope.loadReferenceFile(referenceFileName).then(function(loadReferenceFileResponse) {
							console.log(loadReferenceFileResponse);
							
							//$scope.stage.keyControls.run("r"); // O zoom para tra é demais
							$scope.stage.viewerControls.zoom(-0.5);// Negativo para trás e positivo para frente
							// $scope.stage.viewerControls.distance(200); // Nao funcionou bem
							
						}, function(loadReferenceFileError) {
							console.error(loadReferenceFileError);
						});	
					}, function(loadLigandsResponseError) {
						console.error(loadLigandsResponseError);
					});
				}, function(loadCofactorResponseError) {
					console.error(loadCofactorResponseError);
				});
			}, function(loadProteinResponseError) {
				console.error(loadProteinResponseError);
			});
		}, function(data) {
			console.error('set session vars failed.');
		});	

	};
	
	$scope.hasLigandReference = function(){
		if($scope.referenceFilePath == null){
			return false;
		} else {
			return true;
		}		
	};

	$scope.setSessionVars = function(){
		return $http.post(
			'../action/nglViewerAction.php',
			{					
				'action': 'getSessionId',				
			}				
		).success(function(data, status, headers, config){
			if(status == "200" && data.status == "OK"){
				$scope.sessionId = data.data;
				$scope.sessionResultPath = "../../session-files/"+$scope.sessionId+"/RESULTS/"+$scope.jobId+"/RESULT/"		
			}			
		}).error(function(data, status, headers, config){
			console.error("nglControllerResults getSessionId ERROR: critical error!");			
		});
	}

	/**
	 * LOAD FROM JOB DAEMON FOLDER (../docking/daemon/jobs)
	 */
	$scope.loadProtein = function(){
		
		return $http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': 'protein', 
					'step': 'results',
					'jobId': $scope.jobId
				}				
		).success(function(data, status, headers, config){
			
			if(status == "200" && data.status == "OK"){
				
				var outputPaths = data.data;
				// console.log(outputPaths);
				
				$scope.path = outputPaths[0].paths[0].path;
				$scope.addProteinRepresentation($scope.path).then(function(successResponse) {

					// Debug
					// alert('Success: ' + successResponse);

					// Add protein info
					$scope.protein.path = $scope.path;
					$scope.protein.pathArray = $scope.path.split('/');
					$scope.protein.fileName = $scope.protein.pathArray[$scope.protein.pathArray.length-1];

					return successResponse;
				}, function(reason) {

					// Debug
					// alert('Failed: ' + reason);

					return failedResponse;
				});
										
			} else {
				console.error("nglControllerResults getProtein failed!");
			}
		}).error(function(data, status, headers, config){
			console.error("nglControllerResults getProtein httpt failed!");
		});
	}

	/**
	 * LOAD FROM JOB DAEMON FOLDER (../docking/daemon/jobs)
	 */
	$scope.loadCofators = function(){
		
		return $http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': 'cofactor', 
					'step': 'results',
					'jobId': $scope.jobId
				}				
		).success(function(data, status, headers, config){
			
			if(status == "200" && data.status == "OK"){
				
				var outputPaths = data.data;
				console.log(outputPaths);
				
				// Cofactors
				for (var structureIndex = 0; structureIndex < outputPaths.length; structureIndex++) {

					// Molecules
					for (var pathIndex = 0; pathIndex < outputPaths[structureIndex].paths.length; pathIndex++) {
						$scope.path = outputPaths[structureIndex].paths[pathIndex].path;
						$scope.addCofactorOrLigandRepresentation($scope.path);
					}	        			
				}
										
			} else {
				console.log("nglControllerResults getCofators failed!");
			}
		}).error(function(data, status, headers, config){
			console.error("nglControllerResults getCofators http failed!");
		});
	}

	/**
	 * LOAD FROM SESSION FOLDER (../docking/session-files/)
	 */	
	$scope.loadLigands = function(){
		$bestTopFile = $scope.sessionResultPath+$scope.resultElementSelected.bestTopFileName;
		return $scope.addCofactorOrLigandRepresentation($bestTopFile);		
	};

	$scope.loadReferenceFile = function(referenceFileName){

		console.log("loadReferenceFile "+referenceFileName);
		
		var deferred = $q.defer();

		if(referenceFileName != ''){
			$scope.referenceFilePath = $scope.sessionResultPath+referenceFileName;		
			return $scope.addReferenceRepresentation($scope.referenceFilePath);		
		} else {
			deferred.resolve("addProteinRepresentation complete");
			return deferred.promise;				
		}
		
	};

	$scope.removeAllLigands = function(){

		// console.log("nglControllerResults removeAllLigands");		

		$scope.stage.eachComponent( function( comp ){
			if(comp.name.includes("ligand")){
				$components = $scope.stage.getComponentsByName(comp.name);
				$components.list.forEach(element => {
					$scope.stage.removeComponent(element);
				});
			}			
		});		
	}
	
	$scope.selectLigand = function($element, $ligands){

		// console.log("nglControllerResults selectLigand");
		// console.log($element);

		if($scope.sessionId != null){
			
			$bestTopFile = $scope.sessionResultPath+$element.bestTopFileName;
			$scope.addCofactorOrLigandRepresentation(
				$bestTopFile, 
				$scope.removeAllLigands()
			);

			//console.log($scope.stage);
			
			// Update radios
			$scope.resultElementSelected = $element;
			for (let i = 0; i < $ligands.length; i++) {
			
				for (let j = 0; j < $ligands[i].elements.length; j++) {
				
					if($ligands[i].elements[j].$$hashKey == $element.$$hashKey){
						$ligands[i].elements[j]['checked'] = true;
					}else{
						$ligands[i].elements[j]['checked'] = false;
					}
					
				}			
				
			}
			$scope.addReferenceRepresentation($scope.referenceFilePath);
			console.log("test123");
		}else{
			console.error("Could not select");
		}
		
	};

	$scope.parseLigandName = function($name){
		var nameArray = $name.split("_");
		
		var run = "1";
		if(nameArray[2] != undefined) { // Caso undefined, o a valor default "1" resolve o problema
			run = nameArray[2];
		}
		
		var newName = "ligand "+ run; 
		return newName;
	};

	$scope.parseElementName = function($name){
		var nameArrayUndeline = $name.split("_");
		
		// Ex.: Pattern like "ligand_7b879678ff_1_run_3.log", "ligand_7b879678ff_run_3.log", etc
		
		var lastDotIndex = nameArrayUndeline.length-1;
		
		var nameArrayDot = nameArrayUndeline[lastDotIndex].split("."); // 3.log
		var runNumber = nameArrayDot[0]; // 3		
		var newName = "run "+ runNumber; // "run 3"
		return newName;		
				
	};
	
	$scope.getFileIdName = function($name){
		
		// Ex.: Pattern like "ligand_7b879678ff_1_run_3.log", "ligand_7b879678ff_run_3.log", etc
		var nameArrayUndeline = $name.split("_");
		var fileId = nameArrayUndeline[1];		
		return fileId;
	
	};

	$scope.getLigandFromElementName = function($name){
		if($name!=undefined){
			var nameArray = $name.split("_");
			return nameArray[0]+" "+nameArray[2];
		}		
	};

	$scope.parseResultElements = function(originalResultElements){
		
		originalResultElements = originalResultElements.replace(/'/g, '"'); // This is necessary because JSON.parse do not accept sigle quotes 
		originalResultElements = JSON.parse(originalResultElements);
		ligands = originalResultElements;

		let checked = true;

		for (let i = 0; i < ligands.length; i++) {
		
			ligands[i]['show_elements'] = false;

			for (let j = 0; j < ligands[i].elements.length; j++) {
			
				ligands[i].elements[j]['checked'] = checked;
				checked = false;
			}			
			
		}
		return ligands;
	};

	$scope.showLigandRadios = function(ligand){
		ligand['show_elements'] = !ligand['show_elements'];		
	};
	
	$scope.showLigantOptions = function(index, ligand){
		
		if(ligand.show_elements && $scope.showPaginationElement(index)){
			return true;
		} else {
			return false;
		}		
	};

	$scope.addProteinRepresentation = function(path){
		// console.log("nglControllerResults addProteinRepresentation: " +path);		
		
		var deferred = $q.defer();

		$scope.stage.loadFile(path).then(function (o) {
			
			cartoonRepr = o.addRepresentation("cartoon",{ 
					visible: true, 
					color: "chainid" 
			});
			
			licoriceRepr = o.addRepresentation("licorice", {
				visible: true,
				color: "chainid"
				//colorValue: "chainid"
			});
			//lineRepr = o.addRepresentation("line");
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
	
	$scope.addCofactorOrLigandRepresentation = function(path){
		
		// console.log("nglControllerResults addDockRepresentation " +path);
		
		var deferred = $q.defer();

		$scope.stage.loadFile(path).then(function (o) {
			o.addRepresentation(
				"ball+stick", 
				{ 
					multipleBond: "symmetric",
					color: "element"
				});		  
			
			deferred.resolve("addCofactorOrLigandRepresentation complete");
		});

		return deferred.promise;		
	};

	$scope.addReferenceRepresentation = function(path){
		
		// console.log("nglControllerResults addDockRepresentation " +path);
		
		var deferred = $q.defer();

		if(path!=null){
			$scope.stage.loadFile(path).then(function (o) {
				o.addRepresentation(
				"ball+stick", 
				{ 
					multipleBond: "symmetric",
					color: "lime"
				});
				
				deferred.resolve("addReferenceRepresentation complete");
			});
		} else {
			deferred.resolve("addReferenceRepresentation complete");
		}

		return deferred.promise;		
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

	var pocketRadius = 0;
	var pocketRadiusClipFactor = 1;

	var cartoonRepr, backboneRepr, spacefillRepr, neighborRepr, ligandRepr, contactRepr, pocketRepr, labelRepr;

	var struc;
	var neighborSele;
	// var sidechainAttached = false

	// function showFull () {
	$scope.showFull = function() {
		
		console.log("showFull ...");
		
		backboneRepr.setParameters({ radiusScale: 2 });
		spacefillRepr.setVisibility(true);
		licoriceRepr.setVisibility(false);
		lineRepr.setVisibility(true);

		backboneRepr.setVisibility(false);
		ligandRepr.setVisibility(false);
		neighborRepr.setVisibility(false);
		contactRepr.setVisibility(false);
		pocketRepr.setVisibility(false);
		labelRepr.setVisibility(false);

		struc.autoView("ligand");
	};	
	
	// Disable mouse scroll (http://nglviewer.org/ngl/api/file/src/controls/mouse-controls.js.html)
	$scope.stage.mouseControls.remove( "scroll-*" );
	
	// Disable drag and rotate component
	$scope.stage.mouseControls.remove( "drag-ctrl-shift-right" );
	$scope.stage.mouseControls.remove( "drag-ctrl-shift-left" );	
}]);