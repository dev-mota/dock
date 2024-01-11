var app = angular.module("nglViewerApp", []); 

app.controller("nglViewerController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading nglViewerApp...");
	$scope.test = 'Hello Ugly World!';
	$scope.names = ["EmilEmilEmilEmilEmil", "Tobias", "Linus","Emil", "Tobias", "Linus","Emil", "Tobias", "Linus"];
	
	// Create NGL Stage object
	$scope.stage = new NGL.Stage( "viewport" );
	
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
	        		
	        		$scope.loadNgl($scope.outputPaths[0].paths[0]);
	        }
		}).error(function(data, status, headers, config){
			console.log('nglViewerController loadNgl() fail!');			
		});	
		
	}
	
	$scope.loadNgl = function(fileModel){
		
		console.log("loadNgl: "+fileModel.path);
		$scope.fileModelSelected = fileModel;
		
		
		// Clear NGL Stage object
		$scope.stage.removeAllComponents()
		
		// Handle window resizing
		window.addEventListener( "resize", function( event ){
			$scope.stage.handleResize();
		}, false );

		$scope.stage.setParameters({
			 backgroundColor: "white"
		})
			
		$scope.stage.loadFile(fileModel.path).then(function (o) {
		  o.addRepresentation("ball+stick", { multipleBond: "symmetric" });
		  o.autoView();		  
		})
		
	}
	
	$scope.sorterFunc = function(fileModel){
		return parseInt(fileModel.index);
	};
	
}]);
