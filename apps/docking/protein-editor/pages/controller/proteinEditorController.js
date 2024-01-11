//var app = angular.module('ProteinEditorApp', []);

app.controller('ProteinEditorController',function($rootScope, $scope, $http, $parse) {
	
	//$('.alert-success').hide();
	//$('.alert-danger').hide();
	$scope['count_all'] = 0;
	$scope.proteinEditLog = {};
	$scope.proteinEditLogBuffer = {};
	$scope.proteinLog = true;
	
	// function to get json from pdb file and chain files
	$scope.getJsonFromPdbFIleAndChainFiles = function($pdbFileRandomId,$chainFileNamesArray){
		//console.log("pdbFileRandomId: "+$pdbFileRandomId);
	    $http.get(
	    		'apps/docking/protein-editor/pages/action/proteinEditorAction.php', 
	    		{
	    			params:{
	    				'type':'getJson',
	    				'pdbFileRandomId':$pdbFileRandomId,
	    				'chainFileNamesArray[]':$chainFileNamesArray
	    			}
	    		}
	    ).success(function(response){
	        if(response.status == 'OK'){
	        	
	        	$scope.chainsOriginalJsonFile = angular.fromJson(response.json);
	        	$scope.chains = angular.copy($scope.chainsOriginalJsonFile);
	        	
	        	//console.log($scope.chains);//debug
	        }else if(response.status == 'ERR'){
	        	$scope.$parent.protein.error = "Invalid structure";
	        	$scope.$parent.protein.prepared = false;
	        	$scope.messageError('Failed to parse to json');
	        }
	    });
	};

	//declaration to be used by other angular controller. In this case: DockThor-3.0/apps/docking/js/protein.js
	$rootScope.$on("getJsonFromPdbFIleAndChainFiles", function(event, $pdbFileRandomId,$chainFileNamesArray) {
		$scope.getJsonFromPdbFIleAndChainFiles($pdbFileRandomId,$chainFileNamesArray);
		$scope.chainFileNamesArray = $chainFileNamesArray;
	});
	
	// function send editted JSON 	    
	$scope.sendJsonEditted = function(){
		
		$http.post(
	    		'apps/docking/protein-editor/pages/action/proteinEditorAction.php', 
	    		{
	    			params:{
	    				'type':'sendJson',
	    				'edittedJson':angular.toJson($scope.chains,true),
	    				'pdbFileRandomId':$scope.$parent.protein.codedName,
	    				'chainFileNamesArray':$scope.chainFileNamesArray
	    			}
	    		}
	    ).success(function(response){
	        if(response.status == 'OK'){
	        	$http({
	        		method: 'POST',
					url: 'apps/docking/pdbthorbox/pdbthorbox-controller.php',
					data: {
						action : 'REPREPARE',
						fileName : $scope.$parent.protein.codedName,
					  }
	        		}).then(function successCallback(response) {
	        			//console.log(response);
	        			if(response.status == 200){
		        			$result = response.data;
							if($result.operationStatus == 'SUCCESS'){
			        			$scope.getJsonFromPdbFIleAndChainFiles($scope.$parent.protein.codedName,$scope.chainFileNamesArray);
			        			$scope.resetChains();
			        			//log
			        			$scope.proteinLog = false;
			        			//console.log($scope.proteinEditLogBuffer);
			        			angular.copy($scope.proteinEditLogBuffer,$scope.proteinEditLog);
			        			$scope.proteinEditLogBuffer={};
			        			
			        			//Protein file ' + $scope.$parent.proteinInput.name + ' was uploaded with success
			        			//$scope.messageSuccess('Success! (chains files updated and pdp prepared)');
							} else {
								$scope.$parent.protein.prepared = false;
								$scope.$parent.protein.error = $result.errorMessage;
							}
	        			}
	        		}, function errorCallback(response) {
	        			$scope.$parent.protein.error = "Invalid structure";
	        			$scope.$parent.protein.prepared = false;
	        			$scope.messageError('Failed to reprepare files!');
	        		});
	        }else{
	        	$scope.$parent.protein.prepared = false;
	        	$scope.messageError('Failed to update pdb and chains files!)');
	        }
	    });
	};
	
	$scope.checkChanges = function($chainKey,$atomKey, $index, $selection){
		//console.log($scope.chainsOld);
		//console.log("Test get array: "+ $chainKey + " > "+$atomKey + " > " + $index + " > "+$selection);
		//console.log("original: " + $scope.chainsOriginalJsonFile[$chainKey][$atomKey][$index]['state']['value']);
		//console.log("modified: " + $scope.chains[$chainKey][$atomKey][$index]['state']['value']);
		$original = $scope.chainsOriginalJsonFile[$chainKey][$atomKey][$index]['state']['value'];
		$modified = $scope.chains[$chainKey][$atomKey][$index]['state']['value'];
		
		//set modified index (set 1 or 0)
		$indexId = 'count_'+$chainKey+$atomKey+$index;
		if($original!=$modified){
			//console.log("modified");			
			//index
			if($scope[$indexId] === undefined || $scope[$indexId]==0){
				$scope[$indexId] = 1;
			}
			console.log("Adding to proteinEditLogBuffer: "+$chainKey+$atomKey+$index+$selection);
			$scope.proteinEditLogBuffer[$chainKey+$atomKey+$index] = {'chain':$chainKey,'atom':$atomKey,'index':$index, 'before':$original, 'after':$selection};			
		}else{
			$scope[$indexId] = 0;
			console.log("Removing to proteinEditLogBuffer: "+$chainKey+$atomKey+$index);
			//$scope.proteinEditLog.splice($chainKey+$atomKey+$index,1);
			delete $scope.proteinEditLogBuffer[$chainKey+$atomKey+$index]; // the "delete" is javascript, not angular
			
		}
		
		//console.log("proteinEditLogBuffer:")
		//console.log($scope.proteinEditLogBuffer);
		
		///// atom diplay how many index was changed
		$indexCount = 0;
		angular.forEach($scope.chains[$chainKey][$atomKey], function(value, key) {
//			console.log(key + ': '+ $scope['count_'+$chainKey+$atomKey+key]);
			$indexIdTemp = 'count_'+$chainKey+$atomKey+key;
			if($scope[$indexIdTemp]==1){
				//console.log(key + ': '+ $scope[$indexIdTemp]);
				$indexCount++;
			}
		});
		//console.log($indexCount);
		$scope['count_'+$chainKey+$atomKey] = $indexCount;

		////// chain display how many atoms was changed
		$chainCount = 0;
		//console.log("---");
		angular.forEach($scope.chains[$chainKey], function(value, key) {
			//console.log(key + ': '+ $scope['count_'+$chainKey+key]);
			$chainIdTemp = 'count_'+$chainKey+key;
			if($scope[$chainIdTemp] != undefined){
				//console.log(key + ': '+ $scope['count_'+key]);
				//$chainCount++;
				$chainCount += $scope[$chainIdTemp];
			}
		});
		//console.log($indexCount);
		$scope['count_'+$chainKey] = $chainCount;
		
		// only for enable/diable button (how many chains was changed(>0))
		$chainsChanged = 0;
		angular.forEach($scope.chains, function(value, key) {
			$chainIdTemp = 'count_'+key;
			if($scope[$chainIdTemp] != undefined){
				//console.log(key + ': '+ $scope['count_'+key]);
				//$chainCount++;
				$chainsChanged += $scope[$chainIdTemp];
			}
		});
		//console.log($indexCount);
		$scope['count_all'] = $chainsChanged;		
	}
	
	$scope.resetProteinLog = function(){
		console.log("Reset protein log");
		$scope.proteinLog = true;
		delete $scope.proteinEditLog;
		delete $scope.proteinEditLogBuffer;
		$scope.proteinEditLog = {};
		$scope.proteinEditLogBuffer = {};
	}
	
	//Declaration to be used from external angular controller (besides in use in this controller(ProteinEditorController)).
	//The external angular controller is DockThor-3.0/apps/docking/js/protein.js
	$rootScope.$on("proteinEditorReset", function() {
		$scope.proteinEditorResetAppScope();
		delete $scope.chains;
	});
	
	$scope.proteinEditorResetAppScope = function(){
		console.log("Reset protein editor");
		$scope.resetChains();
		$scope.resetProteinLog();		
	}
	
	$scope.resetChains = function (){
		$scope.chains = {};
		angular.copy($scope.chainsOriginalJsonFile, $scope.chains);
		
		//reset chain edit status
		angular.forEach($scope.chains, function(chainValue, chainKey) {
			if($scope['count_'+chainKey] != undefined){
				//console.log(key + ': '+ $scope['count_'+key]);
				$scope['count_'+chainKey] = 0;
				
				//reset atom edit status
				angular.forEach(chainValue, function(atomValue, atomKey) {
					if($scope['count_'+chainKey+atomKey] != undefined){
						//console.log(key + ': '+ $scope['count_'+key]);
						$scope['count_'+chainKey+atomKey] = 0;
						
						//reset index edit status
						angular.forEach(atomValue, function(indexValue, indexKey) {
							if($scope['count_'+chainKey+atomKey+indexKey] != undefined){
								//console.log(key + ': '+ $scope['count_'+key]);
								$scope['count_'+chainKey+atomKey+indexKey] = 0;
							}
						});
					}
				});
				
			}
		});	
		$scope['count_all'] = 0;		
	}
	
	$scope.checkIfHasOptions = function($element){
		//console.log('---'+$element.<i);
		
		var result = true;
		angular.forEach($element, function(value, key) {
			//console.log(key + ': ' + value['options'].length);
			if(value['options'].length==0) {
				result = false;			
			}
		});
		return result;
	}
	
//	// function to display success message
//    $scope.messageSuccess = function(msg){
//    	$('.alert-success > p').html(msg);
//        $('.alert-success').slideDown();        
//        $('.alert-success').delay(5000).slideUp(function(){
//            $('.alert-success > p').html('');
//        });
//    };
//    
//    // function to display error message
//    $scope.messageError = function(msg){
//    	$('.alert-danger > p').html(msg);
//        $('.alert-danger').slideDown();
//        $('.alert-danger').delay(5000).slideUp(function(){
//            $('.alert-danger > p').html('');
//        });
//    };
	
});