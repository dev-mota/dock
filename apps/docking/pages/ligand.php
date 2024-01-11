<?php

// include ("apps/docking/pages/login-role.php");

$roleStructureValid = 200;

if($_SESSION['VALID_SESSION_USER']){
    $roleStructureValid = 5000;
}

?>

<script type="text/javascript" src="apps/docking/js/ligand.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/toggle-switch.css">

<script>
function bootbox_file() {
	bootbox_confirm("<form class='form-horizontal' enctype='multipart/form-data' id='infos' action='#'>\
	        <br/><input name='letter' type='file'>\
	         \
	        </form>", 'Upload', 'Cancel').modal('show');
	}

	function bootbox_confirm(msg, callback_success, callback_cancel) {
	var d = bootbox.confirm({message:msg, title:"Upload New Letter", show:false, callback:function(result) {
	    if (result)
	        console.log("Hi ");
	    else if(typeof(callback_cancel) == 'function')
	        callback_cancel();
	}});

	d.bind('shown.bs.modal', function(){
	    d.find("input:file").ace_file_input();
	});
	return d;
}	
	
</script>

<div class="docking-page-container" ng-controller="LigandController" ng-init="resetPage(<?php echo $roleStructureValid ?>)">

	<div class="overlay" ng-show="sendToDockStatus == 'sending'">
    	<img class="dockthor-loader" src="./images/logo_dockthor.png">
	</div>

	<div ng-controller="FileUploadController">
	
		<ol class="circles-list">
		
			<li>
				<h3 class="item-title">Upload your ligand file</h3>
				<!-- {{ligandStep}} -->
				<form action="apps/docking/save-file.php" method="POST" enctype="multipart/form-data" auto-upload="false" >
					<div class="row fileupload-buttonbar">
			        	<div id="add_div" class="col-lg-7">
	
							<!-- Upload file(s) -->
							<!-- <span class="btn btn-success fileinput-button" ng-show="!(queue.length >= 30)">-->
							<?php if(!$_SESSION['VALID_SESSION_USER']) {?>
								<span class="btn btn-success fileinput-button" ng-show="(queue.length < 100)">
				                    <i class="glyphicon glyphicon-plus"></i>
				                    <span>Add file</span>
									<!-- <input id="files" type="file" name="files[]" accept=".top, .mol2, .pdb, .sdf" ng-model="queue" ng-class="{disabled: queue.length >= 5}" onchange="angular.element(this).scope().addFile(this)" multiple> -->
									<input id="files" type="file" name="files[]" accept=".top, .mol2, .pdb, .sdf" ng-model="queue" ng-class="{disabled: queue.length >= 5}" multiple ng-click="removeAllFiles(queue, queueTestFile)">
				                    <input type="hidden" id="fileType" name="fileType" value="LIGAND" />
				                </span>
				                <span class="btn btn-success fileinput-button" ng-disabled="(queue.length >= 100)" ng-show="(queue.length >= 100)" disabled>
				                    <i class="glyphicon glyphicon-plus"></i>
				                    <span>Add file</span>
				                </span>
			                <?php }else{?>
			                	<span class="btn btn-success fileinput-button" ng-show="(queue.length < 1000)">
				                    <i class="glyphicon glyphicon-plus"></i>
				                    <span>Add file</span>
				                    <input id="filesLogin" type="file" name="files[]" accept=".top, .mol2, .pdb, .sdf" ng-model="queue" ng-class="{disabled: queue.length >= 5}" multiple ng-click="removeAllFilesWithConfirmation(queue, queueTestFile)"> <!-- onchange="angular.element(this).scope().addFile(this)" multiple>-->
				                    <input type="hidden" id="fileType" name="fileType" value="LIGAND" />
				                </span>
				                <span class="btn btn-success fileinput-button" ng-disabled="(queue.length >= 1000)" ng-show="(queue.length >= 1000)" disabled>
				                    <i class="glyphicon glyphicon-plus"></i>
				                    <span>Add file</span>
				                </span>
			                <?php }?>
			                
			                <!-- Select test files -->
	 		                <span class="dropdown">
	 		                	<button class="btn btn-link dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	 		                		<i class="glyphicon glyphicon-circle-arrow-right"></i>
	    							Select Test File
	    							<span class="caret"></span>
	  							</button>	  							
		 		                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
								  <li><a href="#" ng-init="queueTestFile = []" ng-click="promptSelectTestFile(queueTestFile, queue, $parent.ligandStep, selectSingleDockingTest)">Single docking (1 molecule)</a></li>
								  <li><a href="#" ng-init="queueTestFile = []" ng-click="promptSelectTestFile(queueTestFile, queue, $parent.ligandStep, selectVirtualScreeningTest)">Virtual Screening (17 molecules)</a></li>
								</ul>
							</span>
			                
			            </div>
			        </div>
					
                    <!-- Alvos preparados (carregamento de diretorios e arquivos automaticos) -->
                    <div ng-init="validuser='<?php echo $_SESSION['VALID_SESSION_USER']?>'"></div>
                    <resources-dir  applabel="preparedResoucesLabel" type="preparedResoucesType" select="loadPreparedResource(selectedresource, queue)" selectedresource="selectedresource" validuser="validuser"></resources-dir>
                    <?php if(!$_SESSION['VALID_SESSION_USER']) {?>
                        <small>* Attention: some datasets are available exclusively for approved projects and registered users. You can sign up submiting your project at the Login tab.</small>
                    <?php }?>
                    
                    <table class="table table-hover table-responsive dockthor-table-hover ng-cloak" ng-show="showResourceSelectedDir">
                        <thead>
                            <tr>
                                <th>Resource</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    {{resource}}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- File (s) table (upload) -->                    
                    <table class="table table-hover table-responsive dockthor-table-hover ng-cloak" ng-show="showFileTable">
			            <thead>
				            <tr>
								<!-- File index -->
								<th style="width: 4%">#</th>

								<!-- File modification alert (hidrogens and new)-->
				            	<th style="width: 4%"></th>

								<!-- File name -->
				            	<th>File name</th>

								<!-- File size -->
						    	<th>Size</th>

								<!-- Add hidrogens -->
						    	<th>Add H</th>

								<!-- File status -->
								<th>Status</th>

								<!-- File Action -->
						    	<th style="text-align: right;">Actions</th>
		  					</tr>
	  					</thead>

                        <tbody>
						  	<tr ng-repeat="file in choose(queue,queueTestFile)" ng-show="showItemForPagination($index, queue, pagination)">

								<!-- File index-->
							  	<td style="width: 4%">
								  	{{($index+1)}}
								</td>

								<!-- File modification alert (hidrogens and new) -->
								<td style="width: 4%">
									
									<!-- [DEBUG] Show? {{ showItemForPagination($index, queue, pagination) }} -->
                                    
									<div ng-show="ligandStep=='prepared'">
									
										<!-- If new file -->
										<div ng-show="file.state==undefined">
											<span style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="New file added!"></span>
										</div>
									
										<!-- If alter hidrogen -->
										<div ng-show="file.state=='prepared' || file.state=='failed' || file.state=='partialFailed'">
											<div ng-show="useTestFile">
												<div ng-show="changesAfterPrepared.testFile.file.hidrogen!=file.hidrogen">
													<span style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="Hydrogen changed!"></span>
												</div>
											</div>
											<div ng-show="!useTestFile">
												<div ng-show="changesAfterPrepared.queue.state">
													<div ng-show="changesAfterPrepared.queue.files[file.index].hidrogen!=file.hidrogen">
														<span style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="Hydrogen changed!"></span>
													</div>
												</div>
											</div>
										</div>		
															
									</div>
								</td>
								
								<!--  File name -->
								<td>
				                	<p class="name" ng-switch data-on="!!file.url">
				                        <span ng-switch-when="true" ng-switch data-on="!!file.thumbnailUrl">
				                            <a ng-switch-when="true" ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}" data-gallery>{{file.name}}</a>
											<a ng-switch-default ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}">{{file.name}}</a>
				                        </span>
				                        <span ng-switch-default>{{file.name}}</span>
				                    </p>		                    
				                </td>
				                
				                <!-- Size -->
				                <td>
				                    <p class="size">{{file.size | formatFileSize}}</p>
				                </td>
				                
				                <!-- Add hydrogens -->
				                <td>
				                	<label class="switch" ng-show="!isTOPFile(file)">
										<input type="checkbox" ng-init="file.hidrogen = false" ng-click="checkboxHydrogenOnClick(file,choose(queue,queueTestFile))">
									  	<div class="checkbox-slider round"></div>
									</label>
				                	<label class="switch disabled" ng-init="file.hidrogen = false" ng-show="isTOPFile(file)" disabled="disabled">
										<input class="disabled" type="checkbox" disabled="disabled">							
									  	<div class="checkbox-slider round disabled" disabled="disabled"></div>
	 								</label>
				                </td>
				                
								<!-- Status -->
								<td> 
									<div ng-show="file == undefined || file.state == undefined || file.state == ''" >
										<a href="javascript:void(0)" title="Ready to send!" data-toggle="tooltip" data-placement="right" tooltip>
											<span style="font-size:24px;color:rgb(204, 204, 204)" class="fa fa-circle-o-notch"></span>
										</a>
									</div>
                                    
									<div ng-show="(file.state == 'saving' || file.state == 'loading' || file.state == 'save-success') && !foundProblemAtSaveFiles">
										<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px; "></i>
                                    </div>
                                    
                                    <div ng-show="file.state == 'save-success' && foundProblemAtSaveFiles">
										<i class="fa fa-circle-o-notch" style="font-size:24px;color:rgb(204, 204, 204)" title="Ready to send!" data-toggle="tooltip" data-placement="right" tooltip></i>
                                    </div>
                                    
                                    <div ng-show="file.state == 'prepared'">
                                        <a href="javascript:void(0)" title="Success" data-toggle="tooltip" data-placement="right" tooltip>
                                            <span style="color:green;" class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                    
                                    <div ng-show="file.state == 'save-error'">
                                        <a href="javascript:void(0)" title="{{file.errorMessage}}" data-toggle="tooltip" data-placement="right" tooltip>
                                            <span class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                    
                                    <div ng-show="file.state == 'failed'">
                                        <a href="javascript:void(0)" title="Valid structures: {{file.validStructure}}; Invalid structures: {{file.invalidStructures}}" data-toggle="tooltip" data-placement="right" tooltip>
                                            <span class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                    
                                    <div ng-show="file.state == 'content-error'">
                                        <a href="javascript:void(0)" title="{{file.errorMessage}}" data-toggle="tooltip" data-placement="right" tooltip>
                                            <span class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true" ></span>
                                        </a>
                                    </div>
                                    
                                    <div ng-show="file.state == 'partialFailed'">
                                        <a href="javascript:void(0)" title="Valid structures: {{file.validStructure}}; Invalid structures: {{file.invalidStructures}}" data-toggle="tooltip" data-placement="right" tooltip>
                                            <span style="font-size: 24px;color:rgb(255, 204, 0);" class="fa fa-exclamation-triangle" aria-hidden="true"></span>
                                        </a>
                                    </div>
									
								</td>
				                
				                <!-- Actions -->
				                <td align="right">
				                    <!-- <button type="button" class="btn btn-warning upload-btn-xs" ng-click="cancelFileProccess(file,queue,queueTestFile)" ng-show="file.state == 'loading' || file.state == 'saving' || file.state == 'saved' || file.state == 'preparing'">
				                        <i class="glyphicon glyphicon-ban-circle"></i> 
				                    </button> -->
				                    <button ng-controller="FileDestroyController" type="button" class="btn btn-danger upload-btn-xs" ng-click="removeFileWithConfirmation(file,queue,queueTestFile)" ng-disabled="file.state == 'loading' || file.state == 'saving' || file.state == 'saved'">
				                        <i class="glyphicon glyphicon-trash"></i>
				                    </button>
				                </td>
				            </tr>
			            </tbody>
			        </table>

					<!-- PAGINATION -->
					<nav ng-show="(queue.length>0) && (useTestFile==false) && (queue.length>paginationOptions.numbersPerPage[0])" style="text-align: center">
                        <small>Total files: {{queue.length}}</small><br>
						<ul class="pagination">
                            <li>
								<a href="javascript:void(0);" aria-label="Next" ng-click="paginationFirst(queue)">
									<small aria-hidden="true">First</small>
								</a>
							</li>
							<li>
								<a href="javascript:void(0);" aria-label="Previous" ng-click="paginationPrevious()">
									<small aria-hidden="true">
                                        Previous
                                        <small 
                                            style="color:rgb(255, 204, 0);" 
                                            ng-class="checkPreviousPagesForAProblem(queue)" 
                                            aria-hidden="true" >
                                        </small>
                                    </small>
								</a>
							</li>
							<li ng-repeat="page in [].constructor(pagination.pages) track by $index" class="{{ ($index+1)==(pagination.selectedPage) ? 'active' : ''}}">
								<a href="javascript:void(0);" ng-click="paginationSelect($index)" ng-show="hidePage($index+1)">
									<small> {{$index+1}} 
                                        <small 
                                            style="color:rgb(255, 204, 0);" 
                                            ng-class="checkPageForAProblem(queue, $index)" 
                                            aria-hidden="true" >
                                        </small>
                                    </small>
								</a>
							</li>
                            <li>
								<a href="javascript:void(0);" aria-label="Next" ng-click="paginationNext(queue)">
									<small aria-hidden="true">
                                        Next
                                        <small 
                                            style="color:rgb(255, 204, 0);" 
                                            ng-class="checkNextPagesForAProblem(queue)" 
                                            aria-hidden="true" >
                                        </small>
                                    </small>
								</a>
							</li>
                            <li>
								<a href="javascript:void(0);" aria-label="Next" ng-click="paginationLast(queue)">
									<small aria-hidden="true">Last ({{pagination.pages}})</small>
								</a>
							</li>
						</ul>
						<br>
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Files per page: {{pagination.numberPerPage}} <span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<li><a href="javascript:void(0);" ng-repeat="opt in paginationOptions.numbersPerPage" ng-click="selectNumbersPerPage(opt)" ng-show="queue.length>=opt">{{opt}}</a></li>								
							</ul>
						</div>
					</nav>

			        <div align="right">
			        	<!-- Send all -->
				        <button ng-show="showSendButton" ng-disabled="disableSendButton" type="button" class="btn btn-primary upload-btn" ng-click="startMultFileProccess(queue,queueTestFile)">
				        	<i class="glyphicon glyphicon-upload"></i>
		                    <span class="hidden-md hidden-sm hidden-xs">Send</span>
						</button>
						
						<!-- Cancel all -->
				        <!-- 
				        <button type="button" class="btn btn-warning upload-btn" ng-click="cancelFileProccess(queue, file)" ng-disabled="disableCancellAllButton" ng-show="showCancellAllButton">
					        <i class="glyphicon glyphicon-ban-circle"></i>
		                    <span class="hidden-md hidden-sm hidden-xs">Cancel all</span>
						</button>
						-->
						 
						<!-- Remove all files -->				
				        <button type="button" class="btn btn-danger upload-btn" ng-click="removeAllFilesWithConfirmation(queue, queueTestFile)" ng-show="showRemoveAllButton">
							<i class="glyphicon glyphicon-trash"></i>
		                    <span class="hidden-md hidden-sm hidden-xs">Remove all</span>
						</button>
						
						<!-- All progress bar-->
						<div>
							<br>
							<div ng-show="showSuccessProgressBar">
								<span>Valid structures: <b>{{totalValidStructures}}</b> (max. {{roleStructureValid}})</span>
			 					<div class="progress">
			 						<div class="progress-bar progress-bar-striped progress-bar-success" ng-classrole="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: {{(totalValidStructures*100)/roleStructureValid}}%">
			 							<!-- <span style="float:left; color:black; padding-left:15px;">{{totalValidStructures}}</span> -->
			 						</div>
			 					</div>
			 				</div>
			 				
	 		 				<div ng-show="showFailProgressBar">
	 		 					<span>
	 		 						Valid structures: <b>{{totalValidStructures}}</b> (max. {{roleStructureValid}})
	 		 					</span>
	 		 					
	 		 					<!-- <div class="progress-bar progress-bar-striped progress-bar-danger" ng-classrole="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: {{(totalValidStructures*100)/roleStructureValid}}%"> -->
			 							<!-- <span style="float:left; color:black; padding-left:15px;">{{totalValidStructures}}</span>  -->
	 		 					<!-- </div> -->
	 		 					
	 		 					<div class="progress">
			 						<div class="progress-bar progress-bar-striped progress-bar-success" ng-classrole="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0">
			 						</div>
			 					</div>
			 					
			 					<span class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true"></span>
	 		 					<!-- Error: number of compounds exceeds the limit allowed. -->
                                The number of valid structures exceeds the limit allowed
	 		 				</div>
		 										
						</div>
					</div>
                    
					<div class="row" ng-show="loadingSend" style="text-align: center">
                        <i class="fa fa-circle-o-notch fa-spin" style="font-size:24px; "></i>
                        <br>loading ...            
                    </div>
                    
					<div ng-show="showEqualFileWarning">
 		 				<span class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true"></span>
 		 				Sorry, the following files have the same content:
 		 				<ul>
						  	<li ng-repeat="(key, value) in equalFiles">
						  		Same content: 
						  		<small ng-repeat="(ikey, ivalue) in value">
						  			<b>{{ivalue['originalName']}}</b>, 
						  		</small> 
							</li>
						</ul>
					</div>
					<!-- Alert changes-->
					<!-- 
					<div>
						<div ng-show="!useTestFile">
			 		        <div ng-show="changesAfterPrepared.queue.state">
			 		        	<br> 
					        	<span style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="There's changes to be done!"></span>
			 		        	<b><i><span> Some change occurred. Please click "Send" button</span></i></b><br> 
			 		        	<br>		        	 
			 		        </div> 
						</div>
						<div ng-show="useTestFile">
			 		        <div ng-show="changesAfterPrepared.testFile.state && showEqualFileWarning">
			 		        	<br> 
					        	<span style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="There's changes to be done!"></span>
			 		        	<b><i><span> Some change occurred in test file. Please click "Send" button</span></i></b><br> 
			 		        	<br>		        	 
			 		        </div> 
						</div>
					</div>
					 -->
					
			        <!-- Alert Index -->
			        <span ng-show="showModificationAlert" style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" data-toggle="tooltip" title="There's changes to be done!">					  
					</span>
					
					<!-- Alert success/failed -->
					<div class="alert alert-danger hidden-alert-danger none" style="width: 100%">
						<p></p>
					</div>
					<div class="alert alert-success hidden-alert-success none" style="width: 100%">
						<p></p>					
					</div>
					
				</form>
			</li>

            <!-- ROTB Editor (only for one file uploaded) -->
			<li ng-class="{'li-disabled': !showRotbEditor}" ng-show="showSecondStep && !showEqualFileWarning">
				<h3 class="item-title">
					Rotatable bond editor					
				</h3>
								
		        <div ng-show="showRotbEditor">
					<?php include ("apps/docking/ligand-rotb-editor/pages/rotb-editor.php"); ?>
				</div>
				<p ng-show="!showRotbEditor"><i><span>* This area is just avaliable when working with one valid ligand (pdb, sdf or mol2)</span></i></p>
			</li>
				
			<!-- Prepared file -->				
			<li ng-show="showThirdStep  && !showEqualFileWarning">
			
				<h3 class="item-title">Prepared ligand file</h3>
				
				<div align="center">

                    <!-- JSMol -->
					<button type="button" class="btn btn-primary viewer-button"
						ng-click="open3DModal(choose(queue,queueTestFile))"
						ng-show="(protein.prepared || !isPDBFile(protein) ) && (viewerType=='jsmol')">
						<i class="glyphicon glyphicon-eye-open"></i> View 3D
					</button>
                    
                    <!-- NGL -->
					<button type="button" class="btn btn-primary viewer-button"
						data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
						ng-show="(protein.prepared || !isPDBFile(protein) ) && (viewerType=='ngl') && show3D"
						ng-click="open3DModalNGL(queue)">
						<i class="glyphicon glyphicon-eye-open"></i> View 3D
					</button>

					<div class="btn-group" ng-show="ligandStep=='prepared' && downloadButton">
						<a href="" ng-click="downloadFile('zip')" class="btn btn-primary" aria-label="Left Align">
							<span class="glyphicon glyphicon-circle-arrow-down" aria-hidden="true"></span>
							Download
						</a>
						<button type="button" class="btn btn-primary dropdown-toggle"
							data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="caret"></span> <span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="" ng-click="downloadFile('map')">Map file (.csv)</a></li>
