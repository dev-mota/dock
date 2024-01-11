<!DOCTYPE html>
<html>

<!-- <script src="js/external/angular.min.js"></script> -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
<script type="text/javascript" src="apps/docking/protein-editor/pages/controller/proteinEditorController.js"></script>
<!-- <link rel="stylesheet" type="text/css" href="css/style.css"> -->

<body>

	<div ng-controller="ProteinEditorController">
		
		<!-- scroll -->
		<div style="border: 1px solid lightgrey; border-radius: 5px; padding: 10px; width: 100%;height: 250px;resize: vertical;overflow: auto;">
		
			<div class="list-group-item" style="border: 0px solid" ng-repeat="(chainKey, chainValue) in chains">
				
				<button ng-init="collapsedChain=false" type="button" class="btn btn-outline-secondary btn-xs open-level-button" ng-click="collapsedChain=!collapsedChain">
		        	<span class="glyphicon glyphicon-chevron-right" 
		        	ng-class="{'glyphicon-chevron-down': collapsedChain,'glyphicon-chevron-right': !collapsedChain}"></span>
		        </button>
				<span>
					Chain {{ chainKey }}
					
					<!-- Alert chain -->
					<!-- 
					<span ng-show="count_{{chainKey}}>0" style="color:#999900;" class="fa fa-exclamation-circle" data-toggle="tooltip" data-placement="right" title="File is not prepared" aria-hidden="true" >
						 {{this['count_'+this.chainKey]}}
					</span>
					-->
					<span ng-show="count_{{chainKey}}>0" style="background:#209a9a; margin: 0 3em 0 0" class="badge" data-toggle="tooltip" aria-hidden="true" >
						{{this['count_'+this.chainKey]}}
					</span>
					
				</span>
				
				</br>
				<ul ng-show="collapsedChain" collapsedAtom="false" style="padding-left:5%; " ng-repeat="(atomKey, atomValue) in chainValue ">	
				
					<div ng-show="checkIfHasOptions(atomValue)">
						<li class="list-group-item" style="border: 0px solid">
							
							<button type="button" class="btn btn-outline-secondary btn-xs open-level-button" ng-click="collapsedAtom=!collapsedAtom">
					        	<span class="glyphicon glyphicon-chevron-right" 
					        	ng-class="{'glyphicon-chevron-down': collapsedAtom,'glyphicon-chevron-right': !collapsedAtom}"></span>
					        </button>
					        
						    <span>
						    	{{ atomKey }}
						    	<!-- Alert atom -->						    	    
							    <span ng-show="count_{{chainKey+atomKey}}>0" style="background: #209a9a; margin: 0 3em 0 0" class="badge" data-toggle="tooltip" aria-hidden="true" >
									 {{this['count_'+this.chainKey+this.atomKey]}}
								</span>
						    </span>
						    
						    <div style="padding-left:5%;">
						    	<table ng-show="collapsedAtom" class="table table-striped table-responsive">
						    	
									<tr>
									    <th></th>
									    <th>Residue index</th>
									    <th>Residue number</th>			    
									    <th>
									    	Protonation state
									    	<button type="button" class="btn btn-link btn-xs" data-toggle="modal" data-target="#protonationInfoModal">
											  <span class="glyphicon glyphicon-question-sign"></span>
											</button>											
									    </th>
								  	</tr>
								  	
									<tr ng-repeat="(indexKey, indexValue) in atomValue">
										<td>
											<!-- Alert Index -->
											<span ng-show="count_{{chainKey+atomKey+indexKey}}>0" style="color:#209a9a" class="fa fa-exclamation-circle fa-lg" data-toggle="tooltip" data-placement="right" aria-hidden="true" >
											 <!-- {this['count_'+this.chainKey+this.atomKey+this.indexKey]}}  -->  
											</span>
										</td>
										<td style="vertical-align: middle">
									    	<span>{{indexKey}}</span>
									    </td>
									    <td style="vertical-align: middle">
									    	<span>{{indexValue.state.index}}</span>
									    </td style="vertical-align: middle">
									    <td>						    	
									    	<select ng-if="indexValue.options.length != 0" 
												class="form-control" 
												ng-if="indexValue.options.length" 
												ng-model="indexValue.state.value"
												ng-options="opt for opt in indexValue.options"
												ng-change="checkChanges(chainKey,atomKey,indexKey,indexValue.state.value)"></select>
											<div ng-if="indexValue.options.length == 0">
												<span>-</span>
											</div>											
									    </td>				    
									</tr>
								</table>   
						   	<div>
						</li>
					</div>
				</ul>
			</div>	
		</div>
		
		<br>
		<!-- Apply button -->
		<button class="btn btn-default dockthor-button" ng-click="sendJsonEditted()" ng-disabled="count_all==0">Apply</button>
		<!-- Reset button -->
		<button class="btn btn-default dockthor-button" ng-click="proteinEditorResetAppScope()" ng-disabled="count_all==0">Reset</button>
		
		<!-- Log -->
		<br>
		<br>
		<div class="panel panel-info" ng-hide="proteinLog" style="width: 100%">
			<div class="panel-heading">
				<a href="javascript:void(0);"
					onClick="$('.logData').slideToggle();"
					class="glyphicon glyphicon-plus plus-button"
					ng-click="collapsedLog=!collapsedLog" 
					ng-class="{'glyphicon-plus': collapsedLog,'glyphicon-minus': !collapsedLog}"></a>
				Modification log
				<div class="panel-body none logData">
					<ul>
						<li ng-repeat="(key, value) in proteinEditLog"> 
							<small> 
								Chain {{proteinEditLog[key]['chain']}}: {{proteinEditLog[key]['atom']}} {{proteinEditLog[key]['index']}} was set as {{proteinEditLog[key]['after']}}
							</small>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<!-- Alert success/failed
		<div class="alert alert-danger none" style="width: 100%">
			<p></p>
		</div>
		<div class="alert alert-success none" style="width: 100%">
			<p></p>					
		</div> -->
		
	</div>
	
	<!-- Protonation Info Modal -->
	<div id="protonationInfoModal" class="modal fade" role="dialog">
	  <div class="modal-dialog modal-lg">
	
	    <!-- Modal content-->
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <!--
		<h3 align="center" class="modal-title">Protonation States for Amino Acid Residues</h3><br>
	        <h4> Carbon, nitrogen, oxygen and sulfur atoms are colored grey, blue, red and yellow, respectively. Yellow sphere highlights the C&#945;. * indicates the default protonation state. </h4>
		-->
	      </div>
	      <div class="modal-body" >
	        <img src="apps/docking/protein-editor/pages/images/protonation_states2.png" alt="Residuos Info" style="max-width:100%;max-height:100%;">
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	      </div>
	    </div>
	
	  </div>
	</div>
</body>
</html>
