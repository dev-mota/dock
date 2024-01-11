app.controller("LoginController", [ '$rootScope','$scope', '$http',  
	function($rootScope, $scope, $http) {

	$scope.pdfFile = null;
	
	$('.form').find('input, textarea').on('keyup blur focus', function (e) {
	  
	  var $this = $(this),
	      label = $this.prev('label');
	
		  if (e.type === 'keyup') {
				if ($this.val() === '') {
	          label.removeClass('active highlight');
	        } else {
	          label.addClass('active highlight');
	        }
	    } else if (e.type === 'blur') {
	    	if( $this.val() === '' ) {
	    		label.removeClass('active highlight'); 
				} else {
			    label.removeClass('highlight');   
				}   
	    } else if (e.type === 'focus') {
	      
	      if( $this.val() === '' ) {
	    		label.removeClass('highlight'); 
				} 
	      else if( $this.val() !== '' ) {
			    label.addClass('highlight');
				}
	    }
	
	});
	
	$('.tab a').on('click', function (e) {
	  
	  e.preventDefault();
	  
	  $(this).parent().addClass('active');
	  $(this).parent().siblings().removeClass('active');
	  
	  target = $(this).attr('href');
	
	  $('.tab-content > div').not(target).hide();
	  
	  $(target).fadeIn(600);
	  
	});
	
	$('#inputPDFProjectFile').on('change', function(){ 
		$scope.$apply(function() {
			$scope.pdfFile = $('#inputPDFProjectFile').val().replace(/.*[\/\\]/, '');
		});
	});
	
	$scope.removePDFFile = function (){
		$scope.pdfFile = null;
		$('#inputPDFProjectFile').val(null);
	}
	
	$scope.sendPDFFile = function (){
		$( "#sendPDFForm" ).submit();
	}

}])
