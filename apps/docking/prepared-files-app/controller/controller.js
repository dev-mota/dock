app.directive("resourcesDir", ['$http', function($http){
    return {
        templateUrl: 'apps/docking/prepared-files-app/view/template.html',
        scope: {
            applabel: '=',
            type: '=',
            selectedresource: '=', // CUIDADO - NAO FUNCIONA CamelCase!!!!!!!!!!!!!!!!!!!
            validuser: '=',
            select: '&'
        },
        link: function($scope){
            
            console.log("resourcesDir - start: "+$scope.applabel+" "+$scope.type);
            console.log($scope.validuser);
            
            $scope.modalId = "templatePreparedResourcesModalInfo"+$scope.type;
            $scope.disableInfoButton = true;
            $scope.labels = [];           
            
            $scope.initPreparedResourcesApp = function(){
				
                console.log("resourcesDir - initPreparedResourcesApp()");
                
				$scope.preparedResoucesLabel = $scope.applabel;
                
                if($scope.type=='ligand'){
                    $scope.labels = ['Dataset','pH range','Version'];                    
                } else if($scope.type=='protein'){
                    $scope.labels = ['Target','Variant','Structure'];                
                }
				
				$scope.loadPreparedResourceSuccess = null;
                $scope.loadingPreparedResource = false;
                
				$scope.preparedFiles = [];
				
				$scope.loadPreparedResources();
				
				$scope.selectedLevel1 = null;
				$scope.selectedLevel2 = null;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};
			
			$scope.loadPreparedResources = function() {
				
                $scope.loadingPreparedResource = true;
				$http.post(
					'apps/docking/prepared-files-app/action/action.php',
					{	
						params:{
							action: 'LOAD-TARGETS',
                            type: $scope.type
						}
					}
				).success(function(response){
					$scope.preparedResources = response.data;
                    
					$scope.loadPreparedResourceSuccess = true;	
				}).error(function(response, status) {
					$scope.loadPreparedResourceSuccess = false;
					console.error("Prepared resource error - An internal error occurred: " + status);
				}).finally(function(){
                    $scope.loadingPreparedResource = false;
                });
				
			};
			
			/* Example data: 
			$scope.preparedFiles = [
				{					
					"name": "Main protease",					
					"elements": [
						{
							"name": "PDB code 6w63",
							"elements": [
								{
									"name": "Wild type",
									"fileName": "6w63_dimer_protein.in"			
								},
							],
						},
						{
							"name": "PDB code 6y2F",
							"elements": [
								{
									"name": "Wild type",
									"fileName": "6w63_dimer_protein.in"	
								},
							],
						},
					],
				},
			];
			*/
			
			$scope.selectLevel1 = function(value){
				$scope.selectedLevel1 = value;
				$scope.selectedLevel2 = null;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};			
			
			$scope.selectLevel2 = function(value){
				$scope.selectedLevel2=value;
				$scope.selectedLevel3 = null;
				$scope.disableInfoButton = true;
				$scope.disableSelectButton = true;
			};			
			
			$scope.selectLevel3 = function(value){
				$scope.selectedLevel3 = value;
                $scope.disableInfoButton = false;
				$scope.disableSelectButton = false;
                
                // $scope.resourcefiles = $scope.selectedLevel3;
                
                $scope.path = $scope.selectedLevel1.name + "/" + $scope.selectedLevel2.name + "/" + $scope.selectedLevel3.name;
                
                if($scope.type=='protein'){
                    
                    // If protein, return the file path. Ex.:
                    // targets/covid-19/Nsp12-RdRp/<Wild_type>/PDBcode_7bv2-noRNA/7bv2_wildType_noRNA.in                
                    
                    $http.post(
                        'apps/docking/prepared-files-app/action/action.php',
                        {	
                            params:{
                                action: 'GET-PROTEIN-FILE-PATH',
                                type: $scope.type,
                                selectedPath: $scope.path
                            }
                        }
                    ).success(function(response){
                        console.log("selectResource success!");
                        console.log(response.path);
                        $scope.selectedresource = response.path;
                        //$scope.response = response.path;
                        //$scope.select($scope.response);
                    }).error(function() {
                        console.error("selectResource error!");
                    }).finally(function(){
                        
                    });
                } else  if($scope.type=='ligand'){
                    $scope.selectedresource = $scope.path;
                }
               
			};
            
            $scope.getInfo = function(type, path){                
               
                console.log("getInfo("+type+","+path+")");
                
                $http.post(
					'apps/docking/prepared-files-app/action/action.php',
					{	
						params:{
							action: 'GET-INFO',
							type: type,
                            selectedPath: path
						}
					}
				).success(function(response, status){
					console.log("Prepared resource success!" + status);
                    $scope.selectedLevel3.elements.info = response.info;                    
                    $('#'+$scope.modalId).modal('show');                    
                    
				}).error(function(response, status) {
					console.error("Prepared resource error - An internal error occurred: " + status);
				}).finally(function(){
                    
                });
                
            };
			
			$scope.initPreparedResourcesApp();
        }
    };
}]);