app.controller("RegEmailsController", [ '$scope','$http',
	function($scope, $http) {
	$scope.getEmailsInfo = function() {
		console.log("getEmailsInfo");
		$http.post('apps/registered-emails/action/action.php',
		{	
			params:{


			}
		}).success(function(response){
	    	console.log("response getEmailsInfo");
	        if(response.status == 'OK'){
				// regitered email list
	            $scope.regEmailsList = response.records;
	        }
	    });
		console.log("final getEmailsInfo");
	};
	
}]);