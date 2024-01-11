app.controller("ResultsController", [ '$rootScope', '$scope', '$http', '$timeout', '$window', 'appInfo',
		function($rootScope, $scope, $http, $timeout, $window, appInfo) {
			
			console.log("ResultsController");
			
			$scope.$parent.job.proteinInput = proteinInputFromProperties;
			$scope.$parent.job.ligandInput = ligandInputFromProperties;
			$scope.$parent.job.cofactorsInput = cofactorsInputFromProperties;
			
			$scope.table = [];
			
			$scope.setMaxBindingValue = function(){
				if($scope.$parent.job.ligandInput.length > 1){
					return 3;
				} else {
					return 10;	
				}
			};
			
			$scope.maxBindingValue = $scope.setMaxBindingValue();
			$scope.minBindingValue = 1;
			
			// Load 3D viewer type (ngl or jsmol)
			appInfo.appViewer().then(
				function(response){
					$scope.viewerType = response.data.data;

					if($scope.viewerType=='jsmol'){
						$scope.$watch('view3DProtein', function(newValue) {
							if (typeof document.getElementById('results3DView').contentWindow.showProtein === "function") { 
								if($scope.view3DProtein){
									document.getElementById('results3DView').contentWindow.showProtein();
								} else {
									document.getElementById('results3DView').contentWindow.hideProtein();
								}
							}
						});
						
						$scope.$watch('view3DCofactors', function(newValue) {
							if (typeof document.getElementById('results3DView').contentWindow.showCofactors === "function") { 
								if($scope.view3DCofactors){
									document.getElementById('results3DView').contentWindow.showCofactors();
								} else {
									document.getElementById('results3DView').contentWindow.hideCofactors();
								}
							}
						});
						
						$scope.$watch('view3DReference', function(newValue) {
							if (typeof document.getElementById('results3DView').contentWindow.showReference === "function") { 
								if($scope.view3DReference){
									document.getElementById('results3DView').contentWindow.showReference();
								} else {
									document.getElementById('results3DView').contentWindow.hideReference();
								}
							}
						});
						
						$scope.$watch('ligandSelected', function(newValue) {
							if (typeof document.getElementById('results3DView').contentWindow.showLigand === "function") { 
								document.getElementById('results3DView').contentWindow.showLigand(newValue);
							}
						});
					}

				},
				function(response) {
			        console.error("Could not load the app info");		        
			    }
			);
			
			$scope.rmsd = 2.0;
			$scope.bindingModes = 3.0;
			$scope.referenceFile = null;
			$scope.referenceUploaded = false;
			$scope.disableRmsd = false;
			
			$scope.analyzeStatus = 'stopped';
			
			if($scope.$parent.job.ligandInput.length > 1){
				//$scope.bindingModes = 1;
				$scope.disableRmsd = true;
			}
			
			$scope.addFile = function($input){
				if($("#" + $input.id).val() != null){
					$scope.$apply(function() {
						$scope.referenceFile = $("#" + $input.id).val().replace(/.*[\/\\]/, '');						
					});
				}
			};
			
			$scope.analyzeIsDisabled = function() {
				return (
					($scope.analyzeStatus == 'analyzing') ||
					($scope.bindingModes > $scope.maxBindingValue) ||
					($scope.bindingModes < $scope.minBindingValue)
				       );					
			};
			
			$scope.removeFile = function() {
				$inputFile = $("#referenceInputFile");
				$scope.referenceFile = null;
				$inputFile.val(null);
			};
			
			$("#resultsForm").submit(function () {
				$scope.analyzeStatus = 'analyzing';
				var view3D = "";
				$scope.viewerSrc = view3D;
				$scope.view3DProtein = true;
				$scope.view3DCofactors = true;
				$scope.view3DReference = true;
				
				var formData = new FormData(this);
				$.ajax({
			        url: "apps/docking/action/result-action.php",
			        type: 'POST',
			        data: formData,
			        success: function (response) {
						
						var jsonResponse = JSON.parse(response);
			        	if(jsonResponse.status == "SUCCESS"){

							$scope.$apply(function() {
								
								$scope.analyzeStatus = "complete";	
								$scope.elements = jsonResponse.elements;
								
								// Ordering
								$scope.elements.sort(function(a, b){ 
									return a.elements[0].score - b.elements[0].score;
								});

								// Reference file
								$scope.referenceFile = $("#referenceInputFile").val().replace(/.*[\/\\]/, '');
								
								if($scope.viewerType=='jsmol'){

									$scope.table = [];									
									var ligands = [];
									$scope.elements.forEach(function(ligand) {
										//	$scope.table.push(ligand.elements[0]);
										var item = {name : ligand.name, showPoses : false};
										item.poses = [];
										
										ligand.elements.forEach(function(line, index) {
											line.score = parseFloat(line.score);	
											item.poses.push(line);
										});
										ligands.push(item.name);
										
										$scope.table.push(item);
									});

									if($scope.referenceFile != null && $scope.referenceFile != ''){
										view3D = 'apps/docking/3D-viewer/show3D.php?type=RESULTS&file='+ $scope.$parent.job.proteinInput[0] + '&reference=' +$("#referenceInputFile").val().replace(/.*[\/\\]/, '') + '&jobID=' + $scope.$parent.job.id;
										$scope.referenceUploaded = true;
									} else {
										view3D = 'apps/docking/3D-viewer/show3D.php?type=RESULTS&file='+ $scope.$parent.job.proteinInput[0] + '&jobID=' + $scope.$parent.job.id;
										$scope.referenceUploaded = false;
									}
									
									view3D += '&ligands=true';
									
									if($scope.$parent.job.cofactorsInput != null && $scope.$parent.job.cofactorsInput.length > 0){
										view3D += '&cofactors=' + JSON.stringify($scope.$parent.job.cofactorsInput);
									}
									
									$scope.viewerSrc = view3D;																	
									$scope.ligandSelected = "2.1";									
								
								} else if($scope.viewerType=='ngl'){
									$scope.loadNgl($scope.elements, $scope.$parent.job.id, $scope.referenceFile);
								}
							});
							
							
			        			
			        }else if(jsonResponse.status == "ERR"){
					
								console.log(jsonResponse.errorMessage);
								
								$scope.$apply(function() {
									
								//$scope.messageError(jsonResponse.errorMessage);
								
								//$scope.analyzeNotSolution=jsonResponse.errorMessage;
								
								$scope.analyzeStatus = "error";
								//bootbox.alert({ 
								//	title: "WARNING",
								//	message: jsonResponse.errorMessage,
								//	backdrop: true
								//});
								});
							}},
			        cache: false,
			        contentType: false,
			        processData: false,
			        xhr: function() {  // Custom XMLHttpRequest
			            var myXhr = $.ajaxSettings.xhr();
			            if (myXhr.upload) { // Avalia se tem suporte a propriedade upload
			                myXhr.upload.addEventListener('progress', function () {
			                    /* faz alguma coisa durante o progresso do upload */
			                }, false);
			            }
			        return myXhr;
			        }
			    });
			});
			
			/** Docking NGL App */
			$scope.loadNgl = function(elements, jobId, nglReferenceFile){
				
				// dummyVar - Used to build a dummy var, only to force the angular to reload (https://stackoverflow.com/questions/30830406/how-to-refresh-iframe-url?rq=1)
				var d = new Date(); 	
				var t = d.getTime();

				// Result element
				var resultElements = JSON.stringify(elements);

				
				// Reference file
				var referenceFileName = '';
				if( (nglReferenceFile!=undefined) && (nglReferenceFile != null) ){
					referenceFileName = nglReferenceFile;
				}
				
				// Frame URL
				$iframeUrl = "apps/docking/3D-viewer-ngl/view/nglViewerResults.php?jobId='"+jobId+"'&resultElements="+resultElements+"&referenceFileName='"+referenceFileName+"'&dummyVar="+ t;
				
				// Load URL
				$scope.srcViewerResultsNgl = $iframeUrl;
			};
			
			$scope.downloadResults = function() {
				//	$http.post(
				//		'apps/docking/action/result-action.php',
				//		{
				//			params:{
				//				'action':'DOWNLOAD-RESULTS',
				//			    "jobId" : $scope.$parent.$parent.job.id,
				//			}
				//		}
				//		).success(function(response){
				//		   	console.log(response);
				//		});
				
				//window.location = 'apps/docking/action/result-action.php?action=DOWNLOAD-RESULTS&jobId=' + $scope.$parent.$parent.job.id;				
				window.open('apps/docking/action/result-action.php?action=DOWNLOAD-RESULTS&jobId=' + $scope.$parent.$parent.job.id);
				 
			}
			
			$rootScope.$on("getJobStatus", function() {
				$scope.getJobStatus();
			});

			
			$scope.getJobStatus = function() {
				if($scope.$parent.$parent.job.id != null){
					$http({
						method : "POST",
						url : "apps/docking/job-status.php",
						data : {
							"jobId" : $scope.$parent.$parent.job.id,
						}
					}).then(function successCallback(response) {
						$scope.$parent.$parent.job.status = response.data;
						var status = response.data;
						
						if($scope.$parent.$parent.job.status != "SUCCESS" && $scope.$parent.$parent.job.status != "ERROR"){
							$scope.$parent.$parent.job.status = "RUNNING";
							$timeout($scope.getJobStatus, 5000);
						}
						
					}, function errorCallback(response) {
	
					});
				}
			};
			
			$scope.removeAllFilesWithConfirmation = function($jobId){
				
				bootbox.confirm({ 
					  size: "small",
					  message: "You will remove all files from this job and redirect to homepage. After this action, you will cannot access this job again. Are you sure?", 
					  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/
						  if(result){
							  
							  console.log("Remove all files from job " + $jobId + " ...");
								$http.get(
										'apps/docking/action/result-action.php',
							    		{
											params:{
												'action':'REMOVE-ALL-FILES-FROM-JOB',
												'jobId':$jobId
											}
							    			
							    		}
							    ).success(function(response){
							    	if (response.status = 'SUCCESS'){
							    		
							    		console.log("Remove all files from "+$jobId+": SUCCESS!");
							    		//console.log(location.origin+location.pathname);
							    		
							    		// Refresh
							    		//location.reload();
							    		
							    		// Home page
							    		$window.location.href = location.pathname;
									
									}else{
										console.log("Remove all files from "+$jobId+": FAILED!");
									}
							    });
						  }
					  }
				});	
			}
			
}]);
