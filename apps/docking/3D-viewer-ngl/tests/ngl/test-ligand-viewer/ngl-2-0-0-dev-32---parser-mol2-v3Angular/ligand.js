var app = angular.module("ligandApp", []); 

app.controller("angularController", [ '$scope','$http', function($scope, $http) {
	
	console.log("Loading angularController...");
	
	//$scope.iframeUrl = "view/nglViewer.php"
	
	$scope.loadIframe = function(){
		console.log("angularController loadIframe ...");
		//$scope.iframeUrl = $scope.iframeUrl;
		//$scope.iframeUrl = $sce.trustAsResourceUrl("view/nglViewer.php");
		//$scope.url = undefined;
		//$scope.iframeUrl = $scope.url;
		//$scope.iframeUrl = "";
		//$scope.iframeUrl = "view/nglViewer.php";
		
		document.getElementById('ligandViewNGL').contentWindow.location.reload(true);
	}
	

	
}]);
