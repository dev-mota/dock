<script type="text/javascript" src="apps/docking/ligand-rotb-editor/pages/controller/ligandRotbEditorController.js"></script>
<link rel="stylesheet" type="text/css" href="css/toggle-switch.css">

<!-- <div ng-controller="LigandRotbEditorController" ng-init="test()">	 -->
<div ng-controller="LigandRotbEditorController">
	<br>
	Rotatable bonds: {{getRotbCount(rotbElements)}}
	<br>
	<div style="border: 1px solid lightgrey; border-radius: 5px; prestoreing: 10px; width: 100%;height: 250px;resize: vertical;overflow: auto;">
			
		<table class="table">
		
			<tr>
			    <th style="min-width:45px;width:45px;max-width:45px;"></th>
			    <th width="20%">#</th>
			    <th width="25%">Atom 1</th>
			    <th width="25%">Atom 2</th>
			    <th width="25%">Disable/Enable</th>
		  	</tr>
		  	
		  	<tr ng-repeat="(key, value) in rotbElements">
		  		<td style="min-width:45px;width:45px;max-width:45px;">
		  			<!-- Alert Index -->
					<span ng-show="rotbAlert_{{key}}" style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" >
					  
					</span>
		  		</td>
		  		<td width="20%">
		  			{{$index + 1}}		  			
		  		</td>
				<td width="25%">
					{{value[3]}}{{value[0]}}
				</td>
				<td width="25%">
					{{value[4]}}{{value[1]}}
				</td>
				<td width="25%">
					<label class="switch">
						<input type="checkbox" checked ng-model="value[2]" ng-click="checkModification()">							
					  	<div class="checkbox-slider round"></div>
					</label>
				</td>
			</tr>
			
		</table>
		
	</div>
	
	<br>
	Disable/Enable all: 
	<label class="switch">
		<input type="checkbox" checked ng-click="checkUnCheckAll()" id="checkUnCheckSwitch">
	  	<div class="checkbox-slider round"></div>
	</label>
	
	<!-- Apply button -->
	<br>
	<br>
	<button class="btn btn-default dockthor-button"ng-disabled="disableApplyButton" ng-click="apply()">Apply</button>
	
	<!-- Log -->
	<br>
	<br>
	<div class="panel panel-info" ng-hide="hideRotbLog" style="width: 100%">
		<div class="panel-heading">
			<a href="javascript:void(0);"
				onClick="$('.logData').slideToggle();"
				class="glyphicon glyphicon-plus plus-button"
				ng-click="collapsedLog=!collapsedLog" 
				ng-class="{'glyphicon-plus': collapsedLog,'glyphicon-minus': !collapsedLog}"></a>
			Modification log
			<div class="panel-body none logData">
				<ul>

					<!--  ng-if="value[0]!=null" because ngrepeat will show null elements. 
					ligandEditLog array only the indexes based on rotbElements modification.
					But the ngrepeat will show index from 0 to N.-->
					<li ng-repeat="(key, value) in ligandEditLogLast  track by $index" ng-hide="value[2]==null"> 
						<small>
							<small ng-if="value[2]==false">
								Bond {{value[0]}} - {{value[1]}} set as <b>rigid</b>
							</small>
							<small ng-if="value[2]==true">
								Bond {{value[0]}} - {{value[1]}} set as <b>flexible</b>
							</small>
						</small>
					</li>
										
				</ul>
			</div>
		</div>
	</div>
	
</div>
