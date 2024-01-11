<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>Ligand Page</title>		
		<link rel="stylesheet" href="../../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body ng-app="ligandApp">		
        	
		<div ng-controller="angularController" ng-cloak>
    		
        		<div class="container">
        		
        			<h3>LIGAND PAGE</h3>
        			<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" ng-click="loadIframe()">Launch demo modal</button>
				
            		<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            			<div class="modal-dialog modal-lg">
            				<div class="modal-content">
               				<div class="modal-header" align="center">
        	        					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	        						<span aria-hidden="true">&times;</span></button>
            		    					<h4 class="modal-title" id="myModalLabel">NGL Viewer</h4>
                   				</div>
               				<div class="modal-body">
               					<iframe id="ligandViewNGL" width="100%" height="600px" src="view/nglViewer.php" frameborder="0"></iframe>
               				</div>
               				<div class="modal-footer">
            						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               				</div>
             			</div>
            			</div>
        			</div>
        			
            	</div>
        	</div>
        	
        	<script type="text/javascript" src="../../../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../../../js/external/angular.min.js"></script> 
		<script type="text/javascript" src="./ligand.js"></script>
		
	</body>

</html>
