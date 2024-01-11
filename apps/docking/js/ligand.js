app.controller("LigandController", [ '$rootScope','$scope', '$http', '$q', 'appInfo',
	function($rootScope, $scope, $http, $q, appInfo) {

			// Load 3D viewer type
			appInfo.appViewer().then(function(response){
				$scope.viewerType = response.data.data;
				// console.log($scope.viewerType);
		    });
	
			$scope.sendToDockStatus = "";
	
			/* Code for popup confirmation to send dock tab		
			var popupElement = `<div align="center">
				<button id="sendLigandToDockButtonConfirm" type="button" class="btn btn-primary dock-button">Yes</button>
				<button type="button" class="btn btn-default dock-button">No</button>
			</div>`;

			$scope.$watch(('totalValidStructures') , function(newValue) {
				$('#sendLigandToDockButton').popover({
					animation: true,
					// title : "Send " + newValue + " ligand(s) to DockThor?",
					title : "Send ligand(s) to DockThor?",
					content: popupElement,
					html: true
				});
			});
			
			$( "#sendLigandToDockButton" ).click(function() {
				$( "#sendLigandToDockButtonConfirm" ).click(function() {
					$scope.$apply(function() {
						$scope.sendLigandToDock($scope.choose($scope.queue, $scope.queueTestFile));						
					});
				});
			});
			*/
	
			$scope.resetPage = function($roleStructureValid){
				
				// console.log("Reset page.");
				
				$scope.ligandStep = 'stopped';
				$scope.ligandTestStep = 'stopped';
				
				$scope.showSecondStep = false;
				$scope.showThirdStep = false;
				$scope.showRotbEditor = false;
				$scope.showTestFileMessage = false;
				$scope.showTestFileTable = false;
				$scope.showModificationAlert = false;
				$scope.showCancellAllButton = false;
				$scope.showRemoveAllButton = false;
				$scope.showAllFileFailed = false;
				$scope.showFailProgressBar = false;
				$scope.showSuccessProgressBar = false;
				$scope.showFileTable = false;
				$scope.showSendButton = false;
				$scope.showValidStructureAlert = false;
				$scope.showEqualFileWarning = false;
				
				$scope.disableSendButton = true;
				$scope.disableCancellAllButton = false;
				//$scope.disableRemoveAllButton = false;
				
				$scope.useTestFile = false;
				
				$scope.ligandPreparedFiles = [];				
				$scope.changesAfterPrepared = [];
				$scope.changesAfterPrepared.queue = [];
				$scope.changesAfterPrepared.queue.files = [];
				$scope.changesAfterPrepared.queue.state = false;
				
				$scope.changesAfterPrepared.testFile = [];
				$scope.changesAfterPrepared.testFile.file = [];
				$scope.changesAfterPrepared.testFile.state = false;
				
				$scope.totalValidStructures = 0;
				$scope.roleStructureValid = $roleStructureValid;
				
				$scope.showAllProgressFragment = false;	
				
				//used to determine if all files was processed
				$scope.uploadFilesTotal = 0;
				$scope.uploadFilesPrepared = 0;
				
				$scope.foundProblemAtSaveFiles = false;
				
				$scope.maxInputFiles = 100;
				
				$scope.downloadButton = false;
				$scope.show3D = false;
				
				$scope.showResourceSelectedDir = false;
				
				// Clear session
				$http.post(
						'apps/docking/action/ligand-action.php',
			    		{
			    			params:{
			    				'action':'clearLigandSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status == 'OK'){
						console.log("Clean ligand session files ok");
						
//						//Clear alert!
//						for (var i = 0; i < $queue.length; i++) {
//							$queue[i].prepared = true;
//						}
						
					}else{
						console.log("clean session files failed!");
					}
				});	
				
				$scope.setPaginationData();
				
				//$scope.initPreparedResourcesApp();
			};
			
			$scope.choose = function($queue,$queueTestFile){
				if($scope.useTestFile){
					return $queueTestFile;
				}else{
					return $queue;
				}
			};
			
			$scope.promptSelectTestFile = function($queueFileTest, $queue, $step, callback) {				
				
				if($queue.length>0 || $queueFileTest.length>0){
					bootbox.confirm({ 
						  size: "small",
						  message: "If you select a test file, all previous files will be removed. Are you sure ?", 
						  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/
							  if(result){
								  
								  if($scope.ligandStep!='stopped'){
									  $scope.removeAllFiles($queue, $step);  
								  }
								  
								  if($scope.ligandTestStep!='stopped'){
									  $scope.removeAllFiles($queueFileTest, $step);  
								  }	
								  
								  callback($queueFileTest);
							  }
						  }
					});	
				} else{
					callback($queueFileTest);
				}				
				
			};
			
			$scope.selectSingleDockingTest = function($queueFileTest){
				$scope.selectTestFile('SINGLE_DOCKING',$queueFileTest);				
				$('#testFileSelectModal').modal('hide');
			};
			
			$scope.selectVirtualScreeningTest = function($queueFileTest){
				$scope.selectTestFile('VIRTUAL_SCREENING',$queueFileTest);
				$('#testFileSelectModal').modal('hide');
			};
			
			$scope.selectTestFile = function(testType,$queueFileTest) {
				
				//$scope.queueTestFile = [{}];
				
				// Clear session
				$http.post(
						'apps/docking/action/ligand-action.php',
			    		{
			    			params:{
			    				'action':'clearLigandSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status == 'OK'){
						console.log("Clean ligand session files ok");
						
						//Post test file
						$http.post(
								'apps/docking/action/ligand-action.php',
								{	
									params:{
										action: 'SELECT-TEST-FILE',
										'test_type' : testType
					    			}
								}
							).success(function(response){
						    	if (response.operationStatus == 'SUCCESS'){
						    		
						    		$scope.useTestFile = true;
						    		$scope.ligandTestStep = 'stopped';
						    		$queueFileTest[0] = response.file;
						    		
						    		// Show/hide elements:						    		
						    		$scope.showSendButton = true;
									$scope.disableSendButton = false;
									$scope.showCancellAllButton = true;
									$scope.disableCancellAllButton = true;
									$scope.showRemoveAllButton = true;
									//$scope.disableRemoveAllButton = false;
									$scope.showFileTable = true;
									$scope.showSuccessProgressBar = true;
									$scope.showResourceSelectedDir = false;
									
									$scope.downloadButton = true;
									$scope.show3D = true;
									
									$scope.changesAfterPrepared.testFile.file = angular.copy($queueFileTest[0]);
						    		
								}
							}				    
						);	
						
					}else{
						console.log("clean session files failed!");
					}
			    });
						
			};
			
			$scope.startMultFileProccess = function($queue, $queueTestFile){
				
				// Test file
				if($scope.useTestFile){
					
					console.log("startMultFileProccess test file ...");
					
					// Clear session
					$http.post(
							'apps/docking/action/ligand-action.php',
				    		{
				    			params:{
				    				'action':'clearLigandSession'
				    			}
				    		}
				    ).success(function(response){
				    	if (response.status == 'OK'){
							console.log("Clean ligand session test files ok");
							
							//Post test file
							$http.post(
									'apps/docking/action/ligand-action.php',
									{	
										params:{
											action: 'SAVE-TEST-FILE',
											'file' : $queueTestFile[0]
						    			}
									}
								).success(function(response){
							    	if (response.operationStatus == 'SUCCESS'){
							    		
							    		$queueTestFile[0].state = 'loading';
										
										$http.post(
									    		'apps/docking/action/ligand-action.php',				
									    		{
									    			//timeout: $scope.prepareLigandPost[$index].promise,
									    			params:{
									    				'action':'PREPARE',
									    				file: $queueTestFile[0]
									    			}
									    		}
									    ).success(function(response){									    											
										//console.log(response);
									    	$scope.ligandTestStep = "prepared";
									    	$scope.ligandStep = "prepared";
									    	
									    	// Get new values in prepare file 
									    	$queueTestFile[0].state = response.file.state;
									    	$queueTestFile[0].validStructure = response.file.validStructure;
											$queueTestFile[0].invalidStructures = response.file.invalidStructures;									    	
									    	$scope.changesAfterPrepared.testFile.file = angular.copy($queueTestFile[0]);
									    	$scope.changesAfterPrepared.testFile.state = false;
									    	
									    	$scope.totalValidStructures = parseInt(response.file.validStructure);
									    	
									    	$scope.checkToShowRotbEditor($queueTestFile);// response files: array with ONE file (the test file)
								    		
								    		$scope.disableSendButton = true;
								    		
								    		$scope.showRemoveAllButton = true;
								    		//$scope.disableRemoveAllButton = false;
								    		
								    		$scope.showCancellAllButton = true;
								    		$scope.disableCancellAllButton = true;
								    		
								    		$scope.showSecondStep = true;
								    		$scope.showThirdStep = true;
											
											$scope.downloadButton = true;
											$scope.show3D = true;
									   });
							    		
									}
								}				    
							);
							
						}else{
							console.log("clean session files failed!");
						}
				    });
					
				}else if ($scope.isPredefinedRource){
					
					$scope.loadingSend = true;
					$scope.showRotbEditor = false;
					$scope.showSecondStep = false;
					
					console.log("startMultFileProccess predefined resource ...");
					
					// Clear session
					$http.post(
							'apps/docking/action/ligand-action.php',
				    		{
				    			params:{
				    				'action':'clearLigandSession'
				    			}
				    		}
				    ).success(function(){
						console.log("clear ligand session - success");
						
						//Post test file
						$http.post(
							'apps/docking/action/ligand-action.php',
							{	
								params:{
									action: 'SAVE-RESOURCE-FILES',
									'resource' : $scope.resource
								}
							}
						).success(function(response){
							console.log("save resource files - success!");
							
							$scope.showRotbEditor = false;
							$scope.showSecondStep = true;
							$scope.showThirdStep = true;
							$scope.ligandStep='prepared';							
							// $scope.downloadButton = false;
							$scope.show3D = false;
							$scope.showSuccessProgressBar = false;
							$scope.loadingSend = false;

							$scope.totalValidStructures = response.valid;							
							$scope.queueResource = response.queue_resources;
							console.log($scope.totalValidStructures);
							console.log($scope.queueResource);
							
						}).error(function() {
							console.error("save resource files - error!"+response);
						});
					}).error(function() {
						$scope.loadingSend = false;
                        console.error("clear ligand session error!");
                    });
					
				} else {
					
					console.log("startMultFileProccess upload file ...");
					
					/// Controlls - for all files
					$scope.totalValidStructures = 0;
					$scope.uploadFilesPrepared = 0;
					
					$scope.uploadFilesTotal = $queue.length;
					$scope.ligandStep = 'loading';
					// for each file (set index)
					for (var j = 0; j < $queue.length; j++) {
						$queue[j].state = 'loading';
						$queue[j].index = j;
					}
					
					// Show/hide/disable buttons
					$scope.viewButtonsSending();
					
					// Clear session
					$http.post(
							'apps/docking/action/ligand-action.php',
				    		{
				    			params:{
				    				'action':'clearLigandSession'
				    			}
				    		}
				    ).success(function(response){
				    	if (response.status == 'OK'){
							console.log("Clean ligand session files ok");
						
							$scope.sendFiles($queue);
									
							/*	
							$scope.$watch(function() { 
									return $scope.uploadFilesPrepared;
								},
								function(newValue, oldValue) {
									$scope.finishFileFirstStep($queue);
								}
							);*/
							
						}else{
							console.log("clean session files failed!");
						}
				    });
				}							
			};
			
			/* OLD
			$scope.sendFiles = function($queue){
				
				/// Controlls
				// for all files
				$scope.ligandStep = 'saving';
				// TODO: for each file (cancel saving)
				$scope.saveLigandPost = [];	
				// TODO: for each file (cancel preparing)
				$scope.prepareLigandPost = [];
				for (var int = 0; int < $queue.length; int++) {
					$scope.prepareLigandPost[int] = $q.defer();
				}
				
				var $i = 0;
				for ($i; $i < $queue.length; $i++) {
					
					$queue[$i].state = "saving";
					
					var formData = new FormData();					
				    formData.append('file', $queue[$i]);
				    formData.append('fileIndex', $i);
				    formData.append('action', 'SAVE-FILE');
	              
					$scope.saveLigandPost[$i] = $.ajax({url : 'apps/docking/action/ligand-action.php',				
						dataType : 'text',
						cache : false,
						contentType : false,
						processData : false,
						data : formData,
						type : 'post',
						success : function(response) {$scope.prepareFile($queue,response)}
					});
				}
			}*/
			
			$scope.sendFiles = function($queue){
				
				console.log("sendFiles - start");
				
				/// Controlls
				// for all files
				$scope.ligandStep = 'saving';
				// TODO: for each file (cancel saving)
				$scope.saveLigandPost = [];	
				// TODO: for each file (cancel preparing)
				$scope.prepareLigandPost = [];
				for (var int = 0; int < $queue.length; int++) {
					$scope.prepareLigandPost[int] = $q.defer();
				}
				
				var formData = new FormData();
				
				for (var i = 0; i < $queue.length; i++) {
					$queue[i].state = "saving";					
					formData.append('files[]', $queue[i]);
				}
				
				formData.append('action', 'SAVE-FILES');
			  
				// Debug - Display the values
				/*
				for (var value of formData.values()) {
				   console.log(value); 
				}*/
			  
				/*
				$.ajax({url : 'apps/docking/action/ligand-action.php',				
					dataType : 'text',
					cache : false,
					contentType : false,
					processData : false,
					data : formData,
					type : 'post',
					success : function(response) {
						
						//console.log(response);
						$responseArray = JSON.parse(response); // string json to object
						
						//for (var j=0; j < $queue.length; j++) {
						//	$scope.prepareFileAtOnce($queue,$responseArray[j]); // mudar isso e passar um item por vez
						//}
						
						$foundProblem = false;
						for(var i=0; i< $responseArray.length; i++){
							
							// Binding new data
							$index = $responseArray[i].index;
							
							if($responseArray[i].state != 'success'){
								$foundProblem = true;
							}
							
							$queue[$index].originalName = $responseArray[i].originalName;
							$queue[$index].fileId = $responseArray[i].fileId;
							$queue[$index].fileIdWithExtension = $responseArray[i].fileIdWithExtension;
							$queue[$index].thumbnailUrl = $responseArray[i].thumbnailUrl;
							$queue[$index].deleteUrl = $responseArray[i].deleteUrl;
							$queue[$index].fileExtension = $responseArray[i].fileExtension;
							$queue[$index].errorMessage = $responseArray[i].errorMessage;
							$queue[$index].state = $responseArray[i].state;
							
						}
						
						console.log($queue);
						if(!$foundProblem){
							$scope.prepareFile($queue,$responseArray);							
						} else {
							$scope.disableSendButton = true;
						}
						
					}
				});
				*/
				
				$scope.foundProblemAtSaveFiles = false;
				
				$http({
					method: 'POST',
					url: 'apps/docking/action/ligand-action.php',
					headers: {
						'Content-Type': undefined
					},
					data: formData,					
				})
				.success(function (response) {
					// console.log(response);
					$responseArray = response;
					
					for(var i=0; i< $responseArray.length; i++){
						
						// Binding new data
						$index = $responseArray[i].index;
						
						if($responseArray[i].state != 'save-success'){
							$scope.foundProblemAtSaveFiles = true;
						}
						
						$queue[$index].originalName = $responseArray[i].originalName;
						$queue[$index].fileId = $responseArray[i].fileId;
						$queue[$index].fileIdWithExtension = $responseArray[i].fileIdWithExtension;
						$queue[$index].thumbnailUrl = $responseArray[i].thumbnailUrl;
						$queue[$index].deleteUrl = $responseArray[i].deleteUrl;
						$queue[$index].fileExtension = $responseArray[i].fileExtension;
						$queue[$index].errorMessage = $responseArray[i].errorMessage;
						$queue[$index].state = $responseArray[i].state;
						
					}
					
					console.log("sendFiles - finish");
					if(!$scope.foundProblemAtSaveFiles){
						$scope.prepareFiles($queue,$responseArray);
					} else {
						$scope.disableSendButton = true;
					}
				})
				.error(function (data, status) {
					console.error(data);
					console.error(status);
				});
				
			};
			
			/* OLD
			$scope.prepareFile = function($queue,response) {
				
				if($scope.ligandStep != 'cancelled'){
					
					$saveResponse = angular.fromJson(response);
					$index = $saveResponse.file[0].index;
					$queue[$index].saveResult = $saveResponse.file[0].saveResult;					

					// Binding new data
					$queue[$index].originalName = $saveResponse.file[0].originalName;
					$queue[$index].fileId = $saveResponse.file[0].fileId;
					$queue[$index].fileIdWithExtension = $saveResponse.file[0].fileIdWithExtension;
					$queue[$index].thumbnailUrl = $saveResponse.file[0].thumbnailUrl;
					$queue[$index].deleteUrl = $saveResponse.file[0].deleteUrl;
					$queue[$index].fileExtension = $saveResponse.file[0].fileExtension;		
					$queue[$index].state = "saved";
					
					// Prepare one file
					$scope.$apply(function() {
						
						$http.post(
								'apps/docking/action/ligand-action.php',				
								{
									timeout: $scope.prepareLigandPost[$index].promise,
									params:{
										'action':'PREPARE',
										file: $queue[$index]
									}
								}
						).success(function(response){
							$saveResponse = angular.fromJson(response);
							// console.log(response);
							
							// Biding new data
							$index = $saveResponse.file.index;			    			
							$queue[$index].state = $saveResponse.file.state; // prepared	
							$queue[$index].validStructure = $saveResponse.file.validStructure;
							$queue[$index].errorMessage = $saveResponse.file.errorMessage;
							$queue[$index].invalidStructures = $saveResponse.file.invalidStructures;
							
							//update totalValidStructures (for progress bar)
							$scope.totalValidStructures += parseInt($queue[$index].validStructure);
							
							//Count prepared files
							$scope.uploadFilesPrepared++;
							
							// set prepared value for future changes
							// console.log("arquivo sendo preparado para futuras mudancas: "+$queue[$index].originalName);
							$scope.changesAfterPrepared.queue.files[$index] = angular.copy($queue[$index]);				    			
							//.push('File added: '+$newFileName);
					   });
					});
				}				
			};*/
			
			$scope.prepareFiles = function($queue) {
				
				console.log("prepareFiles - start");
				
				if($scope.ligandStep != 'cancelled'){	
						
					$http.post(
							'apps/docking/action/ligand-action.php',				
							{
								timeout: $scope.prepareLigandPost[$index].promise,
								params:{
									'action':'PREPARE-MULT',
									files: $queue
								}
							}
					).success(function($responseArray){
						
						// console.log(response);						
						
						
						// Biding new info							
						for(var i=0; i< $responseArray.length; i++){
							
							// Update queue info
							$index = $responseArray[i].file.index;			    			
							$queue[$index].state = $responseArray[i].file.state; // prepared	
							$queue[$index].validStructure = $responseArray[i].file.validStructure;
							$queue[$index].errorMessage = $responseArray[i].file.errorMessage;
							$queue[$index].invalidStructures = $responseArray[i].file.invalidStructures;
							
							//update totalValidStructures (for progress bar)
							$scope.totalValidStructures += parseInt($queue[i].validStructure); 
							
							//Count prepared files
							$scope.uploadFilesPrepared++;
							
							// Arquivo sendo preparado para futuras mudancas								
							$scope.changesAfterPrepared.queue.files[i] = angular.copy($queue[i]);	
						}
						
						$scope.finishFileFirstStep($queue);
						console.log("prepareFiles - finish");
				   });
					
				}				
			};
			
			$scope.finishFileFirstStep = function($queue){

				console.log("finishFileFirstStep - start");

    			// Check if all file failed
    			if( ($scope.uploadFilesPrepared!=0 || $scope.uploadFilesTotal!=0) && 
    				($scope.uploadFilesPrepared == $scope.uploadFilesTotal)){
    				$scope.ligandStep = "prepared";
    				$scope.changesAfterPrepared.queue.state = false;
    				
    				if($scope.totalValidStructures!=0){
    					//login role
    	    			if($scope.totalValidStructures<=$scope.roleStructureValid){
    	    				
    	    				$http.post(
						    		'apps/docking/action/ligand-action.php',				
						    		{
						    			params:{
						    				'action':'CHECK_EQUALS',
						    				files: $queue
						    			}
						    		}
						    ).success(function(response){
						    	$equalsResponse = angular.fromJson(response);
						    	if($equalsResponse.isfound){
						    		console.log("Theres equal files");
						    		//console.log($equalsResponse.equalFiles);
						    		$scope.showEqualFileWarning = true; 
						    		$scope.equalFiles = $equalsResponse.equalFiles;
						    	}else{
						    		//console.log("Theres not equal files");
						    		
						    		$scope.equalFiles = null;
						    		$scope.showEqualFileWarning = false;
						    		
						    		$scope.showSuccessProgressBar = true;
		    	    				$scope.showFailProgressBar = false;
		    	    				$scope.disableCancellAllButton = true;
		    	    				//$scope.disableRemoveAllButton = false;
																		
		    	    				$scope.showSecondStep = true;
		    						$scope.showThirdStep = true;
									
									$scope.downloadButton = true;
									$scope.show3D = true;
		    	    				
		    	    				//check if has one file and if has some error
		    	    				if($scope.totalValidStructures == 1){		    					
		    	    					$scope.checkToShowRotbEditor($queue);
		    	    				}else{
		    	    					$scope.showRotbEditor = false;
		    	    				}
									
						    	}
						    	
						    });
							
    	    				console.log("finishFileFirstStep - finish");
    	    				
    	    			}else{
    	    				$scope.showSecondStep = false;
    						$scope.showThirdStep = false;
    						//$file.errorMessage = "The maximun of valid structure is "+$scope.roleStructureValid+"! Please, upload another file(s).";
    						$scope.disableCancellAllButton = true;
    						//$scope.disableRemoveAllButton = false;
    						$scope.showSuccessProgressBar = false;
    	    				$scope.showFailProgressBar = true;
    	    			}
    				}else{
    					$scope.showSendButton = true;
    					$scope.showCancellAllButton = true;
    					$scope.showRemoveAllButton = true;
    					
    					$scope.disableSendButton = true;
    					$scope.disableCancellAllButton = true;
    					//$scope.disableRemoveAllButton = false;
    				}	
				}
			};
			
			$scope.checkboxHydrogenOnClick = function($file,$queue){
				$file.hidrogen=!$file.hidrogen;
				
				if($scope.useTestFile){
					if($scope.ligandTestStep == "stopped"){
						$scope.changesAfterPrepared.testFile.file.hidrogen = $file.hidrogen;
					}else if($scope.ligandTestStep == "prepared"){
						if($scope.changesAfterPrepared.testFile.file.hidrogen != $file.hidrogen){
							$scope.changesAfterPrepared.testFile.state = true;
							$scope.disableSendButton = false;
						}else{
							$scope.changesAfterPrepared.testFile.state = false;
							$scope.disableSendButton = true;
						}
					}
					
				}else{
					if($scope.ligandStep == 'prepared'){

						// Check if has hidrogen change from $scope.ligandPreparedFiles.files and $queue[j].name
						// Just one change to show the warning message (changesAfterPrepared)
						
						$found = false;
						var qtd = $scope.checkPreparedLength();
						if(qtd == $queue.length){
							for(i=0; i < $scope.changesAfterPrepared.queue.files.length; i++ ){
								
								if( ($queue[i].state=='prepared') || ($queue[i].state=='failed') || ($queue[i].state=='partialFailed')  ){
									if($scope.changesAfterPrepared.queue.files[i].hidrogen != $queue[i].hidrogen){
										$scope.changesAfterPrepared.queue.state = true;
										$found = true;
										$scope.disableSendButton = false;
										break;
									}else{
										// do nothing
									}
								}else{
									$scope.changesAfterPrepared.queue.state = true;
									$found = true;
									$scope.disableSendButton = false;
									break;
								}
								
							}
							
							if(!$found){
								$scope.changesAfterPrepared.queue.state = false;
								$scope.disableSendButton = true;
							}
						}else{
							console.log("Some problem occurred in checkbox HydrogenOnClick");
						}		
					}
				}				
			};
		
			$scope.checkToShowRotbEditor = function($queue){
				
				if($queue.length==1 && $scope.totalValidStructures == 1 && $queue[0].fileExtension != 'top'){
						
					$http.post(
						'apps/docking/action/ligand-action.php',				
						{
							params:{
								//'action':'checkIfHasOneTopFile',
								'action':'checkTopHasSelectedTorsion',
								//'files':$filesArray
								'file':$queue[0]
							}
						}
					).success(function(response){
						if (response.status == 'SUCCESS'){
							$scope.showRotbEditor = true;
							$rootScope.$emit("getJsonFromTopFile",$queue[0].fileId);
						}else{
							$scope.showRotbEditor = false;
						}
						console.log("Check if top has selected torsion == 1: " + response.status);
					});		
					
				}else{
					$scope.showRotbEditor = false;
				}
			};
			
			$scope.sendLigandToDock = function ($queue, $queueTestFile){
				
				$array = null;
				
				if($scope.isPredefinedRource){
					$array = $scope.queueResource;
				} else if($scope.useTestFile){
					$array = $queueTestFile;
				}else{
					$array = $queue;
				}
				
				$scope.sendToDockStatus = "sending";
				
				$http.post(
						'apps/docking/action/ligand-action.php',
						{	
							params:{
								action: 'SEND-TO-DOCK'
			    			}
						}
					).success(function(){
						//$scope.$parent.ligandInput = $scope.ligandPreparedFiles;
						$scope.$parent.ligandInput = angular.copy($array);
						
						// $('#sendLigandToDockModal').modal('show');
						$scope.sendToDockStatus = "";
						$scope.changePageSendToDocking();
						
					});
			};
			
			$scope.changePageSendToDocking = function() {
				if($scope.$parent.proteinInput != null){
					$scope.$parent.selectedTab = 'DOCKING';
				}else{
					//bootbox.alert({
    					//	message: "This is the small alert!",
    					//	size: 'small',
					//	callback: function() {$scope.$parent.selectedTab = 'PROTEIN';}
					//});	
					$scope.$parent.selectedTab = 'PROTEIN';
				}
			};
			
			$scope.open3DModal = function($queue) {
				
				//var fileName = $scope.protein.name;
				//var fileNameWithoutExtension = fileName.substr(0, fileName.lastIndexOf('.')) || fileName;
				//var jmolFile = fileNameWithoutExtension + "_prep.pdb";
				
				//$scope.selectedToView = $scope.ligandPreparedFiles.files[0];
				$scope.selectedToView = $queue[0];
				$('#ligandViewerModal').modal('show');
				$scope.update3DModal();
				//document.getElementById('proteinView').src = "apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName;
				//document.getElementById('ligandView').src = "apps/docking/3D-viewer/show3D.php?ligand=1&type=LIGAND&file="+$scope.ligandPreparedFiles.files[0].nameWithExtension +"&original="+$scope.ligandPreparedFiles.files[0].originalName;
				//window.open("apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName, 'View 3D', 'STATUS=NO,TOOLBAR=NO,LOCATION=NO,DIRECTORIES=NO,RESISABLE=NO,SCROLLBARS=YES,TOP=10,LEFT=10,WIDTH=450,HEIGHT=450');
				
			};
			
			$scope.update3DModal = function(){
				document.getElementById('ligandView').src = "apps/docking/3D-viewer/show3D.php?ligand=1&type=LIGAND&file="+$scope.selectedToView.fileIdWithExtension +"&original="+$scope.selectedToView.originalName;
			};
			
			$scope.open3DModalNGL = function($queue) {
				
				if($queue.length>$scope.maxInputFiles){
					bootbox.alert('<p align="center">The 3D visualisation is not available for more than '+$scope.maxInputFiles+' input structures</p>');					
				} else {
					// Debug
					console.log("LigandController open3DModalNGL ...");
					
					// Show modal
					$('#nglViewerModalLigand').modal('show');
					
					// Always reload
					document.getElementById('nglViewerIframeLigand').contentWindow.location.reload(true);
				}
				
				
			};
			
			$scope.downloadFile = function ($type){
				//window.location = 'apps/docking/action/ligand-action.php?action=DOWNLOAD-FILE&type=' + $type;
				window.open('apps/docking/action/ligand-action.php?action=DOWNLOAD-FILE&type=' + $type);
			};

			$scope.removeFileWithConfirmation = function($file,$queue,$queueTestFile){
								
				bootbox.confirm({
					  size: "small",
					  message: "Are you sure to remove the file "+$file.name+"?",
					  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
						if(result){								
							$scope.removeFile($file,$queue,$queueTestFile);
						}
					  }
				});
				
			};
			
			$scope.removeAllFilesWithConfirmation = function($queue, $queueTestFile){
				
				bootbox.confirm({ 
					  size: "small",
					  message: "Are you sure to remove all files?", 
					  callback: function(result){ /* result is a boolean; true = OK, false = Cancel */ 
						if(result){				
							$scope.removeAllFiles($queue, $queueTestFile);
						}
					  }
				});
			};
			
			$scope.removeAllFiles = function($queue, $queueTestFile){				
				
				$scope.resetPage($scope.roleStructureValid);
				
				// Clear session
				$http.post(
						'apps/docking/action/ligand-action.php',
			    		{
			    			params:{
			    				'action':'clearLigandSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status == 'OK'){
			    		
			    		//clear fileUpload. obs.: inverse for: when a file is removed de indexes are updated (removing first: 0,1,2,3; removing second: 0,1,2; ..)
						for (i = ($queue.length-1); i >= 0; i--) { 
							$queue[i].$cancel();
						}
			    		
						//clear test files:
						delete $queueTestFile[0];
						if($queueTestFile.length>0){
							$queueTestFile.length--;
						}
		    			
		    			$scope.changesAfterPrepared.queue.state = false;
		    			$scope.$parent.ligandInput = null;
		    			
						// Clear prepared resources
						$scope.resource = null;
						$scope.showResourceSelectedDir = false;
						$scope.isPredefinedRource = false;
						
		    			console.log("RemoveAllFiles: clean ligand session files ok");
					}else{
						console.log("RemoveAllFiles: clean session files failed!");
					}
			    });
							
			};
			                             
			$scope.removeFile = function($file,$queue,$queueTestFile){
				console.log("Remove file: "+$file.name+ " ("+$file.fileIdWithExtension+")");
				
				if($file.state=="prepared"){
					$http.post(
				    		'apps/docking/action/ligand-action.php',				
				    		{
				    			params:{
				    				'action':'removeFile',
				    				'fileIdWithExtension':$file.fileIdWithExtension
				    			}
				    		}
				    ).success(function(response){
				    	if (response.removeFileResponse == 'SUCCESS'){
				    		
				    		console.log('removeFile '+$file.name+': success!');
				    		
				    		if($scope.useTestFile){ //test file (only one file)
				    			delete $queueTestFile[0];
				    			$queueTestFile.length--;
				    			$scope.resetPage($scope.roleStructureValid);
							} else{ //user uploaded file
								
				    			// Remove from queue
								$file.$cancel();
								
								// Remove and update modification list
								delete $scope.changesAfterPrepared.queue.files[$file.index];
								
								if($scope.checkIfFilesArePrepared($queue)){	
								
									$scope.uploadFilesPrepared -= 1;
									
									if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
										$scope.changesAfterPrepared.queue.state = false;
									}else{
										$scope.changesAfterPrepared.queue.state = true;
									}
									
									// Update total valid structure and number of prepared files and total file selected (add file)
									if($scope.totalValidStructures>0){
										$scope.totalValidStructures = ($scope.totalValidStructures - $file.validStructure);
										//$scope.uploadFilesPrepared -=1;
									}
									$scope.uploadFilesTotal -= 1;
									console.log("tamanho da lista: "+$queue.length);
									
									// Caso o arquivo removido seja o ultimo resetar a pagina (resetPage nao funciona quando executado daqui)
									if($queue.length == 0){								
										
										$scope.showSecondStep = false;
										$scope.showThirdStep = false;
										$scope.showRotbEditor = false;
										$scope.showSuccessProgressBar = false;
										$scope.showFailProgressBar = false;
										$scope.showFileTable =false;
										$scope.showCancellAllButton = false;
										$scope.showRemoveAllButton = false;
										$scope.disableSendButton = false;
										$scope.showSendButton = false;
										$scope.ligandStep = 'stopped';
										$scope.changesAfterPrepared.queue.state = false;
	
									} else {
										
										//check if has one file and if has some error
										if($scope.totalValidStructures == 0){			
											$scope.showSecondStep = false;
											$scope.showThirdStep = false;
										}else if($scope.totalValidStructures == 1){
											$scope.showSuccessProgressBar = true;
											$scope.showFailProgressBar = false;
											$scope.showSecondStep = true;
											$scope.showThirdStep = true;
											$scope.checkToShowRotbEditor($queue);
											$scope.disableSendButton = true;
										} else {
											//login role
											if($scope.totalValidStructures<=$scope.roleStructureValid){
												$scope.showSuccessProgressBar = true;
												$scope.showFailProgressBar = false;
												$scope.disableCancellAllButton = true;
												//$scope.disableRemoveAllButton = false;
												$scope.disableSendButton = true;
												$scope.showSecondStep = true;
												$scope.showThirdStep = true;
												
											}else{
												$scope.showSecondStep = false;
												$scope.showThirdStep = false;
												$file.state = "failed";
												$file.errorMessage = "The maximun of valid structure is "+$scope.roleStructureValid+"! Please, upload another file(s).";
												$scope.disableCancellAllButton = true;
												//$scope.disableRemoveAllButton = false;
												$scope.showSuccessProgressBar = false;
												$scope.showFailProgressBar = true;
											}
										}
										
									}
									
								} else {
									$scope.disableSendButton = true;
									$scope.showSecondStep = false;
									$scope.showThirdStep = false;
								}
							}
							
						}else{
							console.error('RemoveFile '+$file.name+': failed!');
						}
				    });	
				}else{
					
					//test file (only one file)
		    		if($scope.useTestFile){
		    			delete $queueTestFile[0];
		    			$queueTestFile.length--;
		    			$scope.resetPage($scope.roleStructureValid);
		    			
					} else{ //user uploaded file
						
						// Remove from queue
						$file.$cancel(); 
						
						$scope.uploadFilesTotal--;
						$scope.uploadFilesPrepared--;

						if($scope.uploadFilesTotal==0){
							$scope.resetPage($scope.roleStructureValid);
						}else{
							
							$scope.disableSendButton = $scope.checkIfFilesHasProblemBeforePrepared($queue);	
							
							if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
								$scope.changesAfterPrepared.queue.state = false;															
							}else{
								$scope.changesAfterPrepared.queue.state = true;
							}
						}					
						
		    		}
					
				}
				
			};
			
			$scope.checkIfFilesArePrepared = function($queue){
				for(let i=0; i<$queue.length; i++){
					//console.log($queue[i].state); 
					if($queue[i].state!=undefined && $queue[i].state!="prepared"){
						return false;
					} 
				}
				return true;
			};
			
			$scope.checkIfFilesHasProblemBeforePrepared = function($queue){
				for(let i=0; i<$queue.length; i++){
					console.log($queue[i].name + " - "+ $queue[i].state); 
					if($queue[i].state!=undefined && $queue[i].state=="save-error"){
						return true;
					} 
				}
				return false;
			};
			
			//$scope.checkPreparedFile = function($queue,$input){
			$scope.addFile = function($input){
				
				//$scope.resetPage();
				$scope.showSendButton = true;
				$scope.disableSendButton = false;
				$scope.showFileTable = true;
				$scope.showRemoveAllButton = true;
				$scope.showSuccessProgressBar = true;
	    		$scope.showFailProgressBar = false;
	    		//$scope.uploadFilesTotal++;
	    		$scope.uploadFilesTotal=$scope.uploadFilesTotal+$input.files.length;
	    		
	    		//var inp = document.getElementById('files');
	    		
	    		//TODO:Achar qtd de arquivos enviados
	    		//console.log("queue length: "+$queue.length);
	    		
	    		// Case test File
	    		if($scope.useTestFile){
	    			$scope.$$childHead.queue = [];
	    			$scope.$$childHead.queueTestFile = [];
	    			$scope.useTestFile = false;
	    			$scope.totalValidStructures = 0;
	    			$scope.showSecondStep = false;
	    			$scope.showThirdStep = false;
	    		}
	    		// case user file
	    		else{
	    			
	    			if($scope.totalValidStructures!=0){
	    				if($scope.uploadFilesPrepared==0){
			    			$scope.changesAfterPrepared.queue.state = false;
						}else{
							if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
								$scope.changesAfterPrepared.queue.state = false;
							}else{
								$scope.changesAfterPrepared.queue.state = true;
							}					
						}
		    			
		    			if($scope.ligandStep == 'prepared'){
							$scope.showSecondStep = true;
							$scope.showThirdStep = true;
							$scope.changesAfterPrepared.queue.state = true;
							//$scope.changesAfterPrepared.changes.push('File added: '+$newFileName);
//							$scope.changesAfterPrepared.changes.push('File added');
//							
////							angular.forEach($queue, function($queueItem, $queieKey) {
////								console.log($queueItem);
////								$queueItem.showAlert = false;
////							});	
////							$scope.showModificationAlert = true;
////							$scope['alert_'+$alertName] = true;
						}
//						$scope.disableSendButton = false;
//						//console.log("select file");
		    		}
	    		}					
			};		
			
			$scope.checkTotalValidStructure = function() {
				if($scope.totalValidStructures>100){
					$scope.showSecondStep = false;
					$scope.showThirdStep = false;
					$scope.disableSendButton = false;
					$scope.showValidStructureAlert = true;
				}
			};
			
			$scope.viewButtonsSending = function(){
				$scope.showAllProgressFragment = true;
				$scope.showCancellAllButton = true;
				$scope.showRemoveAllButton = true;
				// $scope.disableRemoveAllButton = true;
				$scope.disableCancellAllButton = false;
				$scope.disableSendButton = true;
				
			};
			
			$scope.counter = 0;
		      $scope.change = function() {
		        $scope.counter++;
		      };
		      
		    $scope.checkPreparedLength = function() {
		    	var qtd=0;
		    	angular.forEach($scope.changesAfterPrepared.queue.files, function($queueItem, $queieKey) {
					qtd++;
		    	});	
		    	return qtd;
			};
			

			/**
			 * PAGINATION BEGIN
			*/
			
			$scope.paginationOptions = null;

			$scope.pagination = null;

			$scope.setPaginationData = function(){
				$scope.paginationOptions = {
					'numbersPerPage': [5,10,20,50,100]
				};
	
				$scope.pagination = {
					'pages': 0,
					'numberPerPage': $scope.paginationOptions.numbersPerPage[0],
					'selectedPage': 1,
					'numberOfUnselectedPagesToShow': 3,
				};
			};

			$scope.temp = null;

			$scope.paginationFirst = function(){
				$scope.pagination.selectedPage = 1;
			};

			$scope.paginationPrevious = function(){
				if($scope.pagination.selectedPage>1){
					$scope.pagination.selectedPage--;
				}
			};

			$scope.paginationNext = function(files){
				$scope.pagination.pages = Math.ceil(files.length/$scope.pagination.numberPerPage);
				if($scope.pagination.selectedPage<$scope.pagination.pages){
					$scope.pagination.selectedPage++;
				}				
			};
			
			$scope.paginationLast = function(){
				$scope.pagination.selectedPage = $scope.pagination.pages;
			};

			$scope.paginationSelect = function(index){
				$scope.pagination.selectedPage = index+1;
			};
			
			$scope.hidePage = function($index){
				// return true;
				// console.log($index + " - selectedPage:"+ $scope.pagination.selectedPage);
				
				if( $index > ($scope.pagination.selectedPage+$scope.pagination.numberOfUnselectedPagesToShow) ){ // right
					return false;
				} else if( $index < ($scope.pagination.selectedPage-$scope.pagination.numberOfUnselectedPagesToShow) ){ // left
					return false;
				} else {
					return true;	
				}				
			};

			$scope.showItemForPagination = function(index, files, pagination){

				if(files.length>0){
					
					index++;
					// console.log("## index:"+index);
					// console.log("length:"+files.length);
					$scope.pagination.pages = Math.ceil(files.length/$scope.pagination.numberPerPage);
					// console.log("pages: "+$scope.pagination.pages);	

					$rangeMax = $scope.pagination.selectedPage 		* $scope.pagination.numberPerPage;
					$rangeMin = (($scope.pagination.selectedPage-1) * $scope.pagination.numberPerPage)+1;
					// console.log("index from " + $rangeMin + " to " + $rangeMax);
					if( (index>=$rangeMin) && (index<=$rangeMax) ){
						return true;
					} else{
						return false;
					}
					
				}else{
					return true;
				}	
				
			};

			/*
			
			$scope.paginationFooterMenuCheckHasProblem = function(queue, pagination, menuIndex, changesAfterPrepared){				
				
				// console.log("######## Check problem:");
				// console.log(pagination);
				// console.log(menuIndex)

				let ini = menuIndex*pagination.numberPerPage;
				let end = ini+pagination.numberPerPage;
				
				for (let index = ini; index < end; index++) {

					if(queue[index]!=undefined){ // this is for last page
						const file = queue[index];
						
						//console.log(file);
						//console.log(changesAfterPrepared.queue.files[index]);
						
						if(file.state=="save-error" || file.state=="failed"){
							return "fa fa-ban";
						} else if(file.state=="partialFailed"){
							return "fa fa-exclamation-triangle";
						} else if(changesAfterPrepared.queue.files[index]!=undefined && file.hidrogen!=changesAfterPrepared.queue.files[index].hidrogen){
							return "fa fa-exclamation";
						//} else if( (file.state != undefined) && (file.state != "prepared")){
						//	return "fa fa-exclamation";
						} else {
							return "";
						}

						
					}else{
						break;
					}
					
				}
				return "";
			};*/
			
			//TODO changesAfterPrepared not working: $scope.checkPageForAProblem = function(queue, pagination, menuIndex, changesAfterPrepared){
			$scope.checkPageForAProblem = function($queue, pageIndex){
				
				let ini = pageIndex*$scope.pagination.numberPerPage;
				let end = (ini+$scope.pagination.numberPerPage);				
				let result = '';
				
				// console.log("checking page:" + pageIndex + " queue_ini:"+ini+" - queue_end:"+(end-1));
				// console.log(changesAfterPrepared.queue);
				
				for (let index = ini; index < end; index++) {

					if($queue[index]!=undefined){ // this is for last page
						const file = $queue[index];
						
						// console.log(file.name + " - " + file.state);
						// console.log(changesAfterPrepared.queue.files[index] + " - " + file.hidrogen);
						
						if(
						   file.state!=undefined && // Ainda não foi enviado
						   file.state!="save-success" && // salvo com sucesso
						   file.state!="prepared" // preparado com sucesso
						){
							// console.log("found problem: " + file.name + " - " + file.state);
							result = "fa fa-exclamation";
							break;
						} else if($scope.changesAfterPrepared.queue.files[index]!=undefined && file.hidrogen!=$scope.changesAfterPrepared.queue.files[index].hidrogen){
							result = "fa fa-exclamation";
							break;						
						} 												
						
					}else{
						result = "";
					}
					
				}
				return result;				
			};
			
			$scope.checkNextPagesForAProblem = function($queue){				
				//console.log("checkNextPagesForAProblem start");
				let i = $scope.pagination.selectedPage+$scope.pagination.numberOfUnselectedPagesToShow;
				let result = "";
				
				
				for(i;i<=$scope.pagination.pages; i++){
								
					result = $scope.checkPageForAProblem($queue, i);
					// console.log("verify next page: "+i+" result"+result);		
					if(result != ""){
						return result;
					}
				}
				//console.log("paginationCheckNextProblem end");
			};
			
			$scope.checkPreviousPagesForAProblem = function($queue){
				
				let pageIndex = $scope.pagination.selectedPage-1;
				let i = (pageIndex-$scope.pagination.numberOfUnselectedPagesToShow)-1;
				let result = "";
				// console.log("checkPreviousPagesForAProblem pageIndex="+pageIndex+" i="+i);
				// Check all next pages
				for(i;i>=0; i--){
								
					result = $scope.checkPageForAProblem($queue, i);
					// console.log("verify previous page: "+i+" result"+result);		
					if(result != ""){
						return result;
					}
				}
				//console.log("paginationCheckPreviousProblem end");
			};

			$scope.selectNumbersPerPage = function(opt){
				$scope.setPaginationData();
				$scope.pagination.numberPerPage=opt;				
			};

			/**
			 * PAGINATION END
			*/
			
			/**
			 * Prepared files app - BEGIN
			*/
			
			$scope.preparedResoucesLabel = "Compounds Datasets";
			$scope.preparedResoucesType = "ligand";
			$('#files').fileupload();
			
			$scope.loadPreparedResource = function(selectedpath){
                
				console.log(selectedpath);
				
				$scope.isPredefinedRource = true;
				
				$scope.resource = selectedpath;
				$scope.showResourceSelectedDir = true;
				$scope.showFileTable = false;
				
				$scope.showSendButton = true;
				$scope.showRemoveAllButton = true;
				$scope.disableSendButton = false;
				$scope.downloadButton = false;
				
			};
			
			$scope.sendResource = function(){
				
			};
			
			$scope.removeResource = function(){
				
			};
			
			/**
			 * Prepared files app - END
			*/
			
	}]);
