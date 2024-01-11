var app = angular.module("dockthorApp", ['ngFileUpload', 'blueimp.fileupload', 'vcRecaptcha', 'ngSanitize']); // TODO: transferir ngFileUpload para newsfeed
//var app = angular.module("dockthorApp", []);
app.controller("MainController", [ '$scope','$http',
	function($scope, $http) {
	$scope.selectedTab = 'WELCOME';
	
	//$('.alert-success').hide();
	//$('.alert-danger').hide();
	
	$('.hidden-alert-success').hide();
	$('.hidden-alert-danger').hide();
	
	// function to display success message
    $scope.messageSuccess = function(msg){
//    	$('.alert-success > p').html(msg);
//        $('.alert-success').slideDown();        
//        $('.alert-success').delay(5000).slideUp(function(){
//            $('.alert-success > p').html('');
//        });
    	
    	$('.hidden-alert-success > p').html(msg);
        $('.hidden-alert-success').slideDown();        
        $('.hidden-alert-success').delay(5000).slideUp(function(){
            $('.hidden-alert-success > p').html('');
        });
    };
    
    // function to display error message
    $scope.messageError = function(msg){
//    	$('.alert-danger > p').html(msg);
//        $('.alert-danger').slideDown();
//        $('.alert-danger').delay(5000).slideUp(function(){
//            $('.alert-danger > p').html('');
//        });
    	
    	$('.hidden-alert-danger > p').html(msg);
        $('.hidden-alert-danger').slideDown();
        $('.hidden-alert-danger').delay(5000).slideUp(function(){
            $('.hidden-alert-danger > p').html('');
        });
    };
    
//    $scope.logout = function(){
//    	$http.post(
//				'apps/authentication/logout-action.php'
//			).success(function(response){
//				
//			});
//    }
    
    $scope.goToHomePage = function(){
    	$scope.selectedTab = 'WELCOME';
	}
	
	$scope.registerDatasetDownload = function($filePath){
		
		console.log("registerDatasetDownload ... ");

		$http.put(
			'apps/file-download-counter/service.php',
			{					
				'action': 'registerDatasetDownload',
				'filePath': $filePath,
			}				
		).success(function(data, status, headers, config){
			console.log("registerDatasetDownload 200");								
		}).error(function(data, status, headers, config){
			console.error("registerDatasetDownload. Status response: " + status);
		});	
	}
	
}])
.factory('appInfo', function ($http) {

	return {
		// enabled: function (param) {
		appViewer: function () {
			
			return $http.post(
				'apps/docking/action/structure-viewer-action.php',
				{					
					'action': 'getStructureViewerType',
					'cache': true	
				}
			);
			
		}
	}

});
