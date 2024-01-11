app.controller("DockThorController", [ '$scope','$http',
	function($scope, $http) {
	$scope.selectedTab = 'PROTEIN';
	
	$scope.proteinInput = null;
	$scope.ligandInput = null;
	$scope.cofactorInput = null;
	$scope.emptyLigand = true;
	$scope.emptyProtein = true;
	
	$scope.isPDBFile = function ($file) {
		if($file != null && $file.name != null){
			return $file.name.endsWith(".pdb");
		} else {
			return false;
		}
	};
	
	$scope.isMol2File = function ($file) {
		if($file != null && $file.name != null){
			return $file.name.endsWith(".mol2");
		} else {
			return false;
		}
	};
	
	$scope.isSDFFile = function ($file) {
		if($file != null && $file.name != null){
			return $file.name.endsWith(".sdf");
		} else {
			return false;
		}
	};

	$scope.isTOPFile = function ($file) {
		if($file != null && $file.name != null){
			return $file.name.endsWith(".top");
		} else {
			return false;
		}
	};
	
	$scope.reloadView3D = function(iframeID) {
		document.getElementById(iframeID).contentWindow.load();
	};
	
	$scope.$watch('ligandInput' , function(newValue) {
		if((newValue == null) || (newValue == "")){
			$scope.emptyLigand = true;
		}else{
			$scope.emptyLigand = false;
		}
	});
	
	$scope.$watch('proteinInput' , function(newValue) {
		if((newValue == null) || (newValue == "")){
			$scope.emptyProtein = true;
		}else{
			$scope.emptyProtein = false;
		}
	});

	$(document).ready(function(){
		$('[data-toggle=tooltip]').hover(function(){
			// on mouseenter
			$(this).tooltip('show');
		}, function(){
			// on mouseleave
			$(this).tooltip('hide');
		});
	});

}]);

// ToolTipApp is the ng-app application in your web app
app.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            $(element).hover(function(){
                // on mouseenter
                $(element).tooltip('show');
            }, function(){
                // on mouseleave
                $(element).tooltip('hide');
            });
        }
    };
});


