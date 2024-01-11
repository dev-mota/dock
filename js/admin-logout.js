app.controller("AdminLogoutController", [ '$scope','$http',
	function($scope, $http) {
	$scope.logout = function() {
		console.log("entrando no controller...");
		$http.post(
				'action/admin-logout-action.php',
				{	
					params:{
//
//						user: $scope.contact.userName,
//						email: $scope.contact.eMail,
//						subject: $scope.contact.subject,
//						message: $scope.contact.message
	    			}
				}).success(function(response){
					//tratamento do sucesso da operação, pois no firefox se o tratamento não for feito 
					//o logout só é realizado na segunda tentativa, pois a 1a não é realizada com sucesso
			    	if (response.operationStatus == 'SUCCESS'){
//			    		console.log(response.operationStatus);
			    		$scope.selectedTab = 'WELCOME';
			    		location.href="index.php";
			    	} else {
			    		$scope.selectedTab = 'WELCOME';
			    		location.href="index.php";
			    	}
				}
				);
		
		//$scope.goToHomePage();
		
	};
	
	$scope.goToHomePage = function(){
    	$scope.selectedTab = 'WELCOME';
    	location.href="index.php";
    };
}]);
