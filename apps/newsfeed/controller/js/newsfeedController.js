app.controller("NewsfeedController", [ '$scope', '$http', 'Upload', '$timeout',	
function($scope, $http, Upload, $timeout) {
	
	$scope.newsletter = [];
	$scope.tempNewsletterData = {};
	$scope.hideAddButton = false;
	$scope.hideEditButton = true;
	$scope.hideTestButton = false;
	$scope.disableCancelButton = false;
	$scope.hideRemoveButton = true;
			
	// function to get newsfeed list	    
	$scope.getNewsletterAccounting = function(){
	    $http.get('apps/newsfeed/action.php', {
	        params:{
	            'type':'getNewsfeedList'
	        }
	    }).success(function(response){
	        if(response.status == 'OK'){
				// newsletter list
	            $scope.newsfeedList = response.records;
	        }
	    });
	};
	
	$scope.sendNewsfeed = function(){
		
		bootbox.confirm({ 
		  size: "small",
		  message: "Are you sure to send the newsfeed?", 
		  callback: function(result){ /* result is a boolean; true = OK, false = Cancel*/ 
			if(result){
				$scope.hideCancelButton = true;
				$scope.hideSendButton = true;
				$scope.hideRemoveButton = true;
				$scope.hideUploadButton = true;
				$scope.hideTestButton = true;
				
				Upload.upload({
					url: 'apps/newsfeed/action.php', 
					method: 'POST',						
					file: $scope.picFile,
					data: {  
						'type': 'sendNewsfeed',				
						'subject':$scope.tempNewsletterData.subject,
						'body':$scope.tempNewsletterData.body,
						'':'' // image name will be update later if has file upload
					}
				}).then(function (response) {
					console.log('sendNewsfeed success!' );
					
					$scope.hideCancelButton = false;
					$scope.hideSendButton = false;
					$scope.hideRemoveButton = false;
					$scope.hideUploadButton = false;
					$scope.hideTestButton = false;
					$scope.picFile = null;
					$scope.newsletterForm.$setPristine();
					$scope.tempNewsletterData = {};
					$('.formData').slideUp();
					$scope.messageSuccess(response.data.msg);
					$scope.getNewsletterAccounting();
					
				}, function (response) {
					console.log('Error status: ' + response.status);
					if (response.status > 0){
						//$scope.errorMsg = response.status + ': ' + response.data;
						$scope.hideCancelButton = false;
						$scope.hideSendButton = false;
						$scope.hideRemoveButton = false;
						$scope.hideUploadButton = false;
						$scope.hideTestButton = false;
						$scope.messageError('Some problem in send newsfeed!');
					}	
				}, function (evt) {
					// Math.min is to fix IE which reports 200% sometimes
					if($scope.picFile!=null)
						$scope.picFile.progress = Math.min(99, parseInt(99.0 * evt.loaded / evt.total));
				});
			}
		  }
		});
	}
	
	$scope.sendTest = function(){
		
		bootbox.prompt({
		  title: "Type your email",
		  inputType: 'email',
		  callback: function(result){ /* if user input=true, false = Cancel*/ 
			  if(result!=null){
				$email = result;
				$scope.hideCancelButton = true;
				$scope.hideSendButton = true;
				$scope.hideRemoveButton = true;
				$scope.hideUploadButton = true;
				
				Upload.upload({
					url: 'apps/newsfeed/action.php', 
					method: 'POST',						
					file: $scope.picFile,
					data: {  
						'type': 'sendTest',				
						'subject':$scope.tempNewsletterData.subject,
						'body':$scope.tempNewsletterData.body,
						'email':$email,
						'':'' // image name will be update later if has file upload
					}
				}).then(function (response) {
					console.log('sendNewsfeed success!' );
					
					$scope.hideCancelButton = false;
					$scope.hideSendButton = false;
					$scope.hideRemoveButton = false;
					$scope.hideUploadButton = false;
					$scope.hideTestButton = false;
					$scope.picFile = null;
					$scope.newsletterForm.$setPristine();
					$scope.tempNewsletterData = {};
					$('.formData').slideUp();
					$scope.messageSuccess(response.data.msg);
					$scope.getNewsletterAccounting();
					
				}, function (response) {
					console.log('Error status: ' + response.status);
					if (response.status > 0){
						//$scope.errorMsg = response.status + ': ' + response.data;
						$scope.hideCancelButton = false;
						$scope.hideSendButton = false;
						$scope.hideRemoveButton = false;
						$scope.hideUploadButton = false;
						$scope.hideTestButton = false;
						$scope.messageError('Some problem in send newsfeed!');
					}	
				}, function (evt) {
					// Math.min is to fix IE which reports 200% sometimes
					if($scope.picFile!=null)
						$scope.picFile.progress = Math.min(99, parseInt(99.0 * evt.loaded / evt.total));
				});
			  }			
		  }
		});
	}
	    
	// function to cancel edit/new
	$scope.cancelNewsletter = function(){
	    $scope.picFile = null;	
		$scope.newsletterForm.$setPristine();
		$scope.tempNewsletterData = {};
		$scope.hideAddButton = false;
		$scope.hideEditButton = true;
		$('.formData').slideUp();
	};
	
	// function to display success message
    $scope.messageSuccess = function(msg){
        $('.alert-success > p').html(msg);
        $('.alert-success').show();
        $('.alert-success').delay(5000).slideUp(function(){
            $('.alert-success > p').html('');
        });
    };
    
    // function to display error message
    $scope.messageError = function(msg){
        $('.alert-danger > p').html(msg);
        $('.alert-danger').show();
//        $('.alert-danger').slidelay(5000).slideUp(function(){
//            $('.alert-danger > p').html('');
//        });
    };
	
	// function to get newsfeed list	    
	$scope.unsubscribe = function($id,$email){
		
	    bootbox.confirm({
			size: "small",
			message: "Are you sure to unsubscribe "+$email+"?", 
			callback: function(result){ // result is a boolean; true = OK, false = Cancel
				if(result){
					$http.get('action.php', {
						params:{
							'type':'unsubscribe',
							'id': $id,
							'email': $email
						}
					}).success(function(response){
						if(response.status == 'OK'){
							// newsletter list
							$('.alert-success > p').html(response.msg);
					        $('.alert-success').show();
							$scope.hideUnsubscribeButton = true;							
						}else{
							$scope.messageError('Some problem occurred in unsubscribing!');
						}
					});
				}
			}		
		});
	};	    
}]);
	
	      