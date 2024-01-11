app.controller("ProteinController", [ '$rootScope', '$scope', '$http', '$q', 'appInfo', 
	function($rootScope, $scope, $http, $q, appInfo) {
			
			$scope.sendToDockStatus = "";
			$scope.fileTestPath = "1hpv_protein.pdb";
			$scope.fileUploadStatusShow = false;
			
			$scope.predefinedFilePath = null;
			$scope.predefinedFileType = null;
			
			$scope.level1Label = 'Target';
			$scope.level2Label = 'Variant';
			$scope.level3Label = 'Structure';
			
			// Usado para estatisticas
			$scope.proteinTargetInfoTemp = null;
			$rootScope.proteinTargetInfo = null;
			
			$(function () {
				$('[data-toggle="popover"]').popover();
			});
			
			// Load 3D viewer type
			appInfo.appViewer().then(function(response){
				$scope.viewerType = response.data.data;
				// console.log($scope.viewerType);
		    });
			
			/*** Prepared files app - BEGIN ***/
			
			$scope.preparedResoucesLabel = "COVID-19 resources";
			$scope.preparedResoucesType = "protein";
			
			/*
			$scope.initPreparedResourcesApp = function(){
				
				$scope.preparedResoucesLabel = "COVID-19 resources";
				
				$scope.loadPreparedResourceSuccess = null;
			
				$scope.preparedFiles = [];
				
				$scope.loadPreparedResources();
				
				$scope.selectedLevel1 = null;
				$scope.selectedLevel2 = null;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};
			
			$scope.loadPreparedResources = function() {
				
				$http.post(
					'apps/docking/action/protein-action.php',
					{	
						params:{
							action: 'LOAD-PREPARED-RESOURCES'
						}
					}
				).success(function(response){
					$scope.preparedResources = response.data;
					$scope.loadPreparedResourceSuccess = true;	
				}).error(function(response, status) {
					$scope.loadPreparedResourceSuccess = false;
					console.error("Prepared resource error - An internal error occurred: " + status);
				});
				
			};
			*/
			/* Example data: 
			$scope.preparedFiles = [
				{					
					"name": "Main protease",					
					"elements": [
						{
							"name": "PDB code 6w63",
							"elements": [
								{
									"name": "Wild type",
									"fileName": "6w63_dimer_protein.in"			
								},
							],
						},
						{
							"name": "PDB code 6y2F",
							"elements": [
								{
									"name": "Wild type",
									"fileName": "6w63_dimer_protein.in"	
								},
							],
						},
					],
				},
			];
			*/
			/*
			$scope.selectLevel1 = function(value){
				$scope.selectedLevel1=value;
				$scope.selectedLevel2 = null;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};			
			
			$scope.selectLevel2 = function(value){
				$scope.selectedLevel2=value;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};			
			
			$scope.selectLevel3 = function(value){
				$scope.selectedLevel3 = value;
				$scope.disableInfoButton = false;
				$scope.disableSelectButton = false;
			};
			*/
			
			$scope.loadPreparedResource = function(selectedresource, queue){
				console.log("loadPrepareceResource for protein: "+selectedresource);
				
				// $scope.filePath = resourcefiles.elements.files[0].path;
				$scope.filePath = selectedresource;
				
				var isUploadFile = false;
				var isPredefinedFile = false;		
				
				// Check the type of protein file (upload or predefined file)
				if(queue.length==1){ // uploaded file
					isUploadFile = true;
					isPredefinedFile = false;					
				} else if($scope.protein!=undefined && $scope.protein.isPredefinedFile){ // predefined file
					isUploadFile = false;
					isPredefinedFile = true;
				}
				
				// Usado para estatisticas
				$scope.proteinTargetInfoTemp = {							
					id: $scope.filePath, // this must be rethought (repensado)					
					path: $scope.filePath,
				};
				
				if(isUploadFile || isPredefinedFile){
					bootbox.confirm({ 
						size: "small",
						message: "The protein file will be replaced by the target file. Are you sure?",
						callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/
							
							if(result){
							  
							  if(isUploadFile){
								  file = queue[0];								  
								  file.$cancel();
								  $scope.$parent.$parent.$parent.protein = null;
								  $rootScope.$emit("proteinEditorReset", {});
								  $scope.$parent.proteinInput = null;	
							  } else if(isPredefinedFile) {
								  $scope.$apply(function() {
									  $scope.protein = null;
									  $rootScope.$emit("proteinEditorReset", {});	
									  $scope.$parent.proteinInput = null;
								  });
							  }							  
							  // Load target file here
							  $scope.selectPredefinedFile('target', $scope.filePath);							  
						  }
						}
					});	
				} else {
					// Load target file here
					$scope.selectPredefinedFile('target', $scope.filePath);		
				}
			};
			
			// $scope.initPreparedResourcesApp();
			
			/*** Prepared files app - END ***/
	
			$scope.proteinInit = function(){
				$rootScope.$emit("proteinEditorReset", {});
			};
			
			$scope.selectTestFile = function(fileTestPath){
				
				if(($scope.$$childHead.queue.length>0)||($scope.predefinedFileType=='target')){
					bootbox.confirm({ 
						size: "small",
						message: "The protein file will be replaced by the test file. Are you sure?",
						callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
							if(result){
								//$scope.initPreparedResourcesApp();
								$scope.selectPredefinedFile('test', fileTestPath);
								$scope.$$childHead.queue = [];
								
								// Usado para estatisticas
								$rootScope.proteinTargetInfo = null;
								$scope.proteinTargetInfoTemp = null;
							}
						}
					});
				} else {
					$scope.selectPredefinedFile('test', fileTestPath);	
				}
			};
			
			$scope.selectUploadFile = function(){				  
				$scope.$$childHead.queue = [];
				$scope.protein = null;
				
				//for covid statistics
				$rootScope.proteinTargetInfo = null; 
				$scope.proteinTargetInfoTemp = null;
			};
			
			$scope.selectPredefinedFile = function(fileType, filePath) {
				
				/*
				$scope.protein = {
					name : "1caq.pdb",
					isPredefinedFile : true,
					prepared : false,
					size: "166 KB",
					codedName: "1caq.pdb",
					codedName_pdb: "1caq_prep.pdb"
				};*/
				
				$http.post(
					'apps/docking/action/protein-action.php',
					{	
						'params':{
							'action': 'GET-PREDEFINED-FILE-INFO',
							'type': fileType,
							'filePath': filePath,
						}
					}
				).success(function(response){
					// console.log(response);
					
					$scope.protein = response.file; // Utilizado para montar a tabela na tela
					$scope.predefinedFilePath = filePath;
					$scope.predefinedFileType = fileType;					
					
				}).error(function(response, status) {
					console.log("erro " + status);
				});
				
			};
			
			$scope.sendFilePredefinedButton = function(){
				
				//reset
				$("#proteinInput").val("");					
				$rootScope.$emit("proteinEditorReset", {});
				
				if($scope.predefinedFileType=='test'){
					
					$http.post(
							'apps/docking/action/protein-action.php',
							{	
								'params':{
									'action': 'PREPARE-TEST-FILE',
									'filePath': $scope.predefinedFilePath,
									'type' : $scope.predefinedFileType,
								}
							}
						).success(function(response){
							if (response.operationStatus=='SUCCESS'){
								
								$scope.protein.prepared = true;
								$scope.showRotbEditor = true;
								$rootScope.$emit("getJsonFromPdbFIleAndChainFiles", $scope.protein.codedName, response.chains);								
								
							} else {
								$scope.protein.error = response.errorMessage;
							}
							$scope.preparingFile = false;
						}				    
					);
				} else if($scope.predefinedFileType=='target'){
					
					console.log($scope.protein);
					
					// // Tests
					// $http.get("path/to/file).success(function (response) {
					// 		var content = response;
					// 		var file = new File([content], "fileName.ext", {
					// 			type: "text/plain",
					// 		});
					// });
					
					var file = new File([$scope.protein.content], $scope.protein.name, {
						type: "text/plain",
					});
					
					$scope.sendFile(file, true);
					
				} else {
					
					console.error("The type is  not allowed. Trye 'test' or 'target'");
					
				}				
			};
			
			$scope.sendFileUploadButton = function(file){
				$scope.protein = null;
				$scope.sendFile(file, false);
				$scope.fileUploadStatusShow = true;
			};
			
			$scope.cancelFileProccess = function(queue, file) {
				
				if($scope.sendingFile){
					$scope.saveProteinPost.abort();
					$scope.sendingFile = false;
				}

				if($scope.preparingFile){
					$scope.prepareCanceller.resolve();
					$scope.preparingFile = false;
				}
				
				file.$cancel();
				delete $scope.files;
				$("#proteinInput").val("");
				$scope.protein = null;
				$rootScope.$emit("proteinEditorReset", {});
				
			};
			
			// Target or normal file upload
			$scope.sendFile = function(file, isPredefinedFile){
				$scope.sendingFile = true;
				var formData = new FormData();
				formData.append('files', file);
				formData.append('action', 'SAVE-FILE');
              
				$scope.saveProteinPost = $.ajax({url : 'apps/docking/action/protein-action.php',
					dataType : 'text',
					cache : false,
					contentType : false,
					processData : false,
					data : formData,
					type : 'post',					
					success : function(response) {
						$scope.$apply(function() {
							$result = JSON.parse(response);
							
							$scope.protein = file;
							$scope.protein.isPredefinedFile = isPredefinedFile;
							
							if($result.operationStatus === 'SUCCESS'){
								$scope.files = $result.files;
								if($scope.isPDBFile($scope.protein)){
									$scope.protein.codedName = $scope.files[0].name + '.pdb';
									$scope.protein.codedName_pdb = $scope.files[0].name + '_prep.pdb';								
									$scope.prepareFile();
								} else {
									$scope.protein.codedName = $scope.files[0].name + '.in';
									$scope.preparingFile = false;
									$scope.protein.prepared = true;
								}
							} else {
								$scope.protein.error = $result.errorMessage;
							}
							$scope.sendingFile = false;
						});
		        	//	$scope.messageSuccess('Protein file ' + $scope.protein.name + ' was uploaded with success');
					//	console.log(JSON.parse(phpScriptResponse));
						//$queue.push(JSON.parse(phpScriptResponse).files[0]);
					}
				});
			};
			
			$scope.prepareFile = function () {
				$scope.prepareCanceller = $q.defer();
				$scope.preparingFile = true;
				// Protein Editor reset
				//$rootScope.$emit("proteinEditorReset", {});

				// Hide step 3 and ahead
				$("#proteinInput").val("");
				
				$randomFileName = $scope.protein.codedName;
				
				$http({
					  method: 'POST',
					  url: 'apps/docking/action/protein-action.php',
					  timeout: $scope.prepareCanceller.promise, 
					  data: {
						  params :{
							  action : 'PREPARE',
							  fileName : $randomFileName,
					  		}
					  }
					}).then(function successCallback(response) {
						if(response.status == 200) {
							$result = response.data;
							if($result.operationStatus == 'SUCCESS'){
								$scope.protein.prepared = true;
								
								// Protein Editor Load
								//console.log($result.chains);
								//$rootScope.$emit("getJsonFromPdbFIleAndChainFiles", [$scope.protein.codedName]);
								$rootScope.$emit("getJsonFromPdbFIleAndChainFiles", $scope.protein.codedName, $result.chains);								
							} else {
								$scope.protein.prepared = false;
								$scope.protein.error = $result.errorMessage;
							}
//							$scope.fileUploadStatusShow = true;
							//$scope.messageSuccess('Success! (pdp file was prepared)');
						}
						$scope.preparingFile = false;
					  }, function errorCallback(response) {
						console.error(response);
					  		$scope.preparingFile = false;
					  		if($scope.protein != null){
					  			$scope.protein.prepared = false;
					  		}
						  //$scope.messageError('Error! some problem occurred on prepare file');
					  });
			};
			
			$scope.sendProteinToDock = function (){
				
				$scope.sendToDockStatus = "sending";
				
				// https://docs.angularjs.org/api/ng/function/angular.copy
				// angular.copy(source, [destination]);
				$rootScope.proteinTargetInfo = angular.copy($scope.proteinTargetInfoTemp);
				
				$http.post(
						'apps/docking/action/protein-action.php',
						{	
							params:{
								action: 'SEND-TO-DOCK'
			    			}
						}
				).success(function(response){
					
					console.log(response);
					$scope.$parent.proteinInput = $scope.protein;
					//	$('#sendProteinToDockModal').modal('show');

					$scope.sendToDockStatus = "";
					$scope.$parent.selectedTab = 'COFACTORS';
				});
			};
			
			$scope.downloadFile = function ($type){
				//window.location = 'apps/docking/action/protein-action.php?action=DOWNLOAD-FILE&fileName=' + $scope.protein.codedName+ '&type=' + $type;
				window.open('apps/docking/action/protein-action.php?action=DOWNLOAD-FILE&fileName=' + $scope.protein.codedName+ '&type=' + $type);
			};
			
			$scope.open3DModal = function() {
				//var fileName = $scope.protein.name;
				//var fileNameWithoutExtension = fileName.substr(0, fileName.lastIndexOf('.')) || fileName;
				//var jmolFile = fileNameWithoutExtension + "_prep.pdb";
				
				$('#pdbViewerModal').modal('show');
				//document.getElementById('proteinView').src = "apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName;
				if($scope.isPDBFile($scope.protein)){
					document.getElementById('proteinView').src = "apps/docking/3D-viewer/show3D.php?type=PROTEIN&file="+$scope.protein.codedName_pdb+"&original="+$scope.protein.name;
				} else {
					document.getElementById('proteinView').src = "apps/docking/3D-viewer/show3D.php?type=PROTEIN&file="+$scope.protein.codedName+"&original="+$scope.protein.name;
				}
				//window.open("apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName, 'View 3D', 'STATUS=NO,TOOLBAR=NO,LOCATION=NO,DIRECTORIES=NO,RESISABLE=NO,SCROLLBARS=YES,TOP=10,LEFT=10,WIDTH=450,HEIGHT=450');
			};
			
			$scope.open3DModalNGL = function() {
				
				// Debug
				console.log("ProteinController open3DModalNGL ...");
				
				// Show modal
				$('#nglViewerModalProtein').modal('show');
				
				// Always reload
				document.getElementById('nglViewerIframeProtein').contentWindow.location.reload(true);
				
			};
			
			$scope.deleteTestFile = function () {
				bootbox.confirm({ 
					  size: "small",
					  message: "The file  "+$scope.protein.name+" will be removed. Are you sure?",
					  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
						if(result){
							$scope.$apply(function() {
								$scope.protein = null;
								$rootScope.$emit("proteinEditorReset", {});	
								$scope.$parent.proteinInput = null;
							});
						}
					  }
				});
			};
			
		} ]).controller('FileDestroyController', [
		    '$scope', '$http','$rootScope',
		    function ($scope, $http,$rootScope) {
		    	$scope.$parent.$parent.fileUploadStatusShow = false;
		        var file = $scope.$parent.file;
		        var state;
		        if (file != null) {
		            file.$state = function () {
		                return state;
		            };
		            file.$destroy = function () {
		                state = 'pending';
		                return $http({
		                    url: file.deleteUrl,
		                    method: file.deleteType
		                }).then(
		                    function () {
		                        state = 'resolved';
		                        $scope.clear(file);
		                    },
		                    function () {
		                        state = 'rejected';
		                    }
		                );
		            };
		        } else if (!file.$cancel && !file._index) {
		            file.$cancel = function () {
		                $scope.clear(file);
		            };
		        }
		        
		        $scope.deleteFile = function(file){
		        	bootbox.confirm({ 
						  size: "small",
						  message: "The uploaded file "+file.name+" will be removed. Are you sure?",
						  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
							if(result){
					        	file.$cancel();
					        	$scope.$parent.$parent.$parent.protein = null;
					        	$rootScope.$emit("proteinEditorReset", {});
					        	$scope.$parent.proteinInput = null;
							}
						  }
					});
		        };	        

		    }
		]);