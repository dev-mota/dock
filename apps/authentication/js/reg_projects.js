app.controller("RegProjController", [ '$rootScope','$scope', '$http',  
	function($rootScope, $scope, $http) {
		$scope.success= false;
		$scope.error=false;
		$scope.register = function() {
			$http.post(
					'apps/authentication/action/register-action.php',
					{	
						params:{
	
							name: $scope.reg.name,
							email: $scope.reg.email,
							passwd: $scope.reg.passwd
		    			}
					}).success(function(response){
				    	if (response.operationStatus == 'SUCCESS'){
				    		console.log(response.operationStatus);
				    		$scope.success= true;
				    		$scope.error=false;
				    		$scope.message="Message sent successfully!"
				    		//send contact message to db
				    	} else {
				    		console.log("diferente de success: "+response.operationStatus);
				    		$scope.success= false;
				    		$scope.error=true;
				    		$scope.message="Message can not be sent. Please, try again later."
				    	}
					}
				);
		}
	}
]);