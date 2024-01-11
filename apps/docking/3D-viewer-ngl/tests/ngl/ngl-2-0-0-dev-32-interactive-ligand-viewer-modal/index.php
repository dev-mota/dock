<?php
?>

<!DOCTYPE html>
<html lang="en" >

<head>
	<meta charset="UTF-8">
	<title>NGL@2.0.0-dev.32 - interactive/ligand-viewer</title>
	
	<link rel="stylesheet" href="../../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	<script src="../../../../../js/external/jquery-3.1.1.min.js"></script>
	<script src="../../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
  
</head>

<body>
	
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
      			<h3>Title</h3>
      			<p>text ...</p>
      			
				<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
  					Launch demo modal
				</button>
    			</div>
		</div>
	</div>
	
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
   				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Modal title</h4>
   				</div>
   				<div class="modal-body">
					<iframe id="proteinView" src="view.html" width="100%" height="650px" frameborder="0" scrolling="no"></iframe>
   				</div>
   				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
   				</div>
 			</div>
		</div>
	</div>

</body>
</html>