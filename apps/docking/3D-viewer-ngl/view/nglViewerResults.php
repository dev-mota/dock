<?php 
$job_id = "''";
if(isset($_GET["jobId"])){
    $job_id = $_GET["jobId"];
}

$result_elements = "''";
if(isset($_GET["resultElements"])){
	$result_elements = $_GET["resultElements"];
	$result_elements_parsed = str_replace('"',"\'",$result_elements); // replace " by ' because of html doble quotes (ng-init="...")
}

$reference_file = "''";
if(isset($_GET["referenceFileName"])){
	$reference_file_name = $_GET["referenceFileName"];
}

?>

<!DOCTYPE html> 
<html lang="en" >

	<head>
		<meta charset="UTF-8">
		<title>Docking NGL Result Viewer</title>		
		<link rel="stylesheet" href="../../../../utils/bootstrap-3.3.7-dist/css/bootstrap.min.css">
		<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
		<link rel="stylesheet" href="../css/style.css">	
		
        <style>
            body {
              position: relative;
            }
            
            .nav-pills {
                padding-top: 8px;
                right: 20px;
                position: fixed;                
            }
            
            .navbar {
                background-color: white;
                padding-bottom:58px;
                border-bottom:  1px solid #ccc!important;
            }
            
            #section1 {padding-top:50px;}            
            #section2 {padding-top:50px;}
            
        </style>
        
	</head>

	<body
        ng-app="nglViewerApp" ng-cloak
        data-spy="scroll" data-target=".navbar" data-offset="50">
          
		<div ng-controller="nglControllerResults" ng-init="init(<?= $job_id ?>, '<?= $result_elements_parsed ?>', <?= $reference_file_name ?>)">
    		
            <div class="container-fluid">                           
                
                <div class="row">
                    <div class="col-md-12">
                        <nav class="navbar navbar-fixed-top">
                            <ul class="nav nav-pills navbar-collapse navbar-right" id="myNavbar">
                              <li class="active"><a href="#section1" >Table</a></li>
                              <li><a href="#section2">3D View</a></li>                              
                            </ul>                            
                        </nav>
                        
                        <div id="section1">
                            
                            <h3>Table</h3>
                            <table class="table table-condensed" style="margin-bottom: 0px;">
                                <thead>
                                    <tr style="font-style: oblique;">
                                        <th>Rank</th>
                                        <th>File ID</th>
                                        <th>Compound</th>                                                
                                        <th>Affinity <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="The affinity prediction (kcal/mol) is used to rank different ligands in virtual screening experiments considering the top-energy pose (according to Total Energy) of each compound"><span class="glyphicon glyphicon-question-sign"></span></a> </th>
                                        <th>Total Energy <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="Total Energy (kcal/mol) used to rank different binding modes of the same compound"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                                        <th>vdW Energy</th>
                                        <th>Elec. Energy</th>
                                        <th ng-show="hasLigandReference()">RMSD
                                            <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="Root Mean Square Deviation (given in &Aring;)">
                                                <span class="glyphicon glyphicon-question-sign"></span>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>	
                                <tbody ng-repeat="ligand in resultElements track by $index" ng-init="parentIndex = $index">
                                        
                                    <tr style="background-color: rgba(32, 157, 157, 0.11);" ng-show="showPaginationElement($index)">
                                        <td><b>{{$index+1}}</b></td>
                                        <td>{{ getFileIdName(ligand.name) }}</td>
                                        <td> 
                                            <div ng-show="ligand.show_elements==false">
                                                <a href="javascript:void(0);" style="text-decoration:none" ng-click="showLigandRadios(ligand)">
                                                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true" style="color: #0F8C8C"></span>														
                                                </a> {{ parseLigandName(ligand.name) }}
                                            </div>
                                            <div ng-show="ligand.show_elements==true">
                                                <a href="javascript:void(0);" style="text-decoration:none" ng-click="showLigandRadios(ligand)">
                                                    <span class="glyphicon glyphicon-chevron-down" aria-hidden="true" style="color: #0F8C8C"></span>
                                                </a> {{ parseLigandName(ligand.name) }}
                                            </div>
                                        </td>                                                
                                        <td>{{ ligand.elements[0].score}}</td>
                                        <td>{{ ligand.elements[0].tenergy}}</td>
                                        <td>{{ ligand.elements[0].vdw}}</td>
                                        <td>{{ ligand.elements[0].coul}}</td>
                                        <td ng-show="hasLigandReference()">{{ ligand.elements[0].rmsd}}</td>
                                        
                                    </tr>
                                    
                                    <!-- Debug
                                    <tr><td>{{showPaginationElement($index)}}-{{ligand.show_elements}}=({{showPaginationElement($index) && ligand.show_elements}})</td></tr>
                                    -->
                                    
                                    <tr ng-repeat="element in ligand.elements" ng-show="showLigantOptions(parentIndex, ligand)" ng-style="element.checked && {'background': '#fcf8f9'}" >
                                        <td></td>
                                        <td></td>
                                        <td style="padding-left: 30px;">
                                            <input 
                                                type="radio" 
                                                ng-model="$parent.resultElementSelected" 
                                                ng-value="element" 
                                                ng-checked="element.checked" 
                                                ng-click="selectLigand(element, resultElements)"> {{ parseElementName(element.fileName) }}
                                        </td>       
                                        <td>{{ element.score}}</td>
                                        <td>{{ element.tenergy}}</td>
                                        <td>{{ element.vdw}}</td>
                                        <td>{{ element.coul}}</td>
                                        <td ng-show="hasLigandReference()">{{ element.rmsd}}</td>
                                    </tr>
                                    
                                </tbody>									
                            </table>
                            
                            <nav id="paginationId" aria-label="..." style="text-align: center">
                                <ul class="pagination">
                                    <li>
                                        <a href="#section1" ng-click="previous()">
                                          <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>                                
                                    <li class="{{ $index==selectedPage ? 'active' : ''}} " ng-repeat="x in [].constructor(getQntPages()) track by $index">
                                        <a href="#section1" ng-click="jumpTo($index)">{{ $index+1 }}<span class="sr-only">(current)</span></a>
                                    </li>
                                    <li>
                                        <a href="#section1" ng-click="next()">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>                        
                        <div id="section2">
                            <h3>3D View</h3>
                            <div align="center">
                                <div ng-include="'fragment-note-controls.html'"></div> 							
                                <div id="viewport" style="width:100%; height:500px;"></div>  
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
  	  			
		</div>
		
		<script type="text/javascript" src='../lib/ngl/v2.0.0-dev.32/dist/ngl.js'></script>	
        <script type="text/javascript" src="../../../../js/external/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="../../../../utils/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../../../../js/external/angular.min.js"></script>     		
        <script type="text/javascript" src="../controller/nglControllerResults.js"></script>     		
        <!--
        <script type="text/javascript">
            var versionUpdate = (new Date()).getTime();  
            var script = document.createElement("script");              
            script.src = "../controller/nglControllerResults.js?v=" + versionUpdate;  
            document.body.appendChild(script);  
        </script>
        -->	
	</body>
	
</html>
