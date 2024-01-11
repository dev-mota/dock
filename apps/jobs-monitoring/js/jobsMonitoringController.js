app.controller("JobsMonitoringController-v2", [ '$scope','$http', '$filter', 
	function($scope, $http, $filter) {
	
	$(document).ready(function(){
		  $('[data-toggle="tooltip"]').tooltip(); 
		});
	
	$scope.jobsPerPageList = [10, 15, 20, 50, 100];
	$scope.jobsPerPage = 10;
	$scope.from = 0;
	$scope.totalJobs = 0;
	$scope.pageIndexArray = new Array();
	$scope.lastPageIndex = null;
	
	$scope.currentPage = 1;
	$scope.previousUsage = 'disabled';
	$scope.nextUsage = ''; // Habilitar botao ('')
		
	$scope.propertyFileContent = null;
	
	$scope.jobServiceActionModel = null;
	
	$scope.modalNotification = new Array();
	$scope.modalNotification.header = "Undefided";
	$scope.modalNotification.message = "Undefided";
	
	// Filter job status
	$scope.filterList = ['any', 'cancelled', 'checking', 'downloading', 'downloading_error', 'error', 'paused', 'pending','success', 'running', 'no folder'];
	$scope.selectedFilter = 'any';
	
	// Filter number of ligands
	$scope.filterNumberOfLigantsOption = ['any','1','>1'];
	$scope.selectedFilterNumberOfLigant = 'any';
	
	// Filter by date
	$scope.jobDateFrom = null;
	$scope.jobDateTo = null;
	
	// Search variables
	$scope.jobNameToSearch = null;
	
	
	$scope.changeJobsPerPage = function($value) {
		$scope.jobsPerPage = $value;
		$scope.getJobsInfo(0,$scope.jobsPerPage, $scope.selectedFilter, true);
	}
	
	$scope.changeFilter = function($value){
		$scope.selectedFilter = $value;
		$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, true);		
	}
	
	$scope.changeFilterLigantQuantity = function($value){
		$scope.selectedFilterNumberOfLigant = $value;
		$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, true);
	}
	
	$scope.resetPagination = function(){
		$scope.from = 0;	
		$scope.currentPage = 1;
		
		// Sempre o anterior estara desabilitado, pois sera a primeira pagina
		$scope.previousUsage = 'disabled';
		
		// Caso todos os jobs estejam em uma pagina, desabilitar botao next
		if($scope.totalJobs<=$scope.jobsPerPage){
			$scope.nextUsage = 'disabled';
		}else{
			$scope.nextUsage = '';
		}
	}
	
	$scope.calcNumberOfPages = function() {		
		var pagesNum = Math.ceil($scope.totalJobs/$scope.jobsPerPage);
		//var pagesNum = Math.ceil(3000/$scope.jobsPerPage);
		var arrayInt = null;
		if(pagesNum<10){
			$scope.pageIndexArray = new Array(pagesNum);
		}else{
			$scope.pageIndexArray = new Array(10);
		}   
	}
	
	$scope.getJobsInfo = function (from, jobsPerPage, $selectedFilter, $resetPagination) {
		$http.post(
				'apps/jobs-monitoring/action/jobsMonitoringAction.php',
				{					
					'action': 'getJobsInfoWithParam',
					'from' : from,
					'jobsPerPage':jobsPerPage,
					'selectedFilter': $selectedFilter,
					'jobNameToSearch': $scope.jobNameToSearch,
					'selectedFilterNumberOfLigant': $scope.selectedFilterNumberOfLigant,
					'jobDateFrom': $scope.jobDateFrom,
					'jobDateTo': $scope.jobDateTo
				}				
		).success(function(data, status, headers, config){
            if(status == "200" && data.status == "OK"){
            		
            		$scope.totalJobs = data.totalJobs;
            		
            		// Por algum motivo desconhecido, quando se usa paginacao, os arrays estao vindo sem o tamanho!
            		// Logo o forEach eh apenas um contorno para o problema
            		// console.log(data.jobs);
            		// $scope.jobsArray = data.jobs;
            		// $scope.jobsArray.length = Object.keys(data.jobs).length;
            		// console.log($scope.jobsArray);
            		$scope.jobsArray = [];
            		angular.forEach(data.jobs, function($jobValue, $jobKey) {        				
            			$scope.jobsArray.push($jobValue);        				
        			});
            		// console.log($scope.jobsArray);
            		
            		// Contagem dos numeros ao topo da paginacao
            		$scope.calcNumberOfPages();
            		
            		if($resetPagination){
            			$scope.resetPagination();
            		}            		
            		
            		// Scroll down submission jobs tables
            		//$scope.scrollDownSubmissionJobsTables();
            		
            		$scope.lastPageIndex = Math.ceil($scope.totalJobs/$scope.jobsPerPage) ;

            }
		}).error(function(data, status, headers, config){
			console.log('error getJobsInfoWithParam');			
		});			
	};	
	
	
	$scope.cancelJob = function (jobServiceActionModel){
//		$http.post('apps/jobs-monitoring/action/cancel-job.php', { params :{'serviceJobId' : serviceId}}).success(function(data, status, headers, config) {
//        		if(data == "SUCCESS"){
//        			alert("Job " + serviceId + " cancelled\nThe job status will change on the next execution of daemon");
//    			} else {
//    				alert("Some error occur on cancel job " + data);
//    			}
//        		$scope.getJobsInfo();
//        });
		
		var jobServiceId = "Dock@Dock."+jobServiceActionModel.serviceId;
		
		bootbox.confirm({ 
			  size: "small",
			  message: "Cancel job?", 
			  callback: function(result){ 
				  if(result){
						$http.post(
								'apps/jobs-monitoring/action/jobsMonitoringAction.php',
								{					
									'action': 'cancelJob',
									'serviceJobId' : jobServiceId
								}				
						).success(function(data, status, headers, config){
				            if(status == "200" && data.status == "OK"){
				            	
				            		console.log("cancelJob success!");
				            		$scope.modalNotification.header = "Success";
				            		$scope.modalNotification.message = "The job "+jobServiceId+" was successfully cancelled! Wait for daemon update th job status, and refresh again!";
				            		$('#showJobServiceModal').modal('hide');
				            		$('#modalSuccess').modal('show');
				            		
				            }else{
				            		console.log("cancelJob some error occurred!");
				            		$scope.modalNotification.header = "Error";
				            		$scope.modalNotification.message = data.errormessage;
				            		$('#showJobServiceModal').modal('hide');
				            		$('#modalSuccess').modal('show');				            		
				            }
						}).error(function(data, status, headers, config){
							console.log("cancelJob failed!");	
							$scope.modalNotification.header = "Error";
		            			$scope.modalNotification.message = "An internal error occurred!";
		            			$('#showJobServiceModal').modal('hide');
		            			$('#modalSuccess').modal('show');
		            			
						});	
					}
			  }
		});
		
	}
	
	$scope.resubmitJob = function (portalJobId){
		
		bootbox.confirm("Resubmit job? <br><br> Warning: The job will be moved to 'pending' folder.", function(result){
			if(result){
				$http.post(
						'apps/jobs-monitoring/action/jobsMonitoringAction.php',
						{					
							'action': 'resubmitJob',
							'portalJobId' : portalJobId
						}				
				).success(function(data, status, headers, config) {
					$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, true);
					if(status == "200" && data.status == "OK"){
						// bootbox.alert("Success: The job " + portalJobId + " will be resubmitted on the next daemon execution!");
						$scope.modalNotification.header = "Success";
	            			$scope.modalNotification.message = "The job was resubmited! (moved to pendng folder)";
	            			$('#modalSuccess').modal('show');
	            			
					}else{
						// bootbox.alert("Some error occurred!");
						$scope.modalNotification.header = "Error";
            				$scope.modalNotification.message = "The job "+portalJobId+" could not be resubmited";
            				$('#modalSuccess').modal('show');
						
					}
					
				});
			}
		});		

	}
	
	$scope.refresh = function(){
		$scope.changeJobsPerPage($scope.jobsPerPage);		
	}
	
	$scope.firstPage = function (){
		$scope.changeJobsPerPage($scope.jobsPerPage);
	}
	
	$scope.previousPage = function(){
		if($scope.previousUsage!='disabled'){
			$scope.from -= $scope.jobsPerPage;
			$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, false);
			$scope.currentPage--;
			$scope.nextUsage = ''; // Habilitar botao ('')
			
			if($scope.currentPage==1){
				$scope.previousUsage = 'disabled';
			}else{
				$scope.previousUsage = ''; // Habilitar botao ('')
			}
		}

	}
	
	$scope.nextPage = function(){
		
		if($scope.nextUsage!='disabled'){
			$scope.from += $scope.jobsPerPage;
			$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, false);
			$scope.currentPage++;
			$scope.previousUsage = ''; // Habilitar botao ('')
			
			// console.log(($scope.jobsPerPage * $scope.currentPage) + " " + $scope.totalJobs);
			if( ($scope.jobsPerPage * $scope.currentPage)  >= $scope.totalJobs){
				//console.log('nextUsage disabled');
				$scope.nextUsage = 'disabled';
			}else{
				//console.log('nextUsage NOT disabled');
				$scope.nextUsage = ''; // Habilitar botao ('')
			}			
		}
		
	}
	
	$scope.lastPage = function (){
		
		$scope.currentPage = Math.ceil($scope.totalJobs/$scope.jobsPerPage) ;
		
		var lastFromIndex = (Math.floor($scope.totalJobs/$scope.jobsPerPage)*$scope.jobsPerPage);
		$scope.from = lastFromIndex;
		$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, false);
		// Desabilitar o next, pois sera a ultima pagina
		$scope.nextUsage = 'disabled';
		
		// Caso todos os jobs estejam em uma pagina, desabilitar botao next
		if($scope.totalJobs<=$scope.jobsPerPage){
			$scope.previousUsage = 'disabled';
		}else{
			$scope.previousUsage = '';
		}
	}
	
	$scope.showPropertyFile = function($portalJobId){
		$http.post(
				'apps/jobs-monitoring/action/jobsMonitoringAction.php',
				{					
					'action': 'showPropertyFile',
					'portalJobId' : $portalJobId
				}				
		).success(function(data, status, headers, config){
            if(status == "200" && data.status == "OK"){
            		
            		$scope.propertyFileContent = data.propertyContent;
            		// console.log($scope.propertyFileContent);
            		$('#showPropertyModal').modal('show');
            		
            		
            }
		}).error(function(data, status, headers, config){
			console.log('error');			
		});	
	}
	
	$scope.showPropertyFileTest = function($testType){
		$http.post(
				'apps/jobs-monitoring/action/jobsMonitoringAction.php',
				{					
					'action': 'showPropertyFileTest',
					'testType' : $testType
				}				
		).success(function(data, status, headers, config){
            if(status == "200" && data.status == "OK"){
            		
            		$scope.propertyFileContent = data.propertyContent;
            		// console.log($scope.propertyFileContent);
            		$('#showPropertyModal').modal('show');
            		
            		
            }
		}).error(function(data, status, headers, config){
			console.log('error');			
		});	
	}
	
	$scope.checkIfHasJobActive = function($submissions){
		
		// console.log("================");
		$result = true;
		
		if( ($submissions != undefined) && ($submissions.pending == undefined) ){
			
			// console.log($submissions);
			angular.forEach($submissions, function($submission, $submissionKey) {
    			
				// console.log($submission);				
				$status = $submission['job-service-status'];
				if( ($status == "RUNNING") || ($status == "PENDING") ){								
					$result = false;
				}
				
			});
		}
		return $result;
	}
	
	$scope.checkAndPrintSubmissionId = function($value){
		if( ($value == undefined) || ($value == 'unavailable') || ($value == 'pending')){
			// return "[submission id unavailable]"
			return "N/A"
		}else{
			return $value.split("@Dock.")[1];
		}
	}
	
	$scope.checkAndPrintDate = function($submissionDate){
		// console.log($submissionDate);
		if( ($submissionDate == undefined) || ($submissionDate == 'unavailable') ){
			return "N/A"
		}else{
			return $filter('date')($submissionDate, "yyyy-MM-dd HH:mm:ss");
		}		
	}
	
	$scope.checkAndPrintResource = function($resource){
		if( ($resource == undefined) || ($resource == 'unavailable') ){
			return "N/A"
		}else{
			return $resource.split("_")[1];
		}
	}
	
	$scope.checkAndPrintStatus = function($value){
		if( ($value == undefined) || ($value == 'unavailable') ){
			return "N/A"
		}else{
			return $value;
		}
	}
	
	$scope.checkAndPrintNumLig = function($value){
		if( ($value == undefined) || ($value == 'unavailable') ){
			return "N/A"
		}else{
			return $value + 'L';
		}
	}
	
	$scope.jobServiceAction = function($serviceId, $portalId, $serviceStatus){
		$scope.jobServiceActionModel = [];
		$scope.jobServiceActionModel.serviceId = $serviceId;
		$scope.jobServiceActionModel.portalId = $portalId;
		$scope.jobServiceActionModel.serviceStatus = $serviceStatus;
	}
	
	$scope.downloadResults = function($jobServiceActionModel){
		
		$http.post(
				'apps/jobs-monitoring/action/jobsMonitoringAction.php',
				{					
					'action': 'checkResultForDownload',
					'portalId' : $jobServiceActionModel.portalId,
					'serviceId': $jobServiceActionModel.serviceId
				}				
		).success(function(data, status, headers, config){
			if(status == "200" && data.status == "OK"){
				console.log("checkResultForDownload success!");	
		    		window.location = 'apps/jobs-monitoring/action/jobsMonitoringAction.php?action=downloadResults&portalId='+$jobServiceActionModel.portalId+'&serviceId='+$jobServiceActionModel.serviceId;
		    }
		}).error(function(data, status, headers, config){
			console.log("checkResultForDownload failed!");			
		});	
		
	}
	
	$scope.scrollDownSubmissionJobsTables = function(){
//		console.log("scrollDownSubmissionJobsTables");
//		$(document).ready(function() {
//			$('div[id^="table"]').scrollTop($('#tableSubmissions')[0].scrollHeight);
//		});
	}
	
	// Funcao necessaria, pois algumas vezes jobKey era tratado como string (ex.: 10+1=101)
	$scope.buildIndex = function(jobKey, index){
		return parseInt(jobKey) + 1 + $scope.from;		
	}
	
	$scope.searchJob = function(){
		$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, true);		
	}
	
	$scope.clear = function(){
		$scope.jobsPerPage = 10;
		$scope.from = 0;
		// $scope.totalJobs = 0;
		$scope.currentPage = 1;
		$scope.previousUsage = 'disabled';
		$scope.nextUsage = ''; // Habilitar botao ('')			
		$scope.propertyFileContent = null;		
		$scope.jobServiceActionModel = null;
		$scope.selectedFilter = 'any';
		$scope.selectedFilterNumberOfLigant = 'any';
		$scope.jobNameToSearch = '';
		
		// Clear date from and to
		$scope.jobDateFrom = null;
		$scope.jobDateTo = null;
		
		$scope.getJobsInfo($scope.from, $scope.jobsPerPage, $scope.selectedFilter, true);
	}
	
	$scope.submitTestJob = function($type){
		
		$header = "";
		if($type=='short1lig'){
			$header="Submit 1 ligant (short)";
		}else if($type=='complete1lig'){
			$header="Submit 1 ligant (real)";
		}else if($type=='shortVs'){
			$header="Submit VS (short)";
		}else if($type=='completeVs'){
			$header="Submit VS (real)";
		}
		
		bootbox.confirm('<div style="text-align: center"><b><i>'+ $header+'</i></b><br>The job will be queued and executed on the next minute.</div>', function(result){			
			if(result){
				
				console.log("submitTestJob("+ $type+") ....");
				
				$http.post(
						'apps/jobs-monitoring/action/jobsMonitoringAction.php',
						{					
							'action': 'submitTestJob',
							'testType': $type
						}				
				).success(function(data, status, headers, config){
		            if(status == "200" && data.status == "OK"){
		            		$scope.refresh();
		            		console.log("submitTestJob success!");
		            		bootbox.alert('<div style="text-align: center">Success!</div>');
		            }else{
		            		bootbox.alert('<div style="text-align: center">Failed! ('+data.error+')</div>');
		            }
				}).error(function(data, status, headers, config){
					console.log("submitTestJob failed!");
					bootbox.alert('<div style="text-align: center">Error! ('+data.error+')</div>');
				});
			}				
		});
		
		
	}
	
	/*
	$scope.getJobStatus = function($jobServiceActionModel){
		
		console.log("updateJobStatus .... ");
		
		$http.post(
				'apps/jobs-monitoring/action/jobsMonitoringAction.php',
				{					
					'action': 'updateJobStatus',
					'portalId' : $jobServiceActionModel.portalId,
					'serviceId': $jobServiceActionModel.serviceId
				}				
		).success(function(data, status, headers, config){
			if(status == "200" && data.status == "OK"){
				console.log("updateJobStatus success!");
				//TODO update page
		    }
		}).error(function(data, status, headers, config){
			console.log("updateJobStatus failed!");			
		});	
	}*/
	
}]);

app.directive('aDisabled', function() {
    return {
        compile: function(tElement, tAttrs, transclude) {
            //Disable ngClick
            tAttrs["ngClick"] = "!("+tAttrs["aDisabled"]+") && ("+tAttrs["ngClick"]+")";

            //return a link function
            return function (scope, iElement, iAttrs) {

                //Toggle "disabled" to class when aDisabled becomes true
                scope.$watch(iAttrs["aDisabled"], function(newValue) {
                    if (newValue !== undefined) {
                        iElement.toggleClass("disabled", newValue);
                    }
                });

                //Disable href on click
                iElement.on("click", function(e) {
                    if (scope.$eval(iAttrs["aDisabled"])) {
                        e.preventDefault();
                    }
                });
            };
        }
    };
});

app.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            element.hover(function(){
                // on mouseenter
                element.tooltip('show');
            }, function(){
                // on mouseleave
                element.tooltip('hide');
            });
        }
    };
});
