app.controller("DockingRunController", [ '$rootScope', '$scope', '$http', '$window', 'appInfo',
		function($rootScope, $scope, $http, $window, appInfo) {
			
			$scope.enabledSoftvdw = true; // se false, esse parametro nao sera enviado ao csgrid
			$scope.softvdw = true;
			buttonDockClicked = false;
			persistJobId = '';
			$scope.textSubmitButton = 'Dock!';
			
			// Load 3D viewer type (ngl or jsmol)
			appInfo.appViewer().then(
				function(response){
					$scope.viewerType = response.data.data;
				},
				function(response) {
			        console.error("Could not load the app info");		        
			    }
			);
	
			$scope.viewerSrc = '';
			$scope.isBindingSiteUserDefinedSelected = true;
			$scope.invalideX = false;
			$scope.invalideY = false;
			$scope.invalideZ = false;
			$scope.invXSize = false;
			$scope.invYSize = false;
			$scope.invZSize = false;
			$scope.onlyVS = false;
			
			$scope.bindingSitePreProperties = {
					userDefined : {
						gridCenter : {
							x: 0,
							y: 0,
							z: 0,
						},
						gridSize : {
							x: 10,
							y: 10,
							z: 10,
						},
						rstep : 0.25
					},
					blindDocking : {
						gridCenter : {
							x: 9,
							y: 9,
							z: 9,
						},
						gridSize : {
							x: 9,
							y: 9,
							z: 9,
						}
					},
					test : {
						gridCenter : {
							//x: 16,
							//y: 6,
							//z: 20,
							x: 10.387,
							y: 18.119,
							z: 8.484,
						},
						gridSize : {
							x: 10,
							y: 10,
							z: 10,							
						},
						rstep : 0.25
					}
			};
			
			$scope.algorithmPrecisionPreProperties = {
					standard : {
						naval: 1000000,
						popsize : 750,
						seed : -1985,
						//nrun : 30,
						nrun : 24,
					},
					virtualScreening : {
						naval: 500000,
						popsize : 750,
						seed : -1985,
						//nrun : 10,
						nrun : 12,
					},
					explorer : {
						naval: 0,
						popsize : 0,
						seed : 0,
						nrun : 8,
					}
			}
			
			//sliders inputs on html
			var sliders = ['xGridCenterSlider', 'yGridCenterSlider', 'zGridCenterSlider', 'xGridSizeSlider', 'yGridSizeSlider', 'zGridSizeSlider'];
			sliders.forEach(function(slider){
				//init all sliders with the same value
//				$('#' + slider).slider({
//					precision: 2,
//					value: 8.115
//				});
				
				$scope[slider + 'Num'] = parseInt($("#" + slider).val());
				
				$scope.$watch((slider + 'Num') , function(newValue) {
					//if ($('#xGridCenterInput').is( ":focus" )) {
					//	console.log("is focus!!!!");
					//}
					if (slider == "xGridCenterSlider"){
						$scope.invalideX = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#xGridCenterInput').popover('hide');
							//console.log("popover hide: *"+newValue+"*");
						}catch(e){
							$scope.invalideX = true;
							$('#xGridCenterInput').popover('show');
							//console.log("popover show: *"+newValue+"*");
						}
					}
					if (slider == "yGridCenterSlider"){
						$scope.invalideY = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#yGridCenterInput').popover('hide');
						}catch(e){
							//window.alert(e);
							$scope.invalideY = true;
							$('#yGridCenterInput').popover('show');
						}
					}
					if (slider == "zGridCenterSlider"){
						$scope.invalideZ = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#zGridCenterInput').popover('hide');
						}catch(e){
							//window.alert(e);
							$scope.invalideZ = true;
							$('#zGridCenterInput').popover('show');
						}
					}
					
					if (slider == "xGridSizeSlider"){
						$scope.invXSize = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#xGridSizeInput').popover('hide');
						}catch(e){
							//window.alert(e);
							$scope.invXSize = true;
							$('#xGridSizeInput').popover('show');
						}
					}
					if (slider == "yGridSizeSlider"){
						$scope.invYSize = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#yGridSizeInput').popover('hide');
						}catch(e){
							//window.alert(e);
							$scope.invYSize = true;
							$('#yGridSizeInput').popover('show');
						}
					}
					if (slider == "zGridSizeSlider"){
						$scope.invZSize = false;
						try{
							newValue = parseInt(newValue.toString().replace(/\s/g, ""));
							$('#zGridSizeInput').popover('hide');
						}catch(e){
							//window.alert(e);
							$scope.invZSize = true;
							$('#zGridSizeInput').popover('show');
						}
					}
					
					$('#' + slider).slider('destroy');
					
					$('#' + slider).slider({
						precision: 2,
						value: newValue
					});
					
					$('#' + slider).on("slide", function(slideEvt) {
						$scope.$apply(function () {
							$scope[slider + 'Num'] = parseInt($('#' + slider).val());
						});
					});
					
//					var min = parseInt($('#' + slider).attr('data-slider-min'));
//					var max = parseInt($('#' + slider).attr('data-slider-max'));
//					
//					$scope.canSubmit = true;
//					if(newValue > max){
//						//$scope[slider + 'Num'] = max;
//						$scope.canSubmit = false;
//					} 
//					
//					if(newValue < min){
//						//$scope[slider + 'Num'] = min;
//						$scope.canSubmit = false;
//					}
//					
					if($scope.isBindingSiteBlindDockingSelected){
						$scope.isBindingSiteUserDefinedSelected = (($scope.xGridCenterSliderNum != $scope.bindingSitePreProperties.blindDocking.gridCenter.x) ||
								  ($scope.yGridCenterSliderNum != $scope.bindingSitePreProperties.blindDocking.gridCenter.y) ||
								  ($scope.zGridCenterSliderNum != $scope.bindingSitePreProperties.blindDocking.gridCenter.z) ||

								  ($scope.xGridSizeSliderNum != $scope.bindingSitePreProperties.blindDocking.gridSize.x) ||
								  ($scope.yGridSizeSliderNum != $scope.bindingSitePreProperties.blindDocking.gridSize.y) ||
								  ($scope.zGridSizeSliderNum != $scope.bindingSitePreProperties.blindDocking.gridSize.z));
						
						$scope.isBindingSiteBlindDockingSelected = !$scope.isBindingSiteUserDefinedSelected;
					}
					
					
					if($scope.isBindingSiteTestSelected) {
						$scope.isBindingSiteUserDefinedSelected = (($scope.xGridCenterSliderNum != $scope.bindingSitePreProperties.test.gridCenter.x) ||
								  ($scope.yGridCenterSliderNum != $scope.bindingSitePreProperties.test.gridCenter.y) ||
								  ($scope.zGridCenterSliderNum != $scope.bindingSitePreProperties.test.gridCenter.z) ||

								  ($scope.xGridSizeSliderNum != $scope.bindingSitePreProperties.test.gridSize.x) ||
								  ($scope.yGridSizeSliderNum != $scope.bindingSitePreProperties.test.gridSize.y) ||
								  ($scope.zGridSizeSliderNum != $scope.bindingSitePreProperties.test.gridSize.z));
						
						$scope.isBindingSiteTestSelected = !$scope.isBindingSiteUserDefinedSelected;
					}
					
					
					if($scope.viewerSrc != ''){
						//document.getElementById('dockingView').contentWindow.load();
						//console.log("setBox("+$scope.xGridCenterSliderNum+" ,"+$scope.yGridCenterSliderNum+" ,"+ $scope.zGridCenterSliderNum+" ,"+ $scope.xGridSizeSliderNum+" ,"+ $scope.yGridSizeSliderNum+" ,"+ $scope.zGridSizeSliderNum +")");
						document.getElementById('dockingView').contentWindow.setBox($scope.xGridCenterSliderNum, $scope.yGridCenterSliderNum, $scope.zGridCenterSliderNum, $scope.xGridSizeSliderNum, $scope.yGridSizeSliderNum, $scope.zGridSizeSliderNum);
					}
					
					$scope.points = Math.floor((2*$scope.xGridSizeSliderNum/$scope.rstep)+1)*Math.floor((2*$scope.yGridSizeSliderNum/$scope.rstep)+1)*Math.floor((2*$scope.zGridSizeSliderNum/$scope.rstep)+1);
				 });
				
				$scope.$watch(('rstep') , function(newValue, oldValue) {
					$scope.points = Math.floor((2*$scope.xGridSizeSliderNum/newValue)+1)*Math.floor((2*$scope.yGridSizeSliderNum/newValue)+1)*Math.floor((2*$scope.zGridSizeSliderNum/newValue)+1);
				});
				
				$scope.$watchGroup(['view3DProtein', 'view3DCofactors'] , function(newValues, oldValues) {
					
					if($scope.proteinInput != null && $scope.ligandInput != null){
						if($scope.view3DProtein){
							document.getElementById('dockingView').contentWindow.showProtein();
						} else {
							document.getElementById('dockingView').contentWindow.hideProtein();
						}
						
						if($scope.view3DCofactors){
							document.getElementById('dockingView').contentWindow.showCofactors();
						} else {
							document.getElementById('dockingView').contentWindow.hideCofactors();
						}
					}
				});
				
				// JSMol
				$scope.$watchGroup(['proteinInput','cofactorInput'] , function(newValues, oldValues) {
					if($scope.$parent.proteinInput != null){
						if(oldValues == null || oldValues[0] == null || oldValues[1] == null || (newValues[0].codedName != oldValues[0].codedName) || (newValues[1] != oldValues[1])){
							
							if($scope.$parent.proteinInput.codedName_pdb != null){
								var src = "apps/docking/3D-viewer/show3D.php?type=DOCKING&file=" + $scope.$parent.proteinInput.codedName_pdb;
							} else {
								var src = "apps/docking/3D-viewer/show3D.php?type=DOCKING&file=" + $scope.$parent.proteinInput.codedName;
							}
							
							sliders.forEach(function(slider){
								src+= "&" + slider.replace('Slider', '') + "=" + $scope[slider + 'Num'];
							});
							$scope.viewerSrc = src;
						}
					}
					if($scope.$parent.cofactorInput != null){
						$scope.viewerSrc+= "&cofactors=" + JSON.stringify($scope.$parent.cofactorInput);
					}
				});
				
				$scope.$watchGroup(['naval', 'popsize', 'seed', 'nrun'], function(newValues) {
					if($scope.isAlgorithmPrecisionStandardSelected){
						$scope.isAlgorithmPrecisionExplorerSelected = (newValues[0] != $scope.algorithmPrecisionPreProperties.standard.naval) ||
							(newValues[1] != $scope.algorithmPrecisionPreProperties.standard.popsize) ||
							(newValues[2] != $scope.algorithmPrecisionPreProperties.standard.seed) ||
							(newValues[3] != $scope.algorithmPrecisionPreProperties.standard.nrun);
						
						$scope.isAlgorithmPrecisionStandardSelected = !$scope.isAlgorithmPrecisionExplorerSelected;
					}
					
					if($scope.isAlgorithmPrecisionVirtualScreeningSelected){
						$scope.isAlgorithmPrecisionExplorerSelected = (newValues[0] != $scope.algorithmPrecisionPreProperties.virtualScreening.naval) ||
						(newValues[1] != $scope.algorithmPrecisionPreProperties.virtualScreening.popsize) ||
						(newValues[2] != $scope.algorithmPrecisionPreProperties.virtualScreening.seed) ||
						(newValues[3] != $scope.algorithmPrecisionPreProperties.virtualScreening.nrun);
						
						$scope.isAlgorithmPrecisionVirtualScreeningSelected = !$scope.isAlgorithmPrecisionExplorerSelected;
					}
				});
			});
			
			/** Docking NGL App - begin */
			
			$scope.srcViewerDockingNgl = "";
			$scope.nglDockingTotalGridPointValid = true;
			$scope.nglGrid = null;
			
			// Watch and load ngl iframe
			$scope.$watchGroup(['proteinInput', 'cofactorInput', 'ligandInput'] , function(newValues, oldValues) {
			
				if( ($scope.$parent.proteinInput != null) && ($scope.$parent.ligandInput != null) ){					
					$scope.loadNgl();					
				}
				
			});
			
			// Load ngl iframe
			$scope.loadNgl = function(){
				console.log("DockingController loadNgl - selectedTab="+$scope.$parent.selectedTab+", proteinInput=" + $scope.$parent.proteinInput + ", cofactorInput=" + $scope.$parent.cofactorInput + ", ligandInput=" + $scope.$parent.ligandInput);
				
				console.log("watch $parent.proteinInput:");
				console.log($scope.$parent.proteinInput);
				
				// Checking cofactor
				var useCofactor = false;
				if($scope.$parent.cofactorInput != null){
					useCofactor = true;							
				}
				
				// Checking if protein is test file
				var proteinIsTestFile = false;
				if($scope.$parent.proteinInput.isTestFile != undefined && $scope.$parent.proteinInput.isTestFile == true){
					proteinIsTestFile = true;
				}				
					
				// Build url (dummyVar used only to force the angular to reload the iframe (https://stackoverflow.com/questions/30830406/how-to-refresh-iframe-url?rq=1))
				var d = new Date(); 	
				var t = d.getTime();
				$iframeUrl = "apps/docking/3D-viewer-ngl/view/nglViewerDocking.php?use_cofactor="+useCofactor+"&protein_is_test_file="+proteinIsTestFile+"&dummyVar="+t;
				console.log("DockingController open3DModalNGL watchGroup - Loading iframe: "+$iframeUrl);
					
				// Load url
				$scope.srcViewerDockingNgl = $iframeUrl;
				
			}
			
			// Get message from iframe (grid values, total, etc.)
			$window.addEventListener('message', function(e) {
				
				if(e.data['total_grid_points'] != undefined){
					
					// console.log("NGL Docking - valid total grid points: "+ e.data['total_grid_points']['valid']);
					
					if(e.data['total_grid_points']['valid']==false){	
						
						$scope.$apply(function() {
							$scope.nglDockingTotalGridPointValid = false;
						});
						
					}else{
						
						$scope.$apply(function() {
							$scope.nglDockingTotalGridPointValid = true;
						});
						
					}
					
				}
				
				if(e.data['grid'] != undefined){					
					
					$scope.$apply(function() {
						$scope.nglGrid = [];
						$scope.nglGrid = e.data['grid'];
					});
					
					// Debug
					// console.log("Received grid message:");
					// console.log(e.data);					
					
				}
								
		    });
			
			/** Docking NGL App - end */
			
			$scope.$watch(('jobName') , function(newValue) {
				if(newValue != null){
					$scope.jobName= newValue.replace(/[^a-zA-Z0-9_]/g,'X');
				}
			});
			
			$scope.getLigandTotalStructures = function() {
				var total = 0;
				if($scope.$parent.ligandInput != null) {
					angular.forEach($scope.$parent.ligandInput, function(ligand){
						total += parseInt(ligand.validStructure);
					});
				}
				return total;
			};
			
			$scope.$watch(('$parent.ligandInput') , function(newValue) {
				if ($scope.getLigandTotalStructures() > 1){
					if($scope.getLigandTotalStructures() > 100){
						$scope.onlyVS = true;
					}else{
						$scope.onlyVS = false;
					}
					$scope.algorithmPrecisionVirtualScreeningButton();
				}else if ($scope.getLigandTotalStructures() == 1){
					$scope.algorithmPrecisionStandardButton();
					$scope.onlyVS = false;
				//}else if  ($scope.getLigandTotalStructures() > 100){
				//	$scope.virtualscreening=true;
				//}else if  ($scope.getLigandTotalStructures() <= 100){
				//	$scope.virtualscreening=false;
				}
			});
			
			$scope.getCofactorTotalStructures = function() {
				var total = 0;
				if($scope.$parent.cofactorInput != null) {
					angular.forEach($scope.$parent.cofactorInput, function(cofactor){
					     total += parseInt(cofactor.validStructure);
					});
				}
				return total;
			};
			
			$scope.bindingSiteUserDefinedButton = function() {
				$scope.xGridCenterSliderNum = $scope.bindingSitePreProperties.userDefined.gridCenter.x;
				$scope.yGridCenterSliderNum = $scope.bindingSitePreProperties.userDefined.gridCenter.y;
				$scope.zGridCenterSliderNum = $scope.bindingSitePreProperties.userDefined.gridCenter.z;
				
				$scope.xGridSizeSliderNum = $scope.bindingSitePreProperties.userDefined.gridSize.x;
				$scope.yGridSizeSliderNum = $scope.bindingSitePreProperties.userDefined.gridSize.y;
				$scope.zGridSizeSliderNum = $scope.bindingSitePreProperties.userDefined.gridSize.z;
				
				$scope.rstep = $scope.bindingSitePreProperties.userDefined.rstep;
				
				$scope.isBindingSiteUserDefinedSelected = true;
				$scope.isBindingSiteBlindDockingSelected = false;
				$scope.isBindingSiteTestSelected = false;
			};
			
			
			$scope.bindingSiteBlindDockingButton = function() {
				$http.post(
						'apps/docking/blind-docking/calc-blind-docking.php',
			    		{
							params:{
								'prepName' : $scope.$parent.proteinInput.codedName_pdb
							}
			    		}
			    ).success(function(gridConf){
			    	
				    	//setting values from blind-docking to template
				    	$scope.bindingSitePreProperties.blindDocking.gridSize.x = parseFloat(gridConf.gridSize);
				    	$scope.bindingSitePreProperties.blindDocking.gridSize.y = parseFloat(gridConf.gridSize);
				    	$scope.bindingSitePreProperties.blindDocking.gridSize.z = parseFloat(gridConf.gridSize);
				    	
				    	$scope.bindingSitePreProperties.blindDocking.gridCenter.x = parseFloat(gridConf.X);
				    	$scope.bindingSitePreProperties.blindDocking.gridCenter.y = parseFloat(gridConf.Y);
				    	$scope.bindingSitePreProperties.blindDocking.gridCenter.z = parseFloat(gridConf.Z);
				    	$scope.rstep = parseFloat(gridConf.rstep);
				    
					$scope.xGridCenterSliderNum = $scope.bindingSitePreProperties.blindDocking.gridCenter.x;
					$scope.yGridCenterSliderNum = $scope.bindingSitePreProperties.blindDocking.gridCenter.y;
					$scope.zGridCenterSliderNum = $scope.bindingSitePreProperties.blindDocking.gridCenter.z;
					
					$scope.xGridSizeSliderNum = $scope.bindingSitePreProperties.blindDocking.gridSize.x;
					$scope.yGridSizeSliderNum = $scope.bindingSitePreProperties.blindDocking.gridSize.y;
					$scope.zGridSizeSliderNum = $scope.bindingSitePreProperties.blindDocking.gridSize.z;
	
					$scope.isBindingSiteUserDefinedSelected = false;
					$scope.isBindingSiteBlindDockingSelected = true;
					$scope.isBindingSiteTestSelected = false;
				
			    });	
			};
			
			$scope.bindingSiteTestButton = function() {
				$scope.xGridCenterSliderNum = $scope.bindingSitePreProperties.test.gridCenter.x;
				$scope.yGridCenterSliderNum = $scope.bindingSitePreProperties.test.gridCenter.y;
				$scope.zGridCenterSliderNum = $scope.bindingSitePreProperties.test.gridCenter.z;
				
				$scope.xGridSizeSliderNum = $scope.bindingSitePreProperties.test.gridSize.x;
				$scope.yGridSizeSliderNum = $scope.bindingSitePreProperties.test.gridSize.y;
				$scope.zGridSizeSliderNum = $scope.bindingSitePreProperties.test.gridSize.z;
				
				$scope.rstep = $scope.bindingSitePreProperties.test.rstep;
				
				$scope.isBindingSiteUserDefinedSelected = false;
				$scope.isBindingSiteBlindDockingSelected = false;
				$scope.isBindingSiteTestSelected = true;
			};
			
			$scope.algorithmPrecisionStandardButton = function() {
				$scope.naval = $scope.algorithmPrecisionPreProperties.standard.naval;
				$scope.popsize = $scope.algorithmPrecisionPreProperties.standard.popsize;
				$scope.seed = $scope.algorithmPrecisionPreProperties.standard.seed;
				$scope.nrun = $scope.algorithmPrecisionPreProperties.standard.nrun;
				
				$scope.isAlgorithmPrecisionStandardSelected = true;
				$scope.isAlgorithmPrecisionVirtualScreeningSelected = false;
				$scope.isAlgorithmPrecisionExplorerSelected = false;
			};
			
			$scope.algorithmPrecisionVirtualScreeningButton = function() {
				$scope.naval = $scope.algorithmPrecisionPreProperties.virtualScreening.naval;
				$scope.popsize = $scope.algorithmPrecisionPreProperties.virtualScreening.popsize;
				$scope.seed = $scope.algorithmPrecisionPreProperties.virtualScreening.seed;
				$scope.nrun = $scope.algorithmPrecisionPreProperties.virtualScreening.nrun;
				
				$scope.isAlgorithmPrecisionStandardSelected = false;
				$scope.isAlgorithmPrecisionVirtualScreeningSelected = true;
				$scope.isAlgorithmPrecisionExplorerSelected = false;
			};
			
			$scope.algorithmPrecisionExplorerButton = function() {
				$scope.naval = $scope.algorithmPrecisionPreProperties.explorer.naval;
				$scope.popsize = $scope.algorithmPrecisionPreProperties.explorer.popsize;
				$scope.seed = $scope.algorithmPrecisionPreProperties.explorer.seed;
				$scope.nrun = $scope.algorithmPrecisionPreProperties.explorer.nrun;
				
				$scope.isAlgorithmPrecisionStandardSelected = false;
				$scope.isAlgorithmPrecisionVirtualScreeningSelected = false;
				$scope.isAlgorithmPrecisionExplorerSelected = true;
			};
			
			$scope.validateEmail = function($email) {
				  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
				  return emailReg.test( $email ) && $email != null && $email != '';
			}
			
			$scope.validatePoputationSize = function($value) {
				  if($value>1000){
					  //return false;
					  console.log("true");
				  }else{
					  //return true;
					  console.log("false!");
				  }
			}
			
			$scope.addUserEmailInput = function($index){
				$scope.user.emails.push({id: $index + 1, email : ''});
			};
			
			$scope.addEmailToArray = function($index, email) {
					if(email != ''){
						$scope.user.emails[$index] = email;
					}
			};
			
			$scope.removeEmailInput = function($index){
				$scope.user.emails.splice($index, 1);
			};
			
			$scope.focusFunction = function(){
				if (($('#xGridCenterInput').is( ":focus" ))&&(!$scope.invalideX)) {
					$('#xGridCenterInput').popover('hide');
					console.log("hide no focus");
				}
				if (($('#yGridCenterInput').is( ":focus" ))&&(!$scope.invalideY)) {
					$('#yGridCenterInput').popover('hide');
					console.log("hide no focus");
				}
				if (($('#zGridCenterInput').is( ":focus" ))&&(!$scope.invalideZ)) {
					$('#zGridCenterInput').popover('hide');
					console.log("hide no focus");
				}
				if (($('#xGridSizeInput').is( ":focus" ))&&(!$scope.invXSize)) {
					$('#xGridSizeInput').popover('hide');
					console.log("hide no focus");
				}
				if (($('#yGridSizeInput').is( ":focus" ))&&(!$scope.invYSize)) {
					$('#yGridSizeInput').popover('hide');
					console.log("hide no focus");
				}
				if (($('#zGridSizeInput').is( ":focus" ))&&(!$scope.invZSize)) {
					$('#zGridSizeInput').popover('hide');
					console.log("hide no focus");
				}	
			}
			
			//$scope.isVirtualScreening = function(){
			//	
			//	return $scope.virtualScreening;
			//}
						
			$scope.checkCanSubmit = function(){
				
				var canSubmit = true;
				
				if($scope.viewerType == 'jsmol'){
					
					sliders.forEach(function(slider){
						
						if (slider != "xGridCenterSlider" && slider != "yGridCenterSlider" && slider != "zGridCenterSlider"){
							if(($scope[slider + 'Num'] != null && $scope[slider + 'Num'] != '') || $scope[slider + 'Num'] == 0 ){
								var min = parseInt($('#' + slider).attr('data-slider-min'));
								var max = parseInt($('#' + slider).attr('data-slider-max'));
								
								if($scope[slider + 'Num'] > max || $scope[slider + 'Num'] < min){
									canSubmit = false;
								}
							} else {
								canSubmit = false;
							}
						}
						
						if (slider == "xGridCenterSlider" || slider == "yGridCenterSlider" || slider == "zGridCenterSlider"){
							if(($scope[slider + 'Num'] == null || $scope[slider + 'Num'] == '') && ($scope[slider + 'Num'] != 0)){
								canSubmit = false;
							}
						}
					});
					
					if($scope.rstep == null || $scope.rstep == ''){
						canSubmit = false;
					}
					
				} else if($scope.viewerType == 'ngl'){
					
					if( ($scope.nglGrid == undefined) || ($scope.nglGrid == null) || ($scope.nglGrid.hasError)){
						canSubmit = false;						
					} else if( ($scope.nglGrid.rstep == null) || ($scope.nglGrid.rstep == '') ){
						canSubmit = false;
					} else if($scope.nglGrid.rstep < 0){
						canSubmit = false;
					}
				}
				
				if($scope.acceptTermsCheckBox == null || !$scope.acceptTermsCheckBox.value){
					canSubmit = false;
				}

				if (
						($scope.naval == null || $scope.naval == '') ||
						($scope.popsize == null || $scope.popsize == '') ||
						($scope.nrun == null || $scope.nrun == '') ||
						($scope.seed == null || $scope.seed == '') ||
						($scope.jobName == null || $scope.jobName == '')
				) {
					canSubmit = false;
				}
				
				if( ($scope.rstep<0) && ($scope.rstep>0.5) ){
					canSubmit = false;
				}
				
				if($scope.naval > 1000000 || $scope.naval < 50000){
					canSubmit = false;
				}
				
				if($scope.nrun < 8 || $scope.nrun > 24 || $scope.nrun < 0){
					canSubmit = false;
				}
				
				if($scope.popsize > 1000 || $scope.popsize < 100){
					canSubmit = false;
				}
				
				if($scope.seed > 0 ){ // Somente numeros negativos
					canSubmit = false;
				}
				
				if($scope.points > 950000){
					canSubmit = false;
				}
				
				if ($scope.getLigandTotalStructures() < 1){
					canSubmit = false;
				}

				if (($scope.$parent.proteinInput == null) || ($scope.$parent.proteinInput == "")){
					canSubmit = false;
				}
				
				angular.forEach($scope.user.emails, function($emailInfo){
					if(!$scope.validateEmail($emailInfo.email)){
						canSubmit = false;
					}
				});
				
				if($scope.nglDockingTotalGridPointValid==false){
					canSubmit = false;
				}

				if((buttonDockClicked == true) && (persistJobId == $scope.jobId)){
					canSubmit = false;
				}
				
				return canSubmit;
				 
			}
			
			
			$scope.submitJobWithVerification = function (){
				var xgridCenter = 0;
				var ygridCenter = 0;
				var zgridCenter = 0;
				
				$bootboxMessage = "";

				persistJobId = $scope.jobId;
				buttonDockClicked = true;
				$scope.textSubmitButton = 'Submitted';
				
				if($scope.viewerType == 'jsmol'){
					xgridCenter = $scope.xGridCenterSliderNum;
					ygridCenter = $scope.yGridCenterSliderNum;
					zgridCenter = $scope.zGridCenterSliderNum;
				} else if($scope.viewerType == 'ngl'){
					xgridCenter = parseFloat($scope.nglGrid.gridCenter.x);
					ygridCenter = parseFloat($scope.nglGrid.gridCenter.y);
					zgridCenter = parseFloat($scope.nglGrid.gridCenter.z);
				}
				
				if(xgridCenter == 0 && ygridCenter == 0 && zgridCenter == 0){
					
					// Note: The style "font-family:none" was used bellow just to apply the bold style on the texts (like 'X = 0'). Otherwise it wouldn't work.
					$bootboxTitle = "<div align=\"center\"><b style=\"font-family:none\">Attention!</b></div>";
					$bootboxMessage = 
						"<style>.submitBootBox{background-color: #209A9A; color:white; border-color:#0a9896;}</style>" +
						"<div align=\"justify\">The grid center is defined as <b style=\"font-family:none\">X = "+xgridCenter+"</b>, <b style=\"font-family:none\">Y = "+ygridCenter+"</b>, and <b style=\"font-family:none\">Z = "+zgridCenter+"</b>. " +
					 	"Check carefully the center of the search space to avoid predicting docking poses outside the binding site of interest.</div>"
						
					bootbox.confirm(
						{ 
							title: $bootboxTitle,
							message: $bootboxMessage,
							buttons: {
						        confirm: {
						            label: 'Dock!',
						            className: 'submitBootBox'
						        },
						        cancel: {
						            label: 'Cancel',
						            className: 'btn-warning'
						        }
						    },
						    callback: function (result) {						 
								if(result){
									$scope.submitJob();
								}
						    }
						}
					);	
						
				}else{
					$scope.submitJob();
				}	
				
			}
			
			$scope.submitJob = function() {
						
				var formData = new FormData();
				
				if($scope.viewerType == 'jsmol'){
					
					sliders.forEach(function(slider){
						if (slider != "xGridCenterSlider" && slider != "yGridCenterSlider" && slider != "zGridCenterSlider"){
							if($scope[slider + 'Num'] != null && $scope[slider + 'Num'] != ''){
								var min = parseInt($('#' + slider).attr('data-slider-min'));
								var max = parseInt($('#' + slider).attr('data-slider-max'));
								
								if($scope[slider + 'Num'] <= max && $scope[slider + 'Num'] >= min){
									formData.append(slider.replace('Slider', ''), $scope[slider + 'Num']);
								}
							}
						}else{
							formData.append(slider.replace('Slider', ''), $scope[slider + 'Num']);
						}
					});
					
					if ($scope.rstep != null && $scope.rstep != '') {
						formData.append('rstep', $scope.rstep);
					}
					
				} else if($scope.viewerType == 'ngl'){
					
					formData.append('xGridCenter', $scope.nglGrid.gridCenter.x);
					formData.append('yGridCenter', $scope.nglGrid.gridCenter.y);
					formData.append('zGridCenter', $scope.nglGrid.gridCenter.z);
					
					formData.append('xGridSize', $scope.nglGrid.gridSize.x);
					formData.append('yGridSize', $scope.nglGrid.gridSize.y);
					formData.append('zGridSize', $scope.nglGrid.gridSize.z);
					
					formData.append('rstep', $scope.nglGrid.rstep);

					console.log($scope.nglGrid);
					
				} else {
					console.error("Could not submit cause viewer was not defined properly!");
				}
				
				if ($scope.naval != null && $scope.naval != '') {
					formData.append('naval', $scope.naval);
				}

				if ($scope.popsize != null && $scope.popsize != '') {
					formData.append('popsize', $scope.popsize);
				}

				if ($scope.nrun != null && $scope.nrun != '') {
					formData.append('nrun', $scope.nrun);
				}

				if ($scope.seed != null && $scope.seed != '') {
					formData.append('seed', $scope.seed);
				}
				
				if ($scope.user.emails != null && $scope.user.emails.length != 0) {
					formData.append('emails', JSON.stringify($scope.user.emails));
				}
				
				formData.append('jobName', $scope.jobName.replace(/[^a-zA-Z0-9_]/g,'X'));
				
				var proteinList = [];
				proteinList.push($scope.$parent.proteinInput.codedName);
				formData.append('proteinsList', JSON.stringify(proteinList));
				
				var ligandList = [];
				angular.forEach($scope.$parent.ligandInput, function(ligand){
					console.log(ligand.fileId);
					ligandList.push(ligand.fileId);
				});
				formData.append('ligandsList', JSON.stringify(ligandList));
				
				var cofactorList = [];
				if($scope.$parent.cofactorInput != null){
					angular.forEach($scope.$parent.cofactorInput, function(cofactor){
						cofactorList.push(cofactor.fileId);
					});
					formData.append('cofactorList', JSON.stringify(cofactorList));
				}
				
				if($scope.subscribe != null && $scope.subscribe.value){
					formData.append('subscribe', $scope.subscribe.value);
				}
				
				console.log("submitJob - formData values: ");
				for (var pair of formData.entries()) {
				    console.log(pair[0]+ ', ' + pair[1]); 
				}
				
				if($scope.enabledSoftvdw == true){
					formData.append('softvdw', $scope.softvdw);
				}
				
				// Protein target info
				formData.append('proteinTargetInfo', JSON.stringify($rootScope.proteinTargetInfo));
				
				/*
				$.ajax({
					url : 'apps/docking/run.php',
					dataType : 'text',
					cache : false,
					contentType : false,
					processData : false,
					data : formData,
					type : 'post',
					success : function(phpScriptResponse) {
						var jsonResponse = JSON.parse(phpScriptResponse);
						if (jsonResponse.operationStatus == "submitted") {

							$scope.$apply(function() {
								$scope.$parent.$parent.job.id = jsonResponse.portalId;
								$scope.$parent.$parent.job.proteinInput.push($scope.$parent.proteinInput.codedName);
								$scope.$parent.$parent.job.ligandInput = ligandList;
								$scope.$parent.$parent.job.cofactorsInput = cofactorList;
								
								$scope.$watch(('$parent.$parent.job.id') , function(newValue, oldValue) {
									if($scope.$parent.$parent.job.id != null && $scope.$parent.$parent.job.id != ""){
										$('#goToResults').submit();
									}
								});
							});
						} else if (jsonResponse.operationStatus == "error") {
							bootbox.alert({
								title: "Submission error", 
								message: "Please try again. If the problem persists, contact dockthor@lncc.br"}
							);
						}
					}
				});*/
				
				$http({
					method: 'POST',
					url: 'apps/docking/run.php',
					headers: {
						'Content-Type': undefined
					},
					data: formData,					
				}).success(function (response) {
					if (response.operationStatus == "submitted") {
						
						$scope.$parent.$parent.job.id = response.portalId;
						$scope.$parent.$parent.job.proteinInput.push($scope.$parent.proteinInput.codedName);
						$scope.$parent.$parent.job.ligandInput = ligandList;
						$scope.$parent.$parent.job.cofactorsInput = cofactorList;
						
						$scope.$watch(('$parent.$parent.job.id') , function(newValue, oldValue) {
							if($scope.$parent.$parent.job.id != null && $scope.$parent.$parent.job.id != ""){
								$('#goToResults').submit();
							}
						});
						
					} else if (response.operationStatus == "error") {
						bootbox.alert({
							title: "Submission error", 
							message: "Please try again. If the problem persists, contact dockthor@lncc.br"}
						);
					}
					
				}).error(function (response) {
					console.error("Error on send job to run");
					console.error(response);
					
					var detailMessage = "";					
					if(response.problem == 'protein'){
						detailMessage = "submit the "+response.problem+" file and";
					} else if (response.problem == 'ligand' || response.problem == 'cofactor'){
						detailMessage = "submit the "+response.problem+" file(s) and";
					}
					
					bootbox.alert({
						title: "Submission error", 
						message: "An unexpected error was found. Please "+detailMessage+" try again. If the problem persists contact dockthor@lncc.br. <br><br><b>Attention</b>: do not open the DockThor portal in multiple tabs at the same time."}
					);
				});	
			};
			
} ]);
