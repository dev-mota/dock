<?php 
include_once "apps/docking/job-properties-mananger.php";
include_once "conf/globals-dockthor.php";

$proteinInput = array();
$ligandInput = array();
$cofactorsInput = array();
if(isset($job_id)){
	$jobPropertiesMananger = JobPropertiesMananger::getInstance();
	$job_properties = $jobPropertiesMananger->getJobProperties($job_id);
	
	end ( $job_properties [$job_id] ['submissions'] );
	$submission = $job_properties [$job_id] ['submissions'] [key ( $job_properties [$job_id] ['submissions'] )];
	foreach ( $submission ['file-args'] as $key => $value ) {
		foreach ( $submission ['file-args'][$key] as $file ) {
			if($key == 'r'){
				array_push($proteinInput, $file);			
			}
			
			if($key == 'l'){
				array_push($ligandInput, $file);
			}
			
			if($key == 'c'){
				array_push($cofactorsInput, $file);
			}
		}
	}
}
?>

<script type="text/javascript">
	var proteinInputFromProperties = <?php echo json_encode($proteinInput)?>;
	var ligandInputFromProperties = <?php echo json_encode($ligandInput)?>;
	var cofactorsInputFromProperties = <?php echo json_encode($cofactorsInput)?>;
</script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript" src="apps/docking/js/results.js"></script>

