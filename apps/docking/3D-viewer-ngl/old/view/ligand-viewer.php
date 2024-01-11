<?php ?>

<!DOCTYPE html>
<html lang="en" >

    <head>
    		<meta charset="UTF-8">
    		<title>NGL Viewer</title>
    		
    		<link rel="stylesheet" href="../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
    		<link rel="stylesheet" href="css/style.css">
    		
    		<script type="text/javascript" src="../../../js/external/jquery-3.1.1.min.js"></script>
    		<script type="text/javascript" src="../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>    		
    		  
    </head>

	<body>
<?php

$loadFile = array_pop((array_pop($arrayPathFiles)));

foreach ($arrayPathFiles as $folderName => $filePaths) {
    echo "<span>$folderName</span>";
    echo "<div class=\"form-group\">";
    echo "  <select class=\"form-control\">";
    		    
    foreach($filePaths as $filePath){
        echo "  <option>$filePath</option>";
    }
    
    echo "  </select>";
    echo "</div>";
}

?>	
        <div id="viewport" style="width: 100%; height: 400px;"></div>
        
        
        <script type="text/javascript" src="lib/ngl.js"></script>
        <script>

            // Setup to load data from rawgit
            NGL.DatasourceRegistry.add(
                "data", new NGL.StaticDatasource( "//cdn.rawgit.com/arose/ngl/v2.0.0-dev.32/data/" )
            );
            
            // Create NGL Stage object
            var stage = new NGL.Stage( "viewport" );
            
            // Handle window resizing
            window.addEventListener( "resize", function( event ){
                stage.handleResize();
            }, false );
            
            stage.setParameters({
            	  backgroundColor: "white"
            })
            
            // Dinamic path defined at $filePath (from show3D-controller.php)
            stage.loadFile("<?= $loadFile ?>").then(function (o) {
               o.addRepresentation("ball+stick", { multipleBond: "symmetric" })
               o.autoView()
            })  
            
		</script> 
		
    </body>
    
</html>
