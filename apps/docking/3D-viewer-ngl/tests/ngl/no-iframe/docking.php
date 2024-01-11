<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.8.0/bootstrap-slider.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.8.0/css/bootstrap-slider.min.css">
<link rel="stylesheet" type="text/css" href="css/toggle-switch.css">

<style>
.slider-selection, .slider-handle{
	background: #1f9797;
}
</style>

<div class="docking-page-container" ng-controller="DockingRunController">
	<ol class="circles-list">
		<li>
			<h3 class="item-title">Check your docking input files</h3>
			<ul class="list-group">
				<li class="list-group-item">
					<div class="row">
						<span class="col-xs-11">Protein ( <strong>1</strong> )</span>
						<button ng-hide="showProtein" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#proteinList" ng-click="showProtein = !showProtein">
							<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
						</button>
						<button ng-hide="!showProtein" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#proteinList" ng-click="showProtein = !showProtein">
							<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
						</button>
					</div>
					<ul id="proteinList" class="list-group collapse">
						<li class="list-group-item">{{$parent.proteinInput.codedName}}
<!-- 							<button type="button" class="btn btn-default btn-sm docking-delete-email" -->
<!-- 								aria-label="Remove Protein"> -->
<!-- 								<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> -->
<!-- 							</button> -->
						</li>
					</ul>
				</li>
				<li class="list-group-item">
					<div class="row">
						<span class="col-xs-11">Ligand ( <strong>{{getLigandTotalStructures()}}</strong> )</span>
						<button ng-hide="showLigands" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#ligandList" ng-click="showLigands = !showLigands">
							<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
						</button>
						<button ng-hide="!showLigands" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#ligandList" ng-click="showLigands = !showLigands">
							<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
						</button>
					</div>
					<ul id="ligandList" class="list-group collapse">
						<li class="list-group-item" ng-repeat="ligand in $parent.ligandInput">{{ligand.fileIdWithExtension}}  ( <strong>{{ligand.validStructure}}</strong> )
<!-- 							<button type="button" class="btn btn-default btn-sm docking-delete-email" -->
<!-- 								aria-label="Remove Ligand"> -->
<!-- 								<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> -->
<!-- 							</button> -->
						</li>
					</ul>
				</li>
				<li class="list-group-item">
					<div class="row">
						<span class="col-xs-11">Cofactor ( <strong>{{getCofactorTotalStructures()}}</strong> )</span>
						<button ng-hide="showCofactor" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#cofactorList" ng-click="showCofactor = !showCofactor">
							<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
						</button>
						<button ng-hide="!showCofactor" type="button" class="col-xs-1 btn btn-default btn-sm docking-plus-button"
							aria-label="Show Proteins" data-toggle="collapse"
							data-target="#cofactorList" ng-click="showCofactor = !showCofactor">
							<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
						</button>
					</div>
					<ul id="cofactorList" class="list-group collapse">
						<li class="list-group-item" ng-repeat="cofactor in $parent.cofactorInput">{{cofactor.fileIdWithExtension}}  ( <strong>{{cofactor.validStructure}}</strong> )