<div class="docking-page-container" ng-controller="ResultsController" ng-init="getJobStatus()">

	<div class="overlay" ng-show="analyzeStatus == 'analyzing'">
		<img class="dockthor-loader" src="./images/logo_dockthor.png">
	</div>
	
	<form id="resultsForm" enctype="multipart/form-data"  method="post" ng-show="$parent.$parent.job.status == 'SUCCESS'">
		<ol class="circles-list">
			<li>
				<h3 class="item-title">Select the parameters for analyses of docking results:</h3>
				<div class="col-xs-12" style="float: none">
					
					<div class="row">
						<h4  class="col-xs-8">RMSD to cluster conformers :</h4>
						<div class="col-xs-3">
							<input class=" form-control" type="number" name="c" ng-model="rmsd" step="0.1" ng-disabled={{disableRmsd}}>
						</div>
						<!--<div style="margin-top: 1%">Å</div>-->
					</div>
					
					<div class="row">
						<h4  class="col-xs-8">Number of binding modes :</h4>
						<div ng-class="{'has-error' : (bindingModes > maxBindingValue) || (minBindingValue > bindingModes) }" class="col-xs-3">
							<input class=" form-control" type="number" name="num" ng-model="bindingModes">
							<input type="hidden" name="num" value="{{bindingModes}}"/>
						</div>
					</div>
					
					<div class="row">
						<h4  class="col-xs-8">Compare docking poses with a reference conformation?</h4>
						<div class="col-xs-2" style="margin-top: 1%;">
							<label class="switch" ng-show="!(referenceFile != null && referenceFile != '') && job.ligandInput.length == 1">
								<input id="referenceInputFile" type="file" accept=".mol2, .pdb, .sdf" name="r[]" onchange="angular.element(this).scope().addFile(this)">
								<div class="checkbox-slider round"></div>
							</label>
							<label class="switch" ng-show="referenceFile != null && referenceFile != '' && job.ligandInput.length == 1">
								<input class="disabled" type="checkbox" ng-checked="referenceFile != null && referenceFile != ''" ng-click="removeFile()">	
								<div class="checkbox-slider round"></div>
							</label>
							<label class="switch disabled" ng-show="job.ligandInput.length > 1" disabled="disabled">
								<input class="disabled" type="checkbox" disabled="disabled">							
							  	<div class="checkbox-slider round disabled" disabled="disabled"></div>
	 						</label>
						</div>
					</div>
					
					<br>
					<div class="alert alert-info" role="alert" ng-show="referenceFile != null && referenceFile != ''">
						<strong>{{referenceFile}}</strong> selected as reference. RMSD will be calculated for analyses purposes.
					</div>
				</div>
			</li>
			<li>
				<h3 class="item-title">Analyze your docking results:</h3>
				
				<div align="center">
					<input type="hidden" name="action" value="ANALYSE"/>
					<input type="hidden" name="jobId" value="<?php if(isset($job_id)){$illegalChar = array(".", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                $job_id = str_replace($illegalChar, "", $job_id); echo $job_id;}?>"/>					 
					<button type="submit" name="submit" class="btn send-to-dock-button " ng-disabled="analyzeIsDisabled()">
						<img width="25px" src="./images/logo_dockthor.png" style="margin-right: 6px;">
				     	<span class="hidden-md hidden-sm hidden-xs" ng-show="analyzeStatus != 'analyzing'">Analyze</span>
				     	<span class="hidden-md hidden-sm hidden-xs" ng-show="analyzeStatus == 'analyzing'">Loading...</span>
					</button>					 
					<button type="button" class="btn btn-primary btn-lg analyze-button" ng-click="downloadResults();" ng-disabled="analyzeStatus != 'complete' && analyzeStatus != 'error'">
						<i class="glyphicon glyphicon-circle-arrow-down"></i> Download
					</button>					 
					<!-- Remove all files button -->				
					<button type="button" class="btn btn-danger remove-all-files-button" ng-click="removeAllFilesWithConfirmation(job.id)" ng-disabled="analyzeStatus == 'analyzing'">
						<i class="glyphicon glyphicon-trash"></i>
						<span class="hidden-md hidden-sm hidden-xs">Delete Job</span>
					</button> 
				</div>
				
				<div ng-show="analyzeStatus == 'complete'">
					<div class="row" >				
						<br>
						
						<!-- <div class="{{viewerType=='jsmol' ? 'col-xs-7' : ''}}" > -->
						<!--<div class="{{viewerType=='jsmol' ? 'col-xs-7' : ''}}" ng-show="viewerType=='jsmol'">-->
						<div ng-show="viewerType=='jsmol'">
							<table class="table results-table" style="width:100%">
								<thead>
									<tr>
										<!-- Posicao no arquivo  -->
										<th>Rank</th>
										
										<th>Compound</th>
										
										<!-- Score: Affinity --> 
										<th>Affinity
											<a href="javascript:void(0)" data-toggle="tooltip" title="" data-original-title="The affinity prediction (kcal/mol) is used to rank different ligands in virtual screening experiments considering only the top-energy pose of each compound">
												<span class="glyphicon glyphicon-question-sign"></span>
											</a>
										</th>
										
										<th>Total Energy</th>
										
										<!-- vdW: vdW Energy -->
										<th>vdW Energy</th>
										
										<!-- Coul: Electrostatic Energy -->
										<th>Electrostatic Energy</th>
										
										<!-- <th ng-show="!referenceUploaded">I. Energy</th>  -->
										
										<th ng-show="referenceUploaded">RMSD</th>
									</tr>
								</thead>
									<tbody ng-repeat="item in table | orderBy: 'poses[0].score'">
										<tr style="background-color: rgba(32, 157, 157, 0.11);">
											<td>{{$index + 1}}</td>
											<td>
												<button type="button" class="btn btn-outline-secondary btn-xs open-level-button" ng-show="item.showPoses==false" ng-click="item.showPoses=true" style="padding : 0; background-color: rgb(230, 244, 244);"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></button>
												<button type="button" class="btn btn-outline-secondary btn-xs open-level-button" ng-show="item.showPoses==true" ng-click="item.showPoses=false" style="padding : 0; background-color: rgb(230, 244, 244);"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span></button>
												{{item.name}}
											</td> 
											<td>{{item.poses[0].score}}</td>
											<td>{{item.poses[0].tenergy}}</td>
											<td>{{item.poses[0].vdw}}</td>
											<td>{{item.poses[0].coul}}</td>
											<!-- <td ng-show="!referenceUploaded">{{item.poses[0].ienergy}}</td>  -->
											<td ng-show="referenceUploaded">{{item.poses[0].rmsd}}</td>
										</tr>
										<tr ng-repeat="pose in item.poses" ng-show="item.showPoses">
											<td></td>
											<td><input type="radio" aria-label="..." ng-model="$parent.$parent.ligandSelected" ng-value="($parent.$index + 2) + '.' + ($index + 1)"> run{{pose.run}}_model{{pose.model}}</td> 
											<td>{{pose.score}}</td>
											<td>{{pose.tenergy}}</td>
											<td>{{pose.vdw}}</td>
											<td>{{pose.coul}}</td>
											<!-- <td ng-show="!referenceUploaded">{{pose.ienergy}}</td>  -->
											<td ng-show="referenceUploaded">{{pose.rmsd}}</td>
										</tr>
									</tbody>
							</table>
						</div>
						
					</div>
					
					<div class="row" style="text-align:center">
						<!--<div class="{{viewerType=='jsmol' ? 'col-xs-5' : ''}}" ng-show="viewerType=='jsmol'">-->
						<div ng-show="viewerType=='jsmol'">
							<iframe id="results3DView" ng-src="{{viewerSrc}}" src="apps/docking/3D-viewer/show3D.php?type=RESULTS&file=&jobID=<?php if(isset($job_id)){echo $job_id;}?>" width="500" height="370" frameborder="0" scrolling="no"></iframe>
							<div ng-init="view3DProtein=true;view3DCofactors=true;view3DReference=true">
								<label>Hide/Show</label>
								<div class="row">
									<div class="col-xs-6">
										<p>Protein</p> 
										<label class="switch">
											<input type="checkbox" ng-model="view3DProtein">							
											<div class="checkbox-slider round"></div>
										</label>
									</div>
									<div class="col-xs-6" ng-show="referenceUploaded">
										<p>Reference Ligand</p> 
										<label class="switch">
											<input type="checkbox" ng-model="view3DReference">							
											<div class="checkbox-slider round"></div>
										</label>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-6" ng-show="$parent.job.cofactorsInput.length > 0">
										<p>Cofactors</p>
										<label class="switch">
											<input type="checkbox" ng-model="view3DCofactors"> 							
											<div class="checkbox-slider round"></div>
										</label>
									</div>
								</div>
							</div>
						</div>
						
					</div>
				 
				</div>
				<!-- NGL with iframe embedded -->
				<div ng-show="viewerType=='ngl'">
					<div class="row" ng-show="analyzeStatus == 'complete'" ng-show="viewerType=='ngl'">
						<div class="embed-responsive embed-responsive-4by3" style="border: 1px solid #ccc!important; border-radius: 10px;">
							<iframe class="embed-responsive-item" ng-src="{{srcViewerResultsNgl}}" allowfullscreen></iframe>
						</div>
					</div>
				</div>
				 
			</li>
		</ol>
	</form>
	
	<ol class="circles-list" ng-show="analyzeStatus == 'error'">
		
		<div class="alert alert-danger" role="alert">
			The docking job <i>{{job.id}}</i> has failed. Please check the following requirements:
			<br>
			<br>
			<p>
			<b>Protein file:</b>
			</p>
			
				<UL type=number>
					 <LI> less than 10000 atoms;
					 <LI> less than 1000 amino acid residues;
					 <LI> only contains standard amino acid residues (e.g. MSE is not recognized);
					 <LI> only three-dimensional structures are accepted;
				 <LI> the initial amino acid must contain all atoms of the backbone.
				</UL>
			
			<p>
			<b>Ligand and cofactor files:</b>
			</p>
			
				<UL type=number>
					 <LI> smaller than or equal to 10Mb;
					 <LI> must contain less than 1000 atoms;
					 <LI> all atoms are recognized by the MMFF94 force field;
					 <LI> only three-dimensional structures are accepted.
			
				</UL>

			<p><b>Docking parameters and submission:</b></p>
			<UL type=number>
				 <LI> grid size must be larger than the ligand size;
				 <LI> protein-cofactor complexes with too many atoms may fail (e.g. protein-DNA complexes);
				 <LI> carefully check your email address.
			</UL>
		</div>		
	</ol>


	<ol class="circles-list" ng-show="$parent.$parent.job.status != 'SUCCESS'">
		<li>
			<h3 class="item-title">Job Status</h3>
			<div align="center" ng-show="job.id != null && job.status != 'UNKNOW' && job.status != '' && job.status != null" ng-init="getJobStatus()">
				<div class="row">
					Job id: <strong>{{job.id}}</strong>
				</div>
				<div class="row">
					Job Status: <strong style="color : #1f9797;">{{job.status}}</strong>
				</div>
				<div ng-show="job.status == 'RUNNING'">
					<br><br><br><br>
					<i class="overlay-img"><img class="dockthor-running" src="./images/logo_dockthor.png"></i>
					<br><br>
				</div>
			</div>
			<strong ng-show="job.id != null && job.status == 'UNKNOW'">Job id "{{job.id}}" not found</strong>
		</li>
	</ol>
</div>
