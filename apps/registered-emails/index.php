<script type="text/javascript" src="apps/registered-emails/js/registered-emails.js"></script>

<div class="page-container" ng-controller="RegEmailsController" ng-init="getEmailsInfo();">
	<div class="row">
		Total active emails: <span>{{regEmailsList.length}}</span>
	</div>
	<div>
		<button class="btn btn-default" ng-click="getEmailsInfo();"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>Refresh List</button>
	</div>

	<table class="table table-striped" >
		<tr>
			<th>#</th>
			<th>Email</th>
			<th>Status</th>
		</tr>
		<tr ng-repeat="regemail in regEmailsList">
			<td>{{$index + 1}}</td>
			<td>{{regemail.email}}</td>
 		 	<td>{{regemail.status}}</td>
		</tr>
	</table>
</div>