app.controller("CofactorController", [ '$rootScope','$scope', '$http', '$q', 'appInfo',
	function($rootScope, $scope, $http, $q, appInfo) {
	
			// Load 3D viewer type
			appInfo.appViewer().then(function(response){
				$scope.viewerType = response.data.data;
				// console.log($scope.viewerType);
		    });
	
			$scope.sendToDockStatus = "";
	
			/* Code for popup confirmation to send dock tab
			var popupElement = `<div align="center">
				<button id="sendCofactorToDockButtonConfirm" type="button" class="btn btn-primary dock-button">Yes</button>
				<button type="button" class="btn btn-default dock-button">No</button>
			</div>`;			
			
			$scope.$watch(('totalValidStructures') , function(newValue) {
				$('#sendCofactorToDockButton').popover({
					animation: true,
					// title : "Send " + newValue + " cofactor(s) to DockThor?",
					title : "Send cofactor(s) to DockThor?",
					content: popupElement,
					html: true
				});
			});
			
			$( "#sendCofactorToDockButton" ).click(function() {
				$( "#sendCofactorToDockButtonConfirm" ).click(function() {
					$scope.$apply(function() {
						$scope.sendCofactorToDock($scope.choose($scope.queue, $scope.queueTestFile));
					});
				});
			});
			*/
	
			$scope.resetPage = function($roleStructureValid){
				
				console.log("Reset page.");
				
				$scope.cofactorStep = 'stopped';
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
				
				$scope.preparedFiles = [];				
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
				
				// Clear session
				$http.post(
						'apps/docking/action/cofactor-action.php',
			    		{
			    			params:{
			    				'action':'clearCofactorSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status = 'OK'){
						console.log("Clean ligand session files ok");
						
//						//Clear alert!
//						for (var i = 0; i < $queue.length; i++) {
//							$queue[i].prepared = true;
//						}
						
					}else{
						console.log("clean session files failed!");
					}
			    });	
			}
			
			$scope.choose = function($queue,$queueTestFile){
				if($scope.useTestFile){
					return $queueTestFile;
				}else{
					return $queue
				}
			}
			
			$scope.promptSelectTestFile = function($queueFileTest, $queue, $step, callback) {				
				
				if($queue.length>0 || $queueFileTest.length>0){
					bootbox.confirm({ 
						  size: "small",
						  message: "If you select a test file, all previous files will be removed. Are you sure ?", 
						  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/
							  if(result){
								  
								  if($scope.cofactorStep!='stopped'){
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
			
//			$scope.selectVirtualScreeningTest = function($queueFileTest){
//				$scope.selectTestFile('VIRTUAL_SCREENING',$queueFileTest);
//				$('#testFileSelectModal').modal('hide');
//			};
			
			$scope.selectTestFile = function(testType,$queueFileTest) {
				
				//$scope.queueTestFile = [{}];
				
				// Clear session
				$http.post(
						'apps/docking/action/cofactor-action.php',
			    		{
			    			params:{
			    				'action':'clearCofactorSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status = 'OK'){
						console.log("Clean ligand session files ok");
						
						//Post test file
						$http.post(
								'apps/docking/action/cofactor-action.php',
								{	
									params:{
										action: 'SELECT-TEST-FILE',
										'test_type' : testType
					    			}
								}
							).success(function(response){
						    	if (response.operationStatus = 'SUCCESS'){
						    		
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
									
									$scope.changesAfterPrepared.testFile.file = angular.copy($queueFileTest[0]);
						    		
								}
							}				    
						);	
						
					}else{
						console.log("clean session files failed!");
					}
			    });
						
			};
			
//			$scope.deleteTestFile = function () {
//				bootbox.confirm({ 
//					  size: "small",
//					  message: "Are you sure to remove the file "+$scope.preparedFiles.files[0].originalName+"?",
//					  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
//						if(result){
//							//$scope.$apply(function() {
//								$scope.preparedFiles = null;
//								$scope.showTestFileTable = false;
//								$scope.showSecondStep = false;
//					    		$scope.showThirdStep = false;
//					    		$scope.showTestFileMessage = false;
//					    		$scope.cofactorStep = 'stopped';
//							//})
//						}
//					  }
//				});
//			};
			
			$scope.cancelFileProccess = function($file,$queue,$queueTestFile) {
				
				console.log("Cancel file proccess: " + $file);
				$scope.cofactorStep;
				//console.log("$scope.saveLigandPost: " + $scope.saveLigandPost);
				
				//TODO (dont work)
				if($scope.state == 'sending'){
//					console.log("Canceling in 'sending'..")
//					//$scope.saveLigandPost[$file.index].abort();
//					$scope.removeFile($file,$queue,$queueTestFile);
				}else if ($scope.state == 'preparing'){
//					$scope.preparingLigandPost[$file.index].resolve();
				}
				$scope.removeFile($file,$queue,$queueTestFile);
				
				
//
//				if($scope.cofactorStep == 'preparing'){
//					$scope.prepareCanceller.resolve();
//				}
//				//file.$cancel();
//				//delete $scope.files;
//				//$("#ligandInput").val("");
//				
//				//if($scope.disableSendButton)
//				$scope.cofactorStep = 'cancelled';
//				//$scope.preparedFiles = null;
//				
//				$scope.disableSendButton = false;
			}
			
			$scope.startMultFileProccess = function($queue){
				
				// Test file
				if($scope.useTestFile){
					
					
					// Clear session
					$http.post(
							'apps/docking/action/cofactor-action.php',
				    		{
				    			params:{
				    				'action':'clearCofactorSession'
				    			}
				    		}
				    ).success(function(response){
				    	if (response.status = 'OK'){
							console.log("Clean ligand session test files ok");
							
							//Post test file
							$http.post(
									'apps/docking/action/cofactor-action.php',
									{	
										params:{
											action: 'SAVE-TEST-FILE',
											'file' : $queue[0]
						    			}
									}
								).success(function(response){
							    	if (response.operationStatus = 'SUCCESS'){
							    		
							    		$queue[0].state = 'loading';
										
										$http.post(
									    		'apps/docking/action/cofactor-action.php',				
									    		{
									    			//timeout: $scope.prepareLigandPost[$index].promise,
									    			params:{
									    				'action':'PREPARE',
									    				file: $queue[0]
									    			}
									    		}
									    ).success(function(response){
									    	
									    	$scope.ligandTestStep = "prepared"
									    	$scope.cofactorStep = "prepared";
									    	
									    	// Get new values in prepare file 
									    	$queue[0].state = response.file.state;
									    	$queue[0].validStructure = response.file.validStructure;
									    	
									    	$scope.changesAfterPrepared.testFile.file = angular.copy($queue[0]);
									    	$scope.changesAfterPrepared.testFile.state = false;
									    	
									    	$scope.totalValidStructures = response.file.validStructure;
									    	
									    	//$scope.checkToShowRotbEditor($queue);// response files: array with ONE file (the test file)
								    		
								    		$scope.disableSendButton = true;
								    		
								    		$scope.showRemoveAllButton = true;
								    		//$scope.disableRemoveAllButton = false;
								    		
								    		$scope.showCancellAllButton = true;
								    		$scope.disableCancellAllButton = true;
								    		
								    		$scope.showSecondStep = true;
								    		$scope.showThirdStep = true;
									   });
							    		
									}
								}				    
							);
							
							
							
						}else{
							console.log("clean session files failed!");
						}
				    });
				// User file upload
				}else{
					/// Controlls - for all files
					$scope.totalValidStructures = 0;
					$scope.uploadFilesPrepared = 0;
					
					$scope.uploadFilesTotal = $queue.length;
					$scope.cofactorStep = 'loading';
					// for each file (set index)
					for (var j = 0; j < $queue.length; j++) {
						$queue[j].state = 'loading'
						$queue[j].index = j;
					}
					
					// Show/hide/disable buttons
					$scope.viewButtonsSending();
					
					// Clear session
					$http.post(
							'apps/docking/action/cofactor-action.php',
				    		{
				    			params:{
				    				'action':'clearCofactorSession'
				    			}
				    		}
				    ).success(function(response){
				    	if (response.status = 'OK'){
							console.log("Clean ligand session files ok");
							
//							//Clear alert!
//							for (var i = 0; i < $queue.length; i++) {
//								$queue[i].prepared = true;
//							}
							
							$scope.sendFiles($queue);
							
							$scope.$watch(function() { 
									return $scope.uploadFilesPrepared;
								},
								function(newValue, oldValue) {
									$scope.finishFileFirstStep($queue);
								}
							);
							
							
							
						}else{
							console.log("clean session files failed!");
						}
				    });
				}
				
							
			}
			
			$scope.sendFiles = function($queue){
				
				/// Controlls
				// for all files
				$scope.cofactorStep = 'saving';
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
	              
					$scope.saveLigandPost[$i] = $.ajax({url : 'apps/docking/action/cofactor-action.php',				
						dataType : 'text',
						cache : false,
						contentType : false,
						processData : false,
						data : formData,
						type : 'post',
						success : function(response) {$scope.prepareFile($queue,response)}
					});
				}
			}
			
			$scope.prepareFile = function($queue,response) {
				
				if($scope.cofactorStep != 'cancelled'){
					
					$saveResponse = angular.fromJson(response);
					$index = $saveResponse.file[0].index;
					$queue[$index].saveResult = $saveResponse.file[0].saveResult;
					
					//if($queue[$index].saveResult == "success"){

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
							//$scope.prepareFile($queue,$queue[$index]);
							//$scope.prepareCanceller = $q.defer(); 
							
							$http.post(
						    		'apps/docking/action/cofactor-action.php',				
						    		{
						    			timeout: $scope.prepareLigandPost[$index].promise,
						    			params:{
						    				'action':'PREPARE',
						    				file: $queue[$index]
						    			}
						    		}
						    ).success(function(response){
						    	$saveResponse = angular.fromJson(response);
						    	
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
				    			$scope.changesAfterPrepared.queue.files[$index] = angular.copy($queue[$index]);				    			
				    			//.push('File added: '+$newFileName);
						   });
						});
						
//					} else {
//						
////						$scope.$apply( function (){
////							$scope.cofactorStep = 'sendingError';
////							
////							for(i=0; i < $queue.length; i++ ){
////								$queue[i].errorMessage = $scope.preparedFiles.errorMessage[i];
////							}
////						});
//					}
				}
				
			}
			
			$scope.finishFileFirstStep = function($queue){

				console.log("Finish File First Step");

    			// Check if all file failed
    			if( ($scope.uploadFilesPrepared!=0 || $scope.uploadFilesTotal!=0) && 
    				($scope.uploadFilesPrepared == $scope.uploadFilesTotal)){
    				$scope.cofactorStep = "prepared";
    				$scope.changesAfterPrepared.queue.state = false;
    				
    				if($scope.totalValidStructures!=0){
    					//login role
    	    			if($scope.totalValidStructures<=$scope.roleStructureValid){
    	    				
    	    				$http.post(
						    		'apps/docking/action/cofactor-action.php',				
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
		    	    				
						    	}
						    	
						    });
    	    				
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

			}
			
			
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
					if($scope.cofactorStep == 'prepared'){

						// Check if has hidrogen change from $scope.preparedFiles.files and $queue[j].name
						// Just one change to show the warning message (changesAfterPrepared)
						
						$found = false;
						var qtd = $scope.checkPreparedLength();
						if(qtd == $queue.length){
						//if($scope.changesAfterPrepared.queue.files.length == $queue.length){
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
				
				
				
				
			}
		
			$scope.checkToShowRotbEditor = function($queue){
				
				if($queue.length==1){
					if($scope.totalValidStructures == 1){
						
						for(i=0; i < $queue.length; i++ ){
							if( ($queue[i].validStructure == 1) && ($queue[i].fileExtension != 'top') ){
								$http.post(
							    		'apps/docking/action/cofactor-action.php',				
							    		{
							    			params:{
							    				//'action':'checkIfHasOneTopFile',
							    				'action':'checkTopHasSelectedTorsion',
							    				//'files':$filesArray
							    				'file':$queue[i]
							    			}
							    		}
							    ).success(function(response){
							    	if (response.status == 'SUCCESS'){
							    		$scope.showRotbEditor = true;
										$rootScope.$emit("getJsonFromTopFile",$queue[i].fileId);
									}else{
										$scope.showRotbEditor = false;
									}
							    	console.log("Check if top has selected torsion == 1: " + response.status)
							    });
								break;
							}
						}
						
						
					}else{
						$scope.showRotbEditor = false;
					}
				}else{
					$scope.showRotbEditor = false;
				}
				
			}
			
//REMOVIDO - essa informação ja vem ao executar o mmffligand
//			$scope.checkIfHasOneTop = function($filesArray){
//				
//				$http.post(
//			    		'apps/docking/action/cofactor-action.php',				
//			    		{
//			    			params:{
//			    				'action':'checkIfHasOneTopFile',
//			    				'files':$filesArray
//			    			}
//			    		}
//			    ).success(function(response){
//			    	if (response.checkIfHasOneTopFileResponse == 'TRUE'){
//						$fileNameForEditor = $filesArray[0].name;
//						$rootScope.$emit("getJsonFromTopFile",$fileNameForEditor);
//						$scope.showRotbEditor = true;
//					}else{
//						$scope.showRotbEditor = false;
//						console.log("checkIfHasOneTopFile: false!");
//					}
//			    });
//				
//			}
			
			$scope.sendCofactorToDock = function ($queue){
				
				$scope.sendToDockStatus = "sending";
				
				$http.post(
						'apps/docking/action/cofactor-action.php',
						{	
							params:{
								action: 'SEND-TO-DOCK'
			    			}
						}
					).success(function(response){
						//$scope.$parent.ligandInput = $scope.preparedFiles;
						//$scope.$parent.cofactorInput = $queue;
						$scope.$parent.cofactorInput = angular.copy($queue);
//						$('#sendCofactorToDockModal').modal('show');
						//$scope.$parent.selectedTab = 'COFACTORS';
						
						$scope.sendToDockStatus = "";
						$scope.$parent.selectedTab = 'LIGAND';
					});
			};
			
			$scope.doNotUseCofactors = function() {
				$scope.$parent.cofactorInput = null;
				$scope.$parent.selectedTab = 'LIGAND';
			}
			
			$scope.open3DModal = function($files) {
				//var fileName = $scope.protein.name;
				//var fileNameWithoutExtension = fileName.substr(0, fileName.lastIndexOf('.')) || fileName;
				//var jmolFile = fileNameWithoutExtension + "_prep.pdb";
				
				//$scope.selectedToView = $scope.preparedFiles.files[0];
				$scope.selectedToView = $files[0];
				$('#viewerModal').modal('show');
				$scope.update3DModal();
				//document.getElementById('proteinView').src = "apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName;
				//document.getElementById('view3d').src = "apps/docking/3D-viewer/show3D.php?ligand=1&type=LIGAND&file="+$scope.preparedFiles.files[0].nameWithExtension +"&original="+$scope.preparedFiles.files[0].originalName;
				//window.open("apps/docking/protein-viewer/show3D.php?file="+jmolFile+"&original="+fileName, 'View 3D', 'STATUS=NO,TOOLBAR=NO,LOCATION=NO,DIRECTORIES=NO,RESISABLE=NO,SCROLLBARS=YES,TOP=10,LEFT=10,WIDTH=450,HEIGHT=450');
			};
			
			$scope.open3DModalNGL = function() {
				
				// Debug
				console.log("CofactorController open3DModalNGL ...");
				
				// Show modal
				$('#nglViewerModalCofactor').modal('show');
				
				// Always reload
				document.getElementById('nglViewerIframeCofactor').contentWindow.location.reload(true);
				
			};
			
			$scope.update3DModal = function(){
				document.getElementById('cofactorView').src = "apps/docking/3D-viewer/show3D.php?ligand=1&type=COFACTOR&file="+$scope.selectedToView.fileIdWithExtension;
			};
			
		
			$scope.downloadFile = function ($type){
				//window.location = 'apps/docking/action/cofactor-action.php?action=DOWNLOAD-FILE&type=' + $type;
				window.open('apps/docking/action/cofactor-action.php?action=DOWNLOAD-FILE&type=' + $type);
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
				
			}
			
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
			}
			
			$scope.removeAllFiles = function($queue, $queueTestFile){				
				
				// Clear session
				$http.post(
						'apps/docking/action/cofactor-action.php',
			    		{
			    			params:{
			    				'action':'clearCofactorSession'
			    			}
			    		}
			    ).success(function(response){
			    	if (response.status = 'OK'){
			    		
			    		$scope.resetPage($scope.roleStructureValid);
			    		//clear fileUpload. obs.: inverse for: when a file is removed de indexes are updated (removing first: 0,1,2,3; removing second: 0,1,2; ..)
						for (i = ($queue.length-1); i >= 0; i--) { 
							$queue[i].$cancel();
						}
			    		
						//clear test files:
						delete $queueTestFile[0];
						if($queueTestFile.length>0){
							$queueTestFile.length--;
						}
						
		    			$scope.resetPage($scope.roleStructureValid);
		    			$scope.changesAfterPrepared.queue.state = false;
		    			
		    			console.log("RemoveAllFiles: clean ligand session files ok");
					}else{
						console.log("RemoveAllFiles: clean session files failed!");
					}
			    });
				
				
							
			}
			                             
			$scope.removeFile = function($file,$queue,$queueTestFile){
				console.log("Remove file: "+$file.name+ " ("+$file.fileIdWithExtension+")");
				
				if($file.state=="prepared"){
					$http.post(
				    		'apps/docking/action/cofactor-action.php',				
				    		{
				    			params:{
				    				'action':'removeFile',
				    				'fileIdWithExtension':$file.fileIdWithExtension
				    			}
				    		}
				    ).success(function(response){
				    	if (response.removeFileResponse == 'SUCCESS'){
				    		
				    		console.log('removeFile '+$file.name+': success!');
				    		
				    		//test file (only one file)
				    		if($scope.useTestFile){
				    			delete $queueTestFile[0];
				    			$queueTestFile.length--;
				    			$scope.resetPage($scope.roleStructureValid);
							}
				    		//user uploaded file
				    		else{
				    			
				    			// Remove from queue
								$file.$cancel();
								
								// Remove and update modification list
								delete $scope.changesAfterPrepared.queue.files[$file.index];
								//$scope.changesAfterPrepared.queue.files.length--;
								if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
									$scope.changesAfterPrepared.queue.state = false;
								}else{
									$scope.changesAfterPrepared.queue.state = true;
								}

								// Update total valid structure and number of prepared files and total file selected (add file)
								if($scope.totalValidStructures>0){
									$scope.totalValidStructures = ($scope.totalValidStructures - $file.validStructure);
									$scope.uploadFilesPrepared -=1;
								}
								$scope.uploadFilesTotal -= 1;
								
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
									$scope.cofactorStep = 'stopped';
									$scope.changesAfterPrepared.queue.state = false;

								}else{
									
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
							}
				    		
//						    		//remove file from prepared files
//						    		if($scope.preparedFiles.files != undefined){
//						    			$scope.preparedFiles.files.splice($fileArrayIndex, 1);
//						    		}				    		
				    		
//						    		//Check if has files, if not, back to step 1
//						    		if($scope.preparedFiles.files.length == 0){ 
//						    			$scope.cofactorStep = 'stopped';
//						    			//$scope.showRotbEditor = false;
//						    		}
							
						}else{
							console.log('RemoveFile '+$file.name+': failed!');
						}
				    });	
				}else{
					
					//test file (only one file)
		    		if($scope.useTestFile){
		    			delete $queueTestFile[0];
		    			$queueTestFile.length--;
		    			$scope.resetPage($scope.roleStructureValid);
		    			
					}
		    		//user uploaded file
		    		else{
		    			$file.$cancel();
						$scope.uploadFilesTotal--;
						$scope.uploadFilesPrepared--;
						
						// Remove and update modification list
						//delete $scope.changesAfterPrepared.queue.files[$file.index];
						//if($scope.changesAfterPrepared.queue.files.length>0){
						//	$scope.changesAfterPrepared.queue.files.length--;
						//}
						if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
							$scope.changesAfterPrepared.queue.state = false;
						}else{
							$scope.changesAfterPrepared.queue.state = true;
						}
						
						if($scope.uploadFilesTotal==0){
							$scope.resetPage($scope.roleStructureValid);
						}else{
							if($scope.uploadFilesTotal == $scope.uploadFilesPrepared){
								$scope.changesAfterPrepared.queue.state = false;
								$scope.disableSendButton = true;
								$scope.disableCancellAllButton = true;
							}else{
								$scope.changesAfterPrepared.queue.state = true;
							}
						}
		    		}
					
					
					
				}
				
			}
			
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
		    			
		    			if($scope.cofactorStep == 'prepared'){
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
			}		
			
			$scope.checkTotalValidStructure = function() {
				if($scope.totalValidStructures>100){
					$scope.showSecondStep = false;
					$scope.showThirdStep = false;
					$scope.disableSendButton = false;
					$scope.showValidStructureAlert = true;
				}
			}
			
			$scope.viewButtonsSending = function(){
				$scope.showAllProgressFragment = true;
				$scope.showCancellAllButton = true;
				$scope.showRemoveAllButton = true;
				//$scope.disableRemoveAllButton = true;
				$scope.disableCancellAllButton = false;
				$scope.disableSendButton = true;
				
			}
			
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
			
	}]);
