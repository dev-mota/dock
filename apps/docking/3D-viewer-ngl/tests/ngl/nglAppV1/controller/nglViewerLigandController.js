var app = angular.module("nglViewerLigandApp", []); 

app.controller("nglViewerLigandController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading nglViewerLigandController...");
	
	$scope.selectedInputFileIndex = 0; 
	$scope.selectedFileIndex = 0;
	
	// Create NGL Stage object
	$scope.stage = new NGL.Stage( "viewport" );
	$scope.stage.setParameters({
		 backgroundColor: "white"
	})
	window.addEventListener( "resize", function( event ){ // Handle window resizing
		$scope.stage.handleResize();
	}, false );
	
	$scope.init = function(){
		$scope.getPaths();
	}
	
	$scope.getPaths = function(){
		
		console.log("nglViewerLigandController getPaths... ");
		
		$http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getLigandFilePaths',					
				}				
		).success(function(data, status, headers, config){
			
	        if(status == "200" && data.status == "OK"){
	        		console.log("nglViewerLigandController getPaths success! ");
	        		$scope.outputPaths = data.data;
	        		console.log($scope.outputPaths);	        		
	        		$scope.loadNgl($scope.selectedInputFileIndex, $scope.selectedFileIndex);	        		
	        }
		}).error(function(data, status, headers, config){
			console.log("nglViewerLigandController getPaths fail! ");			
		});	
		
	}
	
	$scope.loadNgl = function(folderIndex, fileIndex){
		
		$scope.selectedInputFileIndex = folderIndex; 
		$scope.selectedFileIndex = fileIndex;
		
		var path = $scope.outputPaths[folderIndex].paths[fileIndex].path;
		
		console.log("nglViewerLigandController loadNgl... " +path);
		
		// Clear NGL Stage object
		$scope.stage.removeAllComponents();
		// https://github.com/arose/ngl/issues/41
		$scope.stage.loadFile(path).then(function(comp){
			$scope.stage.removeComponent(comp);  // this removes the just loaded component
		});		
		
		$scope.stage.loadFile(path).then(function (o) {
		  o.addRepresentation("ball+stick", { multipleBond: "symmetric" });
		  o.autoView();		  
		})	
		
	}
	
}]);
