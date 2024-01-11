var app = angular.module("nglViewerApp", []); 

app.controller("nglViewerController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading nglViewerApp...");
	
	$scope.selectedInputFileIndex = 0; 
	$scope.selectedFileIndex = 0;
	$scope.pageProperties = {
			pathsDropDownListHeader: "Molecule"
	};
	
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
		
		$http.post(
				'../action/nglViewerAction.php',
				{					
					'action': 'getLigandOutputPaths'				
				}				
		).success(function(data, status, headers, config){
			
	        if(status == "200" && data.status == "OK"){
	        		console.log('nglViewerController loadNgl() success!');
	        		$scope.outputPaths = data.data;
	        		console.log($scope.outputPaths);	        		
	        		$scope.loadNgl($scope.selectedInputFileIndex, $scope.selectedFileIndex);	        		
	        }
		}).error(function(data, status, headers, config){
			console.log('nglViewerController loadNgl() fail!');			
		});	
		
	}
	
	$scope.loadNgl = function(folderIndex, fileIndex){
		
		var path = $scope.outputPaths[folderIndex].paths[fileIndex].path;
		console.log("loadNgl... "+path)
		
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
	
	$scope.selectInputFileAndLoadNgl = function(folderKey){		
		$scope.selectedInputFileIndex = folderKey; 
		$scope.selectedFileIndex = 0;		
		$scope.loadNgl($scope.selectedInputFileIndex, $scope.selectedFileIndex); // load first structure
	}
	
	$scope.selectPathFileAndLoadNgl = function(folderKey,fileIndex){
		$scope.selectedInputFileIndex = folderKey; 
		$scope.selectedFileIndex = fileIndex;
		$scope.loadNgl($scope.selectedInputFileIndex, $scope.selectedFileIndex); // load first structure
	}
	
}]);
