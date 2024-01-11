<?php 
// login success controll
if(!isset($_SESSION)){
	session_start();
}
$enabledAdmin = false;
if(isset ($_SESSION['dockthor_admin_login'])){
	$enabledAdmin = true;
	
}
if(!$enabledAdmin){
	// access denied
	echo 'You not allowed to access this page';	
}
?>

<script type="text/javascript" src="apps/authentication/js/reg_projects.js"></script>

<div ng-controller="RegProjController">
	<div class="page-container">
		<h1>Projects</h1>
		<br>
		<h2>User Registration</h2>
		<h4>**You only can do it if the user is already registered in SINAPAD ldap</h4>
		<form>
			<div class="row">
				<div class="form-group col-md-4">
					<label for="name">Name:</label> <input type="text"
						class="form-control" ng-model="reg.name">
				</div>
				<div class="form-group col-md-4">
					<label for="email">Email:</label> <input type="email"
						class="form-control" ng-model="reg.email">
				</div>
				<div class="form-group col-md-2">
					<label for="subject">Password:</label> <input type="password"
						class="form-control" ng-model="reg.passwd">
				</div>
				<div class="form-group col-md-2">
					<button class="btn btn-default dockthor-button"
						ng-disabled="(reg.name == null || reg.name == '') || (reg.email == null || reg.email == '') || (reg.passwd == null || reg.passwd == '')"
						ng-click="register()">Register</button>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-9">
					<div class="alert alert-success" role="alert" ng-show="success">
						{{message}}
					</div>
					<div class="alert alert-danger" role="alert" ng-show="error">
						{{message}}
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- 	<div class="row"> -->
<!-- 		<h2>Newsfeed</h2> -->
<!-- 		<div class="panel panel-default newsletter-content"> -->

<!-- 			<div class="panel-heading"> -->
<!-- 				Compose<a href="javascript:void(0);" -->
<!-- 					class="glyphicon glyphicon-plus plus-button " -->
<!-- 					onClick="$('.formData').slideToggle();"></a>	-->			
<!-- 			</div> -->
<!-- 			<div class="alert alert-danger none"> -->
<!-- 				<p></p> -->
<!-- 			</div> -->
<!-- 			<div class="alert alert-success none"> -->
<!-- 				<p></p> -->
<!-- 			</div> -->
<!-- 			<div class="panel-body none formData">			 -->
<!-- 				<form class="form" name="newsletterForm"> -->
				
					<!-- Subject -->
<!-- 					<div class="form-group"> -->
<!-- 						<label>Subject</label>  -->
<!-- 						<input type="text" class="form-control"	name="subject" ng-model="tempNewsletterData.subject" required/> -->
<!-- 						<i ng-show="newsletterForm.subject.$error.required">*required</i> -->
<!-- 					</div> -->
					
					<!-- Body -->
<!-- 					<div class="form-group"> -->
<!-- 						<label>Body</label>  -->
<!-- 						<textarea name="body" cols="40" rows="5" class="form-control" ng-model="tempNewsletterData.body" required></textarea> -->
<!-- 						<i ng-show="newsletterForm.body.$error.required">*required</i> -->
<!-- 					</div> -->
					
					<!-- Footer -->
<!-- 					<div class="form-group"> -->
<!-- 						<label>Footer</label> -->
<!-- 						<br> -->
<!-- 						<a href="apps/newsfeed/message-template/messageFooter.html" target="_blank">View template footer</a>	 -->
<!-- 						(to change, please do it manually in DockThor-3.0/apps/newsfeed/message-template/messageFooter.html)					 -->
<!-- 					</div> -->
					
					<!-- Image -->
<!-- 					<div class="form-group"> -->
<!-- 						<label>Image</label> -->
<!-- 						<input ng-hide="hideUploadButton" type="file" ngf-select ng-model="picFile" name="file"     -->
<!-- 							accept="image/*" ngf-max-size="2MB" -->
<!-- 							 ngf-model-invalid="errorFile"> -->
						<!-- <i ng-show="newsletterForm.file.$error.required">*required</i><br> -->
<!-- 						<i ng-show="newsletterForm.file.$error.maxSize">File too large  -->
<!-- 						  {{errorFile.size / 1000000|number:1}}MB: max 2M</i> -->
<!-- 						<button ng-hide="hideRemoveButton" ng-click="picFile = null" ng-show="picFile">Remove</button> -->
<!-- 						<br> -->
<!-- 						<img ng-show="newsletterForm.file.$valid" ngf-thumbnail="picFile" class="thumb">  -->
<!-- 						<br> -->
<!-- 						<br>						 -->
						<!-- <button ng-disabled="!newsletterForm.$valid" ng-click="uploadPic(picFile)">Submit</button> -->
<!-- 						<span class="progress" ng-show="picFile.progress >= 0">					 -->
							<!-- <div style="width:{{picFile.progress}}%"  ng-bind="picFile.progress + '%'"></div>-->
<!-- 						</span> -->
						<!-- <span ng-show="picFile.result">Upload Successful</span> -->
						<!-- <span class="err" ng-show="errorMsg">{{errorMsg}}</span> -->											  
<!-- 					</div> -->
					
<!-- 					<a href="javascript:void(0);" -->
<!-- 						ng-hide="hideCancelButton" ng-disabled="disableCancelButton" class="btn btn-warning" ng-click="cancelNewsletter()">Cancel</a> -->
					
<!-- 					<a href="javascript:void(0);" class="btn btn-success" ng-model="sendButton" -->
<!-- 						ng-hide="hideSendButton" ng-click="sendNewsfeed()" ng-disabled="(!newsletterForm.$valid)">Send</a> -->
					
<!-- 					<a href="javascript:void(0);" class="btn btn-success" -->
<!-- 						ng-hide="hideEditButton" ng-click="updateNewsletter()">Update</a> -->
						
<!-- 					<a href="javascript:void(0);" class="btn btn-info" -->
<!-- 						ng-hide="hideTestButton" ng-disabled="(!newsletterForm.$valid)" ng-click="sendTest()">Test</a> -->
<!-- 				</form> -->
<!-- 			</div> -->
			
<!-- 			<table class="table table-striped"> -->
<!-- 				<tr> -->
<!-- 					<th width="10%">Date</th> -->
					<!-- <th width="10%">to</th> <!-- email -->
<!-- 					<th width="15%">Subject</th> -->
<!-- 					<th width="45%">Body</th> -->
<!-- 					<th width="15%">Attachment</th>										 -->
<!-- 				</tr> -->
				
<!-- 				<tr ng-repeat="newsfeed in newsfeedList"> -->
<!-- 					<td>{{newsfeed.date}}</td> -->
<!-- 					<td>{{newsfeed.email}}</td> -->
<!-- 					<td>{{newsfeed.subject}}</td> -->
<!-- 					<td> -->
<!-- 						<div class="comment more">{{newsfeed.body}}</div> -->
<!-- 					</td>					 -->
<!-- 					<td> -->

<!-- 					</td>					 -->
<!-- 				</tr> -->
<!-- 			</table> -->
<!-- 		</div> -->
<!-- 	</div> -->
<!-- </div> -->



