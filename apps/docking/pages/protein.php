<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript" src="apps/docking/js/protein.js"></script>
<script>
function bootbox_file() {
	bootbox_confirm("<form class='form-horizontal' enctype='multipart/form-data' id='infos' action='#'>\
	        <br/><input name='letter' type='file'>\
	         \
	        </form>", 'Upload', 'Cancel').modal('show');
	};

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


<div class="docking-page-container" ng-controller="ProteinController" ng-init="proteinInit()">

	<div class="overlay" ng-show="sendToDockStatus == 'sending'">
    	<img class="dockthor-loader" src="./images/logo_dockthor.png">
	</div>
	
	<ol class="circles-list">
		<li>
			<h3 class="item-title">Upload your protein file</h3>
			<form action="apps/docking/save-file.php" method="POST" enctype="multipart/form-data" auto-upload="false" ng-controller="FileUploadController">
				<div class="row fileupload-buttonbar">
		            <div class="col-lg-7">
						
		                <!--
							The fileinput-button span is used to style the file input field as button
							ng-click="queue=[];$parent.protein = null"
						-->
		                <span class="btn btn-success fileinput-button" ng-click="selectUploadFile()" ng-show="!(queue.length > 0)">
		                    <i class="glyphicon glyphicon-plus"></i>
		                    <span>Add file</span>
		                    <input type="file" name="files[]" accept=".in,.pdb" ng-class="{disabled: queue.length > 0}">
		                    <input type="hidden" id="fileType" name="fileType" value="PROTEIN" />
		                </span>
		                <span class="btn btn-success fileinput-button disabled" ng-show="(queue.length > 0)">
		                    <i class="glyphicon glyphicon-plus"></i>
		                    <span>Add file</span>
		                </span>
		                 <button type="button" class="btn btn-link" ng-click="selectTestFile(fileTestPath);">
 		                    <i class="glyphicon glyphicon-circle-arrow-right"></i>
 		                    <span> Select Test File</span> 
 		                </button> 
		            </div>
		        </div>
				
				<!-- Alvos preparados (carregamento de diretorios e arquivos automativos) -->	
                <div ng-init="validuser='<?php echo $_SESSION['VALID_SESSION_USER']?>'"></div>
				<resources-dir applabel="preparedResoucesLabel" type="preparedResoucesType" select="loadPreparedResource(selectedresource, queue)" selectedresource="selectedresource" validuser="validuser"></resources-dir>
				
				<!-- PREDEFINED FILE (test or target)- Table to visualize -->
				<table class="table table-hover table-responsive dockthor-table-hover" ng-show="$parent.protein.isPredefinedFile">
		      		<thead>
			            <tr>
					    	<th>File name</th>
					    	<th>Size</th> 
					    	<th>Status</th>
					    	<th>Actions</th>
	  					</tr>
  					</thead>
  					<tbody>
  						<tr>
  							<td>{{$parent.protein.name}}</td>
  							<td>{{$parent.protein.size/1024 | number : 2}} KB</td>
  							<td>
  								<div ng-hide="($parent.preparingFile == true) || ($parent.sendingFile == true) || $parent.protein.prepared == true || $parent.protein.error != null"">
									<span style="font-size:24px;color:rgb(204, 204, 204)" class="fa fa-circle-o-notch" data-toggle="tooltip" data-placement="right" title="Ready to send!"></span>	
								</div>
			                	<div ng-show="$parent.sendingFile == true">
									<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>
								</div>
								<div ng-show="$parent.preparingFile == true">
									<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>
								</div>
				                <div ng-show="$parent.protein.prepared == true">
									<span style="color:green;" class="glyphicon glyphicon-ok" aria-hidden="true" data-toggle="tooltip" ></span>
								</div>
								<strong ng-show="$parent.protein.error" class="error text-danger">{{$parent.protein.error}}</strong>  							
  							</td>
  							<td>
  							 	<button type="button" class="btn btn-primary upload-btn-xs" ng-click="sendFilePredefinedButton()" ng-disabled="$parent.sendingFile || $parent.preparingFile || ($parent.protein != null && $parent.protein.prepared)">
			                        <i class="glyphicon glyphicon-upload"></i>
			                        <span class="hidden-md hidden-sm hidden-xs">Send</span>
			                    </button>
  								<button type="button" class="btn btn-danger upload-btn-xs" ng-click="deleteTestFile()" ng-hide="$parent.sendingFile || $parent.preparingFile">
			                        <i class="glyphicon glyphicon-trash"></i>
			                    </button>
  							</td>
  						</tr>
  					</tbody>
				</table>		      
		      
		        <!-- USER UPLOAD FILE - The table listing the files available for upload/download -->
		        <table class="table table-hover table-responsive dockthor-table-hover files ng-cloak" ng-show="!$parent.protein.isPredefinedFile">
		            <thead>
			            <tr ng-show="(queue.length > 0)">
					    	<th>File name</th>
					    	<th>Size</th> 
					    	<th>Status</th>
					    	<th>Actions</th>
	  					</tr>
  					</thead>
  					<tbody>
			            <tr ng-repeat="file in queue" ng-class="{'processing': file.$processing()}">
			                <td>
			                	<p class="name" ng-switch data-on="!!file.url">
			                        <span ng-switch-when="true" ng-switch data-on="!!file.thumbnailUrl">
			                            <a ng-switch-when="true" ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}" data-gallery>{{file.name}}</a>
			                            <a ng-switch-default ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}">{{file.name}}</a>
			                        </span>
			                        <span ng-switch-default>{{file.name}}</span>
			                    </p>
			                </td>
			                <td>
			                    <p class="size">{{file.size | formatFileSize}}</p>
			                </td>
			                <td>
				                <div ng-hide="($parent.preparingFile == true) || ($parent.sendingFile == true) || (file.$state() == 'pending' || file.$state() == 'rejected' || file.prepared == true) || file.error != null"">
									<span style="font-size:24px;color:rgb(204, 204, 204)" class="fa fa-circle-o-notch" data-toggle="tooltip" data-placement="right" title="Ready to send!"></span>	
								</div>
			                	<div ng-show="$parent.sendingFile == true">
									<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>
								</div>
								<div ng-show="$parent.preparingFile == true">
									<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>
								</div>
				                <div ng-show="file.prepared == true">
									<span style="color:green;" class="glyphicon glyphicon-ok" aria-hidden="true" data-toggle="tooltip" ></span>
								</div>
								<span ng-show="file.error" class="fa fa-ban" style="font-size: 24px;color:red;" aria-hidden="true" data-toggle="tooltip" title="{{file.error}}"></span>
			                </td>
			                <td>
			                    <button type="button" class="btn btn-primary upload-btn-xs" ng-click="sendFileUploadButton(file)" ng-hide="!file.$submit || options.autoUpload" ng-disabled="file.$state() == 'pending' || file.$state() == 'rejected' || file.prepared == true || ($parent.sendingFile || $parent.preparingFile)">
			                        <i class="glyphicon glyphicon-upload"></i>
			                        <span class="hidden-md hidden-sm hidden-xs">Send</span>
			                    </button>								
			                    <button type="button" class="btn btn-warning upload-btn-xs" ng-click="cancelFileProccess(queue, file)" ng-show="$parent.sendingFile || $parent.preparingFile">
			                        <i class="glyphicon glyphicon-ban-circle"></i>
			                    </button>
			                    <button type="button" ng-controller="FileDestroyController" class="btn btn-danger upload-btn-xs" ng-click="deleteFile(file)" ng-hide="!file.$cancel || $parent.sendingFile || $parent.preparingFile">
			                        <i class="glyphicon glyphicon-trash"></i>
			                    </button>
			                </td>
			            </tr>
		            </tbody>
		        </table>
				
		        <!-- Alert success/failed -->
				<div class="alert alert-danger hidden-alert-danger none" style="width: 100%">
					<p></p>
				</div>
				<div class="alert alert-success hidden-alert-success none" style="width: 100%">
					<p></p>					
				</div>
				
			</form>
		</li>
		
		<li ng-class="{'li-disabled': !isPDBFile(protein)}" ng-show="protein != null && protein.prepared">
			<h3 class="item-title">
				Select the protonation states
				<button type="button" class="btn btn-link btn-md " data-toggle="modal" data-target="#protonationInfoModal" style="padding-left: 0px; padding-top: 0px;">
					<span class="glyphicon glyphicon-question-sign"></span>
				</button>
			</h3>
			<div ng-show="protein != null && protein.prepared && isPDBFile(protein)">
				<?php include ("apps/docking/protein-editor/pages/index.php"); ?>
			</div>
			<p ng-show="!isPDBFile(protein)"><i><span>* This area is just avaliable with 'pdb' files</span></i></p>			
		</li>
		
		<li ng-show="protein != null && (protein.prepared) && protein.error == null">
			<h3 class="item-title">Prepared protein file</h3>			
			<div align="center">
				<button type="button" class="btn btn-primary viewer-button" ng-show="(protein.prepared || !isPDBFile(protein)) && (viewerType=='jsmol')" ng-click="open3DModal()"> 		                   
					<i class="glyphicon glyphicon-eye-open"></i>
					View 3D
				</button>
				
				<button type="button" class="btn btn-primary viewer-button"
    					data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
    					ng-show="(protein.prepared || !isPDBFile(protein)) && (viewerType=='ngl')"
    					ng-click="open3DModalNGL()">
    				<i class="glyphicon glyphicon-eye-open"></i>
					View 3D
    			</button>        			
        			

				<div class="btn-group" ng-show="protein.prepared && isPDBFile(protein)">
					<a href="" class="btn btn-primary" aria-label="Left Align" ng-click="downloadFile('zip')">
						<span class="glyphicon glyphicon-circle-arrow-down" aria-hidden="true"></span>
						Download
					</a>
					<button type="button" class="btn btn-primary dropdown-toggle"
						data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="caret"></span> <span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu">						
						<li><a href="" ng-click="downloadFile('prep')">Prepared file (.pdb)</a></li>
						<li><a href="" ng-click="downloadFile('in')">Topology file (.in)</a></li>
						<li role="separator" class="divider"></li>
						<li><a href="" ng-click="downloadFile('zip')">Compacted folder (.zip)</a></li>
					</ul>
				</div>

				<a tabindex="0" role="button" class="btn send-to-dock-button" title="Send protein to DockThor?" ng-click="sendProteinToDock()">
					<img width="25px" src="./images/logo_dockthor.png" style="margin-right: 6px;">Send to DockThor
				</a>
				
				<br>
			</div>
		</li>
	</ol>
	
	<!-- 3D Viewer Modal -->
	<div class="modal fade" id="pdbViewerModal" tabindex="-1" role="dialog" aria-labelledby="pdbViewerModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="pdbViewerModalLabel">{{protein.name}}</h4>
	      </div>
	      <div class="modal-body">
	       <iframe id="proteinView" src="#" width="650" height="650" frameborder="0" scrolling="no"></iframe>
	      </div>
	      <div class="modal-footer">
	         <button type="button" class="btn btn-default" ng-click="$parent.reloadView3D('proteinView');">Reload</button>
	         <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	  </div>
	</div>
	
	<!-- 3D Viewer Modal NGL-->
	<div class="modal fade" id="nglViewerModalProtein" tabindex="-1" role="dialog" aria-labelledby="nglViewerModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<iframe id="nglViewerIframeProtein" width="100%" height="600px" src="apps/docking/3D-viewer-ngl/view/nglViewerProtein.php" frameborder="0"></iframe>					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	
</div>
