var app = angular.module("nglViewerApp", []); 

app.controller("nglControllerCofactorOrLigand", [ '$scope','$http', function($scope, $http) {
	
	// console.log("Loading nglControllerCofactorOrLigand...");
	
	$scope.selectedInputFileIndex = 0;
	$scope.selectedFileIndex = 0;
	$scope.outputPaths = null;
	
	// Create NGL Stage object
	$scope.stage = new NGL.Stage( "viewport" );
	$scope.stage.setParameters({
		 backgroundColor: "white"
	});
	
	window.addEventListener( "resize", function( event ){ // Handle window resizing
		$scope.stage.handleResize();
	}, false );
	
	$scope.init = function($structureType){
		// console.log("nglControllerCofactorOrLigand getPaths("+$structureType+")");
		$scope.getPaths($structureType);
	};
	
	$scope.getPaths = function($structureType){
		
		//console.log("nglControllerCofactorOrLigand getPaths("+$structureType+") ... ");
		
		$http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getFilePaths',
					'structureType': $structureType, 
					'step': 'upload'
				}				
		).success(function(data, status, headers, config){		
			
	        if(status == "200" && data.status == "OK"){
				
				console.log("nglControllerCofactorOrLigand getPaths("+$structureType+") success! ");
				// $scope.outputPaths = data.data;
				$scope.outputPaths = $scope.sortByKey(data.data, 'inputNoExt');
				console.log($scope.outputPaths);			
				$scope.loadNgl($scope.selectedInputFileIndex, $scope.selectedFileIndex);
				
	        } else if (data.status == "NOT_PREPARED_YET"){
	        		// console.log('nglControllerCofactorOrLigand loadNgl WARNING: not prepared yet!');
	        } else {
	        		console.log("nglControllerCofactorOrLigand getPaths("+$structureType+") ERROR: response error! " + data + "; " + status + "; " + headers + "; " + config);
	        }
		}).error(function(data, status, headers, config){
			console.log("nglControllerCofactorOrLigand getPaths("+$structureType+ ") ERROR: critical error!" + data + "; " + status + "; " + headers + "; " + config);
		});
		
	};
	
	$scope.sortByKey = function(array, key) {
		return array.sort(function(a, b) {
			var x = a[key]; var y = b[key];
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		});
	};
	
	$scope.loadNgl = function(folderIndex, fileIndex){
		
		$scope.selectedInputFileIndex = folderIndex; 
		$scope.selectedFileIndex = fileIndex;
		
		var path = $scope.outputPaths[folderIndex].paths[fileIndex].path;
		
		console.log("nglControllerCofactorOrLigand loadNgl... " +path);
		
		// Clear NGL Stage object
		$scope.stage.removeAllComponents();
		// https://github.com/arose/ngl/issues/41
		$scope.stage.loadFile(path).then(function(comp){
			$scope.stage.removeComponent(comp);  // this removes the just loaded component
		});		
		
		$scope.stage.loadFile(path).then(function (o) {
		  o.addRepresentation("ball+stick", { multipleBond: "symmetric" });
		  // o.autoView(); // isso n tem funcionado
		  $scope.stage.keyControls.run("r");
		});	
		
	};
	
}]);
