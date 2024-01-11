<link rel="stylesheet"
	href="apps/authentication/css/authentication-style.css">
<div class="container" ng-controller="LoginController">
	<div class="row">
		<div class="login-container col-md-4 form">
			<ul class="tab-group">
				<li class="tab active"><a href="#login">Log In</a></li>
				<li class="tab"><a href="#signup">Sign Up</a></li>
			</ul>
			<div class="tab-content">
				<div id="login">
					<form action="apps/authentication/login-action.php" method="post">
						<div class="form-group">
							<label for="inputEmail">Email</label> <input type="email"
								class="form-control" id="inputEmail" name="inputEmail"
								placeholder="Email">
						</div>
						<div class="form-group">
							<label for="inputPassword">Password</label> <input
								type="password" class="form-control" id="inputPassword"
								name="inputPassword" placeholder="Password">
						</div>
						<button type="submit" class="button-login button-login-block">Submit</button>
					</form>
					<div class="alert alert-danger" style="margin-top: 1%" role="alert"
						ng-show="loginError">{{loginError}}</div>
				</div>
				<div id="signup" class="signup-div">
					<div>
						<h2>Why to register?</h2>
						<ul>
							<li>Submit virtual screening experiments with up to 5000 ligands.</li>
							<li>Perform virtual screening experiments with the Santos Dumont supercomputer (according to availability).</li>
							<li>Run your job with priority.</li>
						</ul>
					</div>
					<div class="how-get-login-div">
						<h2>How to get a login?</h2>
						<ol>
							<li>Submit a ​short ​research project following the instructions.</li>
							<li>The project will be evaluated by an internal committee.</li>
							<li>In the case of the acceptance of your proposal, you will receive an e-mail with the instructions to access the restrict area of the DockThor web server.</li>
						</ol>
					</div>
					<div align="center">
						<button type="submit" class="button-login button-login-block" data-toggle="modal" data-target="#instrucionsModal">See instructions</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	
		<!-- Instructions Modal -->
	<div class="modal fade" id="instrucionsModal" tabindex="-1" role="dialog" aria-labelledby="instrucionsModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h2 class="modal-title" id="instrucionsModalLabel">Instructions</h2>
	      </div>
	      <div class="modal-body instructions-div">
	      	<ol>
	      		<li>Only the Research Coordinator (i.e. the researcher responsible for the project) should request the login. Applications from students (e.g. graduate, Master or PhD students) are not accepted.</li>
	      		<li>
		      		The project must contains the following parts​ (maximum 4 pages)​:
		      		<ul>
		      			<li>Researcher informations 
		      				<ul>
		      					<li>first and last names</li>
		      					<li>position</li>
		      					<li>department</li>
		      					<li>institution</li>
		      					<li>complete address</li>
		      					<li>e-mail</li>
		      				</ul>
<!-- 		      			(first and last names, position, department, institution, complete address and e-mail). -->
		      			</li>
		      			<li>Title of the project</li>
		      			<li>Keywords</li>
		      			<li>Introduction​</li>
		      			<li>Objectives</li>
		      			<li>Methodology</li>
		      			<li>Perspectives</li>
		      			<li>Signature</li>
		      		</ul>
	      		</li>
				<li>Guest users are able to submit up to 200 compounds per job. The project must clearly justify the need of the limit of up to 5000 compounds per submission. </li>
	      	</ol>
<!-- 	      	<p>Upload your PDF project file:</p> -->
<!-- 	      	<div class="form-group"> -->
<!-- 				<input type="file" class="form-control" id="pdfFile" name="pdfFile" accept=".pdf" placeholder="PDF file"> -->
<!-- 			</div> -->
<!-- 			<button type="button" class="button-login button-login-block">Upload your PDF</button> -->
			<form id="sendPDFForm" action="apps/authentication/send-pdf-action.php" method="POST" enctype="multipart/form-data">
				<span id="addPDFProjectFile" class="btn fileinput-button button-login button-login-block" ng-show="pdfFile == null || pdfFile == ''">
				                    <span>Upload your PDF</span>
				                    <input id="inputPDFProjectFile" type="file" name="pdfFile[]" accept=".pdf">
				 </span>
				 <div align="center" ng-show="pdfFile != null && pdfFile != ''" >
				 	<p class="pdf-file-name"><strong>{{pdfFile}}</strong> uploaded <a href="" ng-click="removePDFFile()"><span class="glyphicon glyphicon-trash remove-pdf-trash" style="color: red" aria-hidden="true"></span></a></p>
				 </div>
				<div class="row accept-div">
					 <p class="col-xs-5">Accept <a href="#termsModal" data-toggle="modal" >terms of use</a></p>
					 <!-- <p class="col-xs-5">Accept <button type="button" class="btn btn-link terms-of-use" data-toggle="modal" data-target="#termsModal">terms of use</button></p>-->
					 <label class="switch">
						<input type="checkbox" ng-model="acceptTermsCheckBox.value">							
						<div class="checkbox-slider round"></div>
					</label>
				</div>
			</form>
	      </div>
	      <div class="modal-footer">
	       	<!-- <button type="button" class="btn btn-default send-pdf-button" ng-disabled="(pdfFile == null || pdfFile == '') || !acceptTermsCheckBox.value" ng-click="sendPDFFile()">Send</button>-->
	       	<button type="button" class="btn btn-default send-pdf-button" data-toggle="modal" data-target="#uploadFileResult" ng-disabled="(pdfFile == null || pdfFile == '') || !acceptTermsCheckBox.value" ng-click="sendPDFFile()">Send</button>
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	  </div>
	</div>
		
	<!-- <div class="modal fade" id="uploadFileResult" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h2 class="modal-title" id="instrucionsModalLabel">File Sent with success!</h2>
	      </div>
	    </div>
	  </div>
	</div>-->
</div>
<script type="text/javascript" src="apps/authentication/js/index.js"></script>
