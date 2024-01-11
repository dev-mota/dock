var app = angular.module("sampleApp", []); 

app.controller("sampleController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading sampleController...");
	
	$scope.loadIframeLigand = function(){
		console.log("sampleController loadIframeLigand ...");
		document.getElementById('nglViewerIframeLigand').contentWindow.location.reload(true); // always reload		
	}
	
	$scope.loadIframeProtein = function(){
		console.log("sampleController loadIframeProtein ...");
		document.getElementById('nglViewerIframeProtein').contentWindow.location.reload(true); // always reload
	}
		
}]);
