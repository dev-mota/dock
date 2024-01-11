app.controller('LigandRotbEditorController',function($rootScope, $scope, $http) {
	
	//$scope.rotbElementsToBeRemoved = null;
	$scope.ligandLog = true;
	$scope.rotbElementsOriginal = {};
	$scope.disableApplyButton = true;
	$scope.hideRotbLog = true;
	$scope.ligandEditLog = [];
	$scope.ligandEditLogLast = [];
	$scope.fileNameId = "";
	//$scope.ligandEditLogLastModification = {};
	
	$scope.test = function(){
		$http.get(
	    		'apps/docking/ligand-rotb-editor/pages/action/ligandRotbEditorAction.php',				
	    		{
	    			params:{
	    				'type':'getJsonTest',
	    				'fileNameId':'1a1e_ligand_rnum.top'
	    			}
	    		}
	    ).success(function(response){
	        if(response.status == 'OK'){
	        	//console.log('success to parse rotb editor');
	        	$scope.rotbElements = angular.fromJson(response.json);	        	
	        	//console.log($scope.rotbElements);//debug
	        	
	        }else if(response.status == 'ERR'){
	        	$scope.messageError('Failed to parse rotb editor');
	        	//console.log('Failed to parse rotb editor');
	        	
	        }
	    });
	};
	
	$scope.getJsonFromTopFile = function($fileNameId){
		
		$scope.fileNameId = $fileNameId;
		$scope.rotbElementsOriginal = {};
		
		$http.get(
	    		'apps/docking/ligand-rotb-editor/pages/action/ligandRotbEditorAction.php',				
	    		{
	    			params:{
	    				'type':'getJson',
	    				'fileNameId':$fileNameId
	    			}
	    		}
	    ).success(function(response){
	        if(response.status == 'OK'){
	        	//console.log('success to parse rotb editor');
	        	$scope.rotbElements = angular.fromJson(response.json);	        	
	        	angular.copy($scope.rotbElements,$scope.rotbElementsOriginal);
	        	
	        	$("#checkUnCheckSwitch").prop("checked","checked");
	        	//console.log($scope.rotbElements);//debug
	        	$scope.ligandEditLog = [];
	        	$scope.ligandEditLogLast = [];
	        	$scope.hideRotbLog = true;
	        	$scope.clearRotbAlert();
	        	
	        }else if(response.status == 'ERR'){
	        	$scope.messageError('Failed to parse rotb editor');
	        	//console.log('Failed to parse rotb editor');
	        	
	        }
	    });
	}
	
	//declaration to be used by other angular controller. In this case: DockThor-3.0/apps/docking/js/protein.js
	$rootScope.$on("getJsonFromTopFile", function(event, $topFileName) {
		//console.log("blailufhs");
		$scope.getJsonFromTopFile($topFileName);
	});
	
	// function send editted JSON 	    
	$scope.apply = function(){
		
		//$scope.updateJson();
		//$fileNameWithOutExtension = $scope.ligand.codedName.substring(0,$scope.ligand.codedName.lastIndexOf("."));
		//$fileNameId = $scope.$parent.ligandPreparedFiles.files[0].name;		
		
		$http.post(
	    		'apps/docking/ligand-rotb-editor/pages/action/ligandRotbEditorAction.php', 
	    		{
	    			params:{
	    				'type':'updateTop',
	    				'edittedJson':angular.toJson($scope.rotbElements,true),	    				
	    				'fileNameId':$scope.fileNameId	    				
	    			}
	    		}
	    ).success(function(response){
	        if(response.status == 'OK'){
	        	$scope.hideRotbLog = false;
	        	$scope.fillLigandEditLog();
	        	$scope.updateRotbElementsOriginal();
	        	$scope.checkModification();	        	
	        	$scope.disableApplyButton = true;
	        	console.log('success!');
	        }else{
	        	response;
	        	console.log('failed!');
	        	//$scope.$parent.protein.prepared = false;
	        	//$scope.messageError('Failed to update pdb and chains files!)');
	        }
	    });
		
	};
	
	$scope.updateJson = function(){
		
		$scope.rotbElements = {};
		$i=0;
		angular.forEach($scope.rotbElementsOriginal, function(value, key) {
			if(value[2]==true){
				//rotbElementsArray.push(value);
				$scope.rotbElements[$i] = value;
				$i++;
			}
		});
		
	}
	
	$scope.getRotbCount = function ($rotb){
		if($rotb!=null){
			return Object.keys($rotb).length;
		}		
	}
	
	$scope.checkUnCheckAll = function(){
		
		var state = false;
		if($("#checkUnCheckSwitch").is(":checked")){ //was used jquery for this, because ngmodel (checkUnCheckSwitch) breaks css switch
			state = true;
		}
		
		angular.forEach($scope.rotbElements, function(value, key) {
			  $scope.rotbElements[key][2] = state;
			  $scope.checkModification();
		});
	}
	
	$scope.checkModification = function(){
		
		$hasModification = false;
		angular.forEach($scope.rotbElements, function($value, $key) {
			if($scope.rotbElements[$key][2] != $scope.rotbElementsOriginal[$key][2]){
				$scope['rotbAlert_'+$key] = true;
				$hasModification = true;
			}else{
				$scope['rotbAlert_'+$key] = false;				
			}		
		});
		
		$scope.disableApplyButton = !$hasModification;
		
//		//check with original
//		if($scope.rotbElements[$key][2] != $scope.rotbElementsOriginal[$key][2]){
//			
//			//show yellow alert
//			$scope['rotbAlert_'+$key] = true;
//			
//			//add item
//			//$scope.ligandEditLog[$key] = $scope.rotbElements[$key];
//			
//			//enable apply button
//			$scope.disableApplyButton = false;
//		}else{
//			//show yellow alert
//			$scope['rotbAlert_'+$key] = false;
//			
//			//remove item
//			//$scope.ligandEditLog.splice($key, 1);
//		}
//		
////		if($scope.ligandEditLog.length> 0){
////			$scope.hideRotbLog = false;
////		}

	}
	
	$scope.fillLigandEditLog = function(){
		$scope.ligandEditLog = [];
		angular.forEach($scope.rotbElements, function($value, $key) {
//			var $index = parseInt(key)+1;
//			$scope.ligandEditLogLastModification[$index] = [value[0],value[1]];
			if($scope.rotbElements[$key][2] != $scope.rotbElementsOriginal[$key][2]){
				$scope.ligandEditLog[$key] = $scope.rotbElements[$key];
			}			
		});
		
		if($scope.ligandEditLog.length> 0){
			$scope.hideRotbLog = false;
		}
		
		angular.copy($scope.ligandEditLog,$scope.ligandEditLogLast);
	}
	
	$scope.updateRotbElementsOriginal = function(){
		angular.forEach($scope.rotbElements, function($value, $key) {
			$scope.rotbElementsOriginal[$key][2] = $scope.rotbElements[$key][2];			
		});
	}
	
	$scope.clearRotbAlert = function (){
		angular.forEach($scope.rotbElements, function($value, $key) {			
			$scope['rotbAlert_'+$key] = false;				
					
		});
	}
		

});