<!-- 							<button type="button" class="btn btn-default btn-sm docking-delete-email" -->
<!-- 								aria-label="Remove Ligand"> -->
<!-- 								<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> -->
<!-- 							</button> -->
						</li>
					</ul>
				</li>
			</ul>
		</li>

		<li ng-init="bindingSiteUserDefinedButton()">
			<h3 class="item-title">Define the binding site 
				<small><small>
					<a href="#bindingModal" data-toggle="modal" >
						<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
					</a>
				</small></small>
			</h3>
			<div>
				<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteUserDefinedSelected}" class="btn btn-default docking-pre-config-button" ng-click="bindingSiteUserDefinedButton()">User defined</button>
				<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteBlindDockingSelected}" class="btn btn-default docking-pre-config-button" ng-click="bindingSiteBlindDockingButton()">Blind docking</button>
				<button type="button" ng-class="{'docking-pre-config-selected' : isBindingSiteTestSelected}" class="btn btn-default docking-pre-config-button" ng-click="bindingSiteTestButton()">Test</button>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<section>
						<div class="row">
							<div class="col-xs-12">
								<!-- <h4>Grid center <small><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></small>:</h4>-->
								<h4>Grid center:</h4> 
							</div>
	<!-- 						<div class="col-xs-8"></div> -->
						</div>
						<div class="row spinner-row ">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>X:</p>  
							</div>
							<div class="col-xs-6">
								<input id="xGridCenterSlider" data-slider-id='XGridCenterSlider' type="text" data-slider-min="-180" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>  
							</div>
							<!-- <div class="col-xs-4" ng-class="{'has-error' : xGridCenterSliderNum > 180 || -180 > xGridCenterSliderNum || invalideX}">-->  
							<div class="col-xs-4" ng-class="{'has-error' : invalideX}">
								<input class="form-control grid-inputs" required id="xGridCenterInput" type="number" ng-model="xGridCenterSliderNum" 
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()">
							</div>
						</div>
						
						<div class="row spinner-row">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>Y:</p>  
							</div>
							<div class="col-xs-6">
								<input id="yGridCenterSlider" data-slider-id='YGridCenterSlider' type="text" data-slider-min="-180" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>  
							</div>
							<!-- <div class="col-xs-4" ng-show="!invalideY" ng-class="{'has-error' : yGridCenterSliderNum > 180 || -180 > yGridCenterSliderNum || invalideY}"  >  
									<input class="form-control grid-inputs" id="yGridCenterInput" type="number" ng-model="yGridCenterSliderNum">
							</div> -->
							<!-- <div class="col-xs-4" ng-class="{'has-error' : yGridCenterSliderNum > 180 || -180 > yGridCenterSliderNum || invalideY}"  >--> 
							<div class="col-xs-4" ng-class="{'has-error' : invalideY}"  > 
									<input class="form-control grid-inputs" id="yGridCenterInput" type="number" ng-model="yGridCenterSliderNum"
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()">
							</div> 
							
							<!-- <div class="row" ng-show="invalideY">
								<div class="col-xs-2"></div>
								<div class="col-xs-8 alert alert-danger value-alert" role="alert" >
									<buton type="button" class="close" data-dimiss="alert" arial-label="close"><span aria-hidden="true"></span></buton>
									<strong>Warning!</strong>Check value!
								</div>  
							</div>-->
						</div>
						
						<div class="row spinner-row ">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>Z:</p>  
							</div>
							<div class="col-xs-6">
								<input id="zGridCenterSlider" data-slider-id='ZGridCenterSlider' type="text" data-slider-min="-180" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>  
							</div>
							<!-- <div class="col-xs-4" ng-class="{'has-error' : zGridCenterSliderNum > 180 || -180 > zGridCenterSliderNum || invalideZ}"  >--> 
							<div class="col-xs-4" ng-class="{'has-error' : invalideZ}"  >  
								<input class="form-control grid-inputs" id="zGridCenterInput" type="number" ng-model="zGridCenterSliderNum"
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()">  
							</div> 
						</div>
					</section>
					
					<section>
						<div class="row spinner-row ">
							<div class="col-xs-12">
								<!-- <h4>Grid size <small><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></small>:</h4>--> 
								<h4>Grid size:</h4> 
							</div>
	<!-- 						<div class="col-xs-8"></div> -->
						</div>
						<div class="row spinner-row ">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>X:</p>  
							</div>
							<div class="col-xs-6">
								<!-- <input id="xGridSizeSlider" data-slider-id='XGridSizeSlider' type="text" data-slider-min="-180" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>-->
								<input id="xGridSizeSlider" data-slider-id='XGridSizeSlider' type="text" data-slider-min="0" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>    
							</div>
							<div class="col-xs-4" ng-class="{'has-error' : xGridSizeSliderNum > 180 || 0 > xGridSizeSliderNum || invXSize}"  >  
								<input class="form-control grid-inputs" id="xGridSizeInput" type="number" ng-model="xGridSizeSliderNum"
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()"> 
							</div> 
						</div>
						
						<div class="row spinner-row ">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>Y:</p>  
							</div>
							<div class="col-xs-6">
								<input id="yGridSizeSlider" data-slider-id='YGridSizeSlider' type="text" data-slider-min="-0" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>  
							</div>
							<div class="col-xs-4" ng-class="{'has-error' : yGridSizeSliderNum > 180 || 0 > yGridSizeSliderNum || invYSize}"  >  
								<input class="form-control grid-inputs" id="yGridSizeInput" type="number" ng-model="yGridSizeSliderNum"
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()">    
							</div> 
						</div>
						
						<div class="row spinner-row ">
							<div class="col-xs-1"></div>
							<div class="col-xs-1">
								<p>Z:</p>  
							</div>
							<div class="col-xs-6">
								<input id="zGridSizeSlider" data-slider-id='ZGridSizeSlider' type="text" data-slider-min="0" data-slider-max="180" data-slider-step="1" data-slider-value="14"/>  
							</div>
							<div class="col-xs-4" ng-class="{'has-error' : zGridSizeSliderNum > 180 || 0 > zGridSizeSliderNum || invZSize}"  >  
								<input class="form-control grid-inputs" id="zGridSizeInput" type="number" ng-model="zGridSizeSliderNum"
								data-toogle="popover" data-trigger="focus" data-placement="right" data-content="Please remove white spaces" ng-focus="focusFunction()">    
							</div> 
						</div>
					</section>
					
					
					<div class="row" ng-init="rstep=0.25" >
					 		<!-- <h4 class="col-xs-8">Discretization <small><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></small> :</h4>--> 
					 		<h4 class="col-xs-8">Discretization:</h4> 
							<div class="col-xs-4" ng-class="{'has-error' : 0 > rstep}">
								<input class="form-control grid-inputs" id="rstepInput" type="number" step="0.01" ng-model="rstep"">
							</div>								
					</div>
					
					<div class="row"> 
							<!-- <h4 class="col-xs-8">Total grid points <small><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></small> :</h4>-->
							<h4 class="col-xs-8">Total grid points:</h4>
							<div class="col-xs-4">
								<span ng-class="{'has-error' : points > 900000}" class="label label-default points-label">{{points}}</span>
	<!-- 							<input class=" form-control" type="number" ng-model="points"> (max 900.000) -->
							</div>
					</div>
					
				</div>
				
				<div class="col-xs-6 viewer-div ">
					 <div>
						<iframe id="dockingView" ng-src="{{viewerSrc}}" width="600" height="400" frameborder="0" scrolling="no"></iframe>
					 </div>
					 <div ng-init="view3DProtein=true;view3DCofactors=true;">
						 <label>Hide/Show</label>
						 <div class="row">
							 <div class="row">
								 <p class="col-xs-3">Protein</p> 
								 <label class="switch">
									<input type="checkbox" ng-model="view3DProtein">							
									<div class="checkbox-slider round"></div>
								</label>
							</div>
							 
							 <div class="row" ng-show="getCofactorTotalStructures()>0">
							 	 <p class="col-xs-3">Cofactors</p>
								 <label class="switch">
									<input type="checkbox" ng-model="view3DCofactors"> 							
									<div class="checkbox-slider round"></div>
								</label>
							</div>
						</div>
					 </div>
				</div>
				
			</div>
			
			<!-- NGL with iframe embedded -->
			<div class="row">
				<!-- 
				<div class="embed-responsive embed-responsive-4by3">
					<iframe class="embed-responsive-item" ng-src="{{srcViewerDockingNgl}}" allowfullscreen></iframe>
                </div>
				-->
				<!-- <div ng-include="'apps/docking/3D-viewer-ngl/view/nglViewerDocking.php'"></div>  -->
				<?php include_once ("apps/docking/3D-viewer-ngl/view/nglViewerDocking.php"); ?>

            </div>
		</li>

		<li ng-init="algorithmPrecisionStandardButton()">
			<h3 class="item-title">Select the search algorithm precision</h3>
			<div>
				<button type="button" ng-class="{'docking-pre-config-selected' : isAlgorithmPrecisionStandardSelected}" class="btn btn-default docking-pre-config-button" ng-click="algorithmPrecisionStandardButton()">Standard</button>
				<button type="button" ng-class="{'docking-pre-config-selected' : isAlgorithmPrecisionVirtualScreeningSelected}" class="btn btn-default docking-pre-config-button" ng-click="algorithmPrecisionVirtualScreeningButton()">Virtual Screening</button>
				<button type="button" ng-class="{'docking-pre-config-selected' : isAlgorithmPrecisionExplorerSelected}" class="btn btn-default docking-pre-config-button" ng-disabled="onlyVS" ng-click="algorithmPrecisionExplorerButton()">Explorer</button>
			</div>
			
			<div style="margin-top: 1%;">
				<div class="row">
					<h4 class="col-xs-4">Number of Evaluations:</h4> 
					<div class="col-xs-3" ng-class="{'has-error' : naval > 1000000 || 50000 > naval }">
						<input class=" form-control" id="navalInput" type="number" ng-model="naval" ng-disabled="onlyVS">
					</div>
				</div>
					
				<div class="row">
					<h4 class="col-xs-4">Population Size:</h4>
					<div class="col-xs-3" ng-class="{'has-error' : popsize > 1000 || 100 > popsize}">
						<input class=" form-control" id="popsizeInput" type="number" ng-model="popsize" ng-disabled="onlyVS">
					</div>
				</div>
				
				<div class="row">
					<h4 class="col-xs-4">Initial Seed:</h4>
					<div class="col-xs-3" ng-class="{'has-error' : seed > 0}">
						<input class=" form-control" id="seedInput" type="number" ng-model="seed" ng-disabled="onlyVS">
					</div>
				</div>
				
				<div class="row">
					<h4 class="col-xs-4">Number of Runs:</h4>
					<div class="col-xs-3" ng-class="{'has-error' : nrun > 32 || 8 > nrun }">
						<input class=" form-control" id="nrunInput" type="number" ng-model="nrun" ng-disabled="onlyVS">
					</div>
				</div>
			</div>
		</li>

		<li>
			<h3 class="item-title">Identify your docking job</h3>
			<div>
				<label>Job name:</label>
				<div class="row">
					<div class="col-xs-6">
						<div ng-class="{'has-success has-feedback' : jobName != null && jobName != ''}" class="form-group">
							<input type="text" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status" ng-model="jobName" maxlength="40"> 
							<span ng-show="jobName != null && jobName != ''" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span> 
							<span id="inputSuccess2Status" class="sr-only">(success)</span>
						</div>
					</div>
				</div>
			</div>
			<div ng-init="user.emails = [{id: 0, email: '', clicked : false}]">
				<label>E-mail:</label>
				<div class="row" ng-repeat="userEmail in user.emails">
					<div class="col-xs-6">
						<div ng-class="{'has-success has-feedback' : validateEmail(user.emails[$index].email), 'has-error' : !validateEmail(user.emails[$index].email) && user.emails[$index].clicked}" class="form-group">
							<input type="email" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status" ng-click="user.emails[$index].clicked = true"  ng-model="user.emails[$index].email"> 
							<span ng-show=" validateEmail(user.emails[$index].email)" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span> 
							<span id="inputSuccess2Status" class="sr-only">(success)</span>
						</div>
					</div>
					<button type="button" class="btn btn-default btn-sm docking-delete-email"
						aria-label="Delete email" ng-show="user.emails.length > 1" ng-click="removeEmailInput($index)">
						<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
					</button>
					<button type="button" class="btn btn-default btn-sm docking-plus-button"
						aria-label="Add email" ng-show="$index == (user.emails.length - 1)" ng-disabled="!validateEmail(user.emails[$index].email)" ng-click="addUserEmailInput($index)">
						<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div>
				 <div class="row">
					 <p class="col-xs-4">Subscribe DockThor e-Newsletters</p> 
					 <label class="switch">
						<input type="checkbox" ng-model="subscribe.value">							
						<div class="checkbox-slider round"></div>
					</label>
				</div>
				 <div class="row">
				 	<p class="col-xs-4">Accept <a href="#termsModal" data-toggle="modal">terms of use</a></p>
					 <!-- <p class="col-xs-4">Accept <a href="fragments/terms-of-use.php">terms of use</a></p> -->
					 <label class="switch">
						<input type="checkbox" ng-model="acceptTermsCheckBox.value">							
						<div class="checkbox-slider round"></div>
					</label>
				</div>
				
				<div align="center"> 
					<button class=" btn btn-primary send-to-dock-button" ng-disabled="!checkCanSubmit()" ng-click="submitJob()">
						<img width="25px" src="./images/logo_dockthor.png" style="margin-right: 6px;">
						Dock!
					</button> 
					<br>
				</div>
				<form id="goToResults" method="get" action="index.php">
					<input type="hidden" name="tab" value="DOCKING">
					<input type="hidden" name="page" value="RESULTS">
					<input type="hidden" name="jobId" value="{{$parent.$parent.job.id}}"> 
				</form>
			</div>
		</li>
	</ol>
		
	<!-- Modal - NGL viewer -->
	<!-- 
	<div class="modal fade" id="nglViewerDockingModal" tabindex="-1" role="dialog" aria-labelledby="nglViewerDockingModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<iframe id="nglViewerIframeDocking" width="100%" height="600px" src="apps/docking/3D-viewer-ngl/view/nglCombineViewer.php?combineType=docking" frameborder="0"></iframe>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	 -->
	 
</div>
<script>
	$('#xGridCenterSlider').slider({
		precision: 2,
		value: 0
	});
	
	$('#yGridCenterSlider').slider({
		precision: 2,
		value: 0
	});
	
	$('#zGridCenterSlider').slider({
		precision: 2,
		value: 0
	});
	
	$('#xGridSizeSlider').slider({
		precision: 2,
		value: 4
	});
	
	$('#yGridSizeSlider').slider({
		precision: 2,
		value: 4
	});
	
	$('#zGridSizeSlider').slider({
		precision: 2,
		value: 4
	});

	//removendo espaços digitados ou colados
 	$(function() {
 	    $('#xGridCenterInput, #yGridCenterInput, #zGridCenterInput, #xGridSizeInput, #yGridSizeInput, #zGridSizeInput, #rstepInput, #navalInput, #popsizeInput, #seedInput, #nrunInput').on('keypress', function(e) { 	        if (e.which == 32)
 	            return false;
 	    });
	    
 	});

 	// $('.nglIframe').css('height', ($(window).height())+'px');
	
</script>
<script type="text/javascript" src="apps/docking/js/docking.js"></script>