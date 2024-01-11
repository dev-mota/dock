<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>Portal Sample</title>		
		<link rel="stylesheet" href="../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body ng-app="sampleApp">		
        	
		<div ng-controller="sampleController" ng-cloak>
    		
        		<div class="container">
        		
        			<br><br><br>
        			<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#nglModalProtein" ng-click="loadIframeProtein()">Modal for protein</button>
        			
            		<div class="modal fade" id="nglModalProtein" role="dialog" aria-labelledby="nglModalProteinLabel" aria-hidden="true">
            			<div class="modal-dialog modal-lg">
            				<div class="modal-content">
               				<div class="modal-header" align="center">
        	        					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	        						<span aria-hidden="true">&times;</span></button>
            		    					<h4 class="modal-title" id="nglModalProteinLabel">NGL Viewer</h4>
                   				</div>
               				<div class="modal-body">
               					<iframe id="nglViewerIframeProtein" width="100%" height="600px" src="view/nglViewerProtein.php" frameborder="0"></iframe>
               				</div>
               				<div class="modal-footer">
            						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               				</div>
             			</div>
            			</div>
        			</div>
        		     
        		    <br><br><br>
        			<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#nglModalLigand" ng-click="loadIframeLigand()">Modal for ligand</button>
        			
            		<div class="modal fade" id="nglModalLigand" role="dialog" aria-labelledby="nglModalLigandLabel" aria-hidden="true">
            			<div class="modal-dialog modal-lg">
            				<div class="modal-content">
               				<div class="modal-header" align="center">
        	        					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	        						<span aria-hidden="true">&times;</span></button>
            		    					<h4 class="modal-title" id="nglModalLigandLabel">NGL Viewer</h4>
                   				</div>
               				<div class="modal-body">
               					<iframe id="nglViewerIframeLigand" width="100%" height="600px" src="view/nglViewerLigand.php" frameborder="0"></iframe>
               				</div>
               				<div class="modal-footer">
            						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               				</div>
             			</div>
            			</div>
        			</div>        			 
        			
            	</div>
        	</div>
        	
        	<script type="text/javascript" src="../../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../../js/external/angular.min.js"></script> 
		<script type="text/javascript" src="index.js"></script>
		
	</body>

</html>
