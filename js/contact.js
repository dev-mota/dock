app.controller("ContactController", [ 'vcRecaptchaService','$scope','$http',
	function(vcRecaptchaService,$scope,$http){
	
	//variaveis para o reCaptcha
	var form = this;
	form.publicKey = "6LdzTzAUAAAAALySKFDaqvrNI7nPRlj7an1Nkl3Z";
	
	$scope.success = false;
	$scope.error = false;
	$scope.captcha = true;
	$scope.sendContactMessage = function() {
		if(vcRecaptchaService.getResponse() === ""){ //if string is empty
			$scope.success = false;
			$scope.error = true;
			$scope.message="Message can not be sent. Please resolve the captcha and submit."
        }else {
			$http.post(
					'action/contact-action.php',
					{	
						params:{
	
							user: $scope.contact.userName,
							email: $scope.contact.eMail,
							subject: $scope.contact.subject,
							message: $scope.contact.message,
							recaptcha: vcRecaptchaService.getResponse()
		    			}
					}).success(function(response){
				    	if (response.operationStatus == 'SUCCESS'){
				    		console.log(response.operationStatus);
				    		$scope.success = true;
				    		$scope.error = false;
				    		$scope.message="Message sent successfully!"
				    		//send contact message to db
				    		$scope.resetContactMessage();
				    	} else {
				    		console.log("diferente de success: "+response.operationStatus);
				    		$scope.success = false;
				    		$scope.error = true;
				    		$scope.resetContactMessage();
				    		$scope.message="Message can not be sent. Please, try again later."
				    	}
					}
				);
        }
		
	};
	
	$scope.resetContactMessage = function() {
		$scope.contact = null;
	};
}]);
