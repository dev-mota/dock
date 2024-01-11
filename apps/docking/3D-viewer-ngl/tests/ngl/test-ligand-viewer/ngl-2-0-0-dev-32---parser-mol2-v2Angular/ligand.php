<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>Ligand Page</title>		
		<link rel="stylesheet" href="../../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	</head>
	
	<body>		
        	
		<div>
    		
        		<div class="container">
        		
        			<h3>LIGAND PAGE</h3>
        			<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">Launch demo modal</button>
				
            		<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            			<div class="modal-dialog modal-lg">
            				<div class="modal-content">
               				<div class="modal-header">
    	        					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    	        						<span aria-hidden="true">&times;</span></button>
        		    					<h4 class="modal-title" id="myModalLabel">Modal title</h4>
               				</div>
               				<div class="modal-body">
               				<!-- <iframe id="ligandViewNGL" src="nglViewer.php" width="100%" height="500px" frameborder="0" scrolling="no"></iframe> -->
               				<iframe id="ligandViewNGL" width="100%" height="500px" src="./view/nglViewer.php" frameborder="0"></iframe>
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
		
	</body>

</html>
