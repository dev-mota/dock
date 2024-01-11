var app = angular.module("proteinApp", []); 

app.controller("proteinController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading proteinController...");
	
	$scope.loadIframe = function(){
		console.log("proteinController loadIframe ...");
		document.getElementById('proteinViewNGL').contentWindow.location.reload(true);
	}
	
}]);