<!-- 							<li><a href="" ng-click="downloadFile('tableZip')">Properties table(s) (.csv)</a></li> -->
							<li><a href="" ng-click="downloadFile('topZip')">Topology file(s) (.top)</a></li>
							<li role="separator" class="divider"></li>															
							<li><a href="" ng-click="downloadFile('zip')">Compacted folder (.zip)</a></li>
						</ul>
					</div>
					 
					<!-- Send to Dock tab --> 
					
                    <!--
					<a tabindex="0" role="button" id="sendLigandToDockButton" class="btn send-to-dock-button" data-toggle="popover" data-trigger="focus" data-html="true" ng-click="$parent.queue = queue ; $parent.queueTestFile = queueTestFile" >
                        <img width="25px" src="./images/logo_dockthor.png" style="margin-right: 6px;">Send to DockThor
					</a>
                    -->
                    <a tabindex="0" role="button" class="btn send-to-dock-button" title="Send protein to DockThor?" ng-click="sendLigandToDock(queue, queueTestFile)" ng-disabled="totalValidStructures<1">
						<img width="25px" src="./images/logo_dockthor.png" style="margin-right: 6px;">Send to DockThor
					</a>
                    
					<br>
					
					<!-- Modal - JSmol viewer -->
					<div class="modal fade" id="ligandViewerModal" tabindex="-1" role="dialog" aria-labelledby="ligandViewerModalLabel">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="ligandViewerModalLabel">{{selectedToView.originalName}}</h4>
								</div>
								<div class="modal-body">
									<select class="form-control" ng-options="ligandToView.originalName for ligandToView in choose(queue,queueTestFile)" ng-model="$parent.selectedToView" ng-change="update3DModal(selectedToView)"></select>
									<iframe id="ligandView" src="#" width="650" height="650" frameborder="0" scrolling="no"></iframe>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" ng-click="$parent.reloadView3D('ligandView');">Reload</button>
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Modal - NGL viewer -->
					<div class="modal fade" id="nglViewerModalLigand" tabindex="-1" role="dialog" aria-labelledby="ligandViewerModalLabelNGL">
						<div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
								<div class="modal-body">
									<iframe id="nglViewerIframeLigand" width="100%" height="600px" src="apps/docking/3D-viewer-ngl/view/nglViewerCofactorOrLigand.php?structureType=ligand" frameborder="0"></iframe>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</li>
		</ol>

	</div>
	
</div>

<script>
    $("#files").on("change", function() {
         if($("#files")[0].files.length > 100) {
            alert("Error: maximum number of files is 200.");
            $("#files").attr('title', window.webkitURL ? ' ' : '');
            $("#files")[0].files=[];
         } else {
        	 angular.element(this).scope().addFile(this);
         }
    });

    $("#filesLogin").on("change", function() {
        if($("#filesLogin")[0].files.length > <?php echo $roleStructureValid ?>) {
            alert("Error: maximum number of files is <?php echo $roleStructureValid ?>");
            $("#filesLogin").attr('title', window.webkitURL ? ' ' : '')
            $("#filesLogin")[0].files=[];
        } else {
            angular.element(this).scope().addFile(this);
        }
   });   
</script>
