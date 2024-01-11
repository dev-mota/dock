<script type="text/javascript" src="apps/jobs-monitoring/js/jobsMonitoringController.js"></script>
<style>
.tooltip-inner {
    max-width: 100% !important;
}
</style>
<div class="page-container" ng-controller="JobsMonitoringController-v2" ng-init="getJobsInfo(from,jobsPerPage,selectedFilter);">

	<div class="row">
		<form class="form-inline">
            <div class="btn-group">
				<button type="button" class="btn btn-primary" ng-click="submitTestJob('short1lig')">Submit 1 ligant (short)</button>
              	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                		<span class="caret"></span>
                		<span class="sr-only">Toggle Dropdown</span>
              	</button>
              	<ul class="dropdown-menu">
                		<li><a href="#" ng-click="showPropertyFileTest('short1lig')">View properties</a></li>
				</ul>
            </div>
            
            <div class="btn-group">
				<button type="button" class="btn btn-primary" ng-click="submitTestJob('complete1lig')">Submit 1 ligant (real)</button>
              	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                		<span class="caret"></span>
                		<span class="sr-only">Toggle Dropdown</span>
              	</button>
              	<ul class="dropdown-menu">
                		<li><a href="#" ng-click="showPropertyFileTest('complete1lig')">View properties</a></li>
				</ul>
            </div>
            
            <div class="btn-group">
				<button type="button" class="btn btn-primary" ng-click="submitTestJob('shortVs')">Submit VS (short)</button>
              	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                		<span class="caret"></span>
                		<span class="sr-only">Toggle Dropdown</span>
              	</button>
              	<ul class="dropdown-menu">
                		<li><a href="#" ng-click="showPropertyFileTest('shortVs')">View properties</a></li>
				</ul>
            </div>
            
            <div class="btn-group">
				<button type="button" class="btn btn-primary" ng-click="submitTestJob('completeVs')">Submit VS (real)</button>
              	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                		<span class="caret"></span>
                		<span class="sr-only">Toggle Dropdown</span>
              	</button>
              	<ul class="dropdown-menu">
                		<li><a href="#" ng-click="showPropertyFileTest('completeVs')">View properties</a></li>
				</ul>
            </div>
		</form>			
	</div>
	
	<div class="row">
	
		<form class="form-inline">
		
			<div class="form-group">
            
            	    <!-- Refresh button -->	
				<button class="btn btn-default" ng-click="refresh();"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>Refresh</button>
        			<!-- clear button -->	
        			<button class="btn btn-default" ng-click="clear();"><span class="glyphicon glyphicon-erase" aria-hidden="true"></span>Clear</button>
            
        			<!-- Job per page button -->
        			<div class="btn-group" >
        				<button class="btn btn-default" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        						{{jobsPerPage}} jobs per page <span class="caret"></span>
        				</button>
        				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        					<li ng-model="jobsPerPage" ng-repeat="valueToShow in jobsPerPageList" ng-click="changeJobsPerPage(valueToShow)"><a href="#">{{valueToShow}}</a></li>				
        				</ul>
        			</div>
        			
        			<!-- Filter job status -->
        			<div class="btn-group" >
        				<button class="btn btn-default" type="button" id="dropdownMenuFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        						Folder: {{selectedFilter}} <span class="caret"></span>
        				</button>
        				<ul class="dropdown-menu" aria-labelledby="dropdownMenuFilter">
        					<li ng-model="selectedFilter" ng-repeat="filterValue in filterList" ng-click="changeFilter(filterValue)"><a href="#">{{filterValue}}</a></li>				
        				</ul>
        			</div>	
        			
        			<!-- Filter quantity of ligants-->
        			<div class="input-group">
              		<!-- Job per page button -->
            			<div class="btn-group" >
            				<button class="btn btn-default" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            						Ligants: {{selectedFilterNumberOfLigant}} <span class="caret"></span>
            				</button>
            				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
            					<li ng-model="selectedFilterNumberOfLigant" ng-repeat="filterValue in filterNumberOfLigantsOption" ng-click="changeFilterLigantQuantity(filterValue)"><a href="#">{{filterValue}}</a></li>            					
            				</ul>
            			</div>
				</div>							
				
          	</div>
          	
        </form>
        
	</div>
	
	<div class="row">
	
		<form class="form-inline">
		
			<div class="form-group">
			
				<!-- Job name search -->
				<div class="input-group">
					<span class="input-group-addon">Job Name</span>
					<input type="text" class="form-control" placeholder="type here" style="height:42px; " ng-model="jobNameToSearch" >
					<div class="input-group-btn">
              			<button class="btn btn-default" type="submit" style="height:42px" ng-click="searchJob()">
                				<i class="glyphicon glyphicon-search"></i>
              			</button>
            			</div>
              	</div>
			
				<!-- From -->
				<div class="input-group">
                		<span class="input-group-addon">From</span>
                  	<input class="form-control" type="date" id="exampleInput" name="input" ng-model="jobDateFrom" placeholder="yyyy-MM-dd" style="height:42px;"/>
                  	<span class="input-group-btn">
                    		<button class="btn btn-default" type="button" ng-click="searchJob()">
                    			<i class="glyphicon glyphicon-search"></i>
                    		</button>
                  	</span>
    				</div>
                
                <!-- To -->
                <div class="input-group">
                		<span class="input-group-addon">To</span>
                  	<input class="form-control" type="date" id="exampleInput" name="input" ng-model="jobDateTo" placeholder="yyyy-MM-dd" style="height:42px;" />
                  	<span class="input-group-btn">
                    		<button class="btn btn-default" type="button" ng-click="searchJob()">
                    			<i class="glyphicon glyphicon-search"></i>
                    		</button>
                  	</span>
    				</div>
    				
			</div>
			
		</form>
		
	</div>
	
	<div ng-show="jobsArray.length == 0">
		<div style="text-align: center">
			<br><br><br>
			<p><i>Jobs not found</i></p>
		</div>
	</div>		
	
	<div ng-show="jobsArray.length > 0">
		
        <!-- Pagination -->
		<div class="row" align="center">
        		<nav>
        			<ul class="pagination">
        				<li>
        					<a href="#" aria-label="First" ng-click="firstPage()">
            					<span aria-hidden="true">First</span>
            				</a>
    					</li>
    					<li class="{{previousUsage}}">
            				<a href="#" aria-label="Previous" ng-click="previousPage()">
            					<span aria-hidden="true">Previous</span>
            				</a>
                    	</li>
                    	<li class="active" ng-model="currentPage">
                  		<a href="#">
                  			{{currentPage}}
                  		</a>
                  	</li>
                  	<li class="{{nextUsage}}">
            				<a href="#" aria-label="Next" ng-click="nextPage()">
            					<span aria-hidden="true">Next</span>
            				</a>
                    </li>
                    <li>
            				<a href="#" aria-label="Last" ng-click="lastPage()">
            					<span aria-hidden="true">Last ({{lastPageIndex}})</span>
            				</a>
                    </li>
                </ul>                    
    			</nav>
    			<p>Jobs found: {{totalJobs}}</p>
    		</div>
	
	    <!-- Table data -->
		<table class="table">
        		<tr>
        			<th>#</th>
        			<th>Portal Job ID</th>
        			<th>Date</th>
        			<th>Folder</th>
        			<th>Action</th>
        			<th style="text-align: center">
        				Service Job 
        				<a href="#modalStatusInfo" data-toggle="modal"><span class="glyphicon glyphicon-exclamation-sign"></span></a>
        			</th>
        		</tr>
        
        		<tr ng-repeat="(jobKey, jobValue) in jobsArray" ng-init="serviceId.seleceted=null">
        
        			<!-- Index coloumn -->
        			<td>{{buildIndex(jobKey, $index)}}</td>
         		 	
         		<!-- Id coloumn -->
        			<!-- <td style="word-break: break-all;">{{jobValue.id}}</td> -->
				<td style="  overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 100px;">
					<a href="#0" title="{{jobValue.id}}" data-toggle="tooltip" data-placement="top" tooltip>
						{{jobValue.id}}
					</a>
				</td>
        			
        			<!-- Date coloumn -->
        			<td><b>{{jobValue['submission-date']}}</b></td>
        			
        			<!-- Daemon folders  -->
        			<td>
        				<div ng-repeat="(folderKey, folderValue) in jobValue.folders">
        					{{folderValue}}<br>
        				</div>
        				<div ng-show="jobValue.folders==0">
        					<span class="glyphicon glyphicon-exclamation-sign"></span>Not found
					</div>
        			</td>
        			
        			<!-- Action coloumn -->
        			<td>
        				<div class="dropdown">
        					<button class="btn btn-primary btn-xs dropdown-toggle"
        						type="button" id="dropdownMenu1" data-toggle="dropdown"
        						aria-haspopup="true" aria-expanded="true">
        						<span class="caret"></span>
        					</button>
        					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
    							<li>
    								<a href="" ng-show="checkIfHasJobActive(jobValue['property-file'][jobValue.id]['submissions'])" ng-click="resubmitJob(jobValue.id)">Resubmit</a></li>
        						<li>
        						<a href="" ng-click="showPropertyFile(jobValue.id)">Property file</a></li>
        					</ul>
        				</div>
        			</td>
        			
        			<!-- Service Jobs coloumn-->
        			<td>
        				<table class="table" >
        					<tbody>
        						<tr ng-repeat="(submissionKey, submissionValue) in jobValue['property-file'][jobValue.id]['submissions']" style="text-align: left; height:80px;">
        						
        							<!-- Index -->
        							<!-- <td>{{$index+1}}</td>  -->
        								
                                 <!-- Service ID -->
                                 <td class="col-md-3">
                                 	<div ng-switch on="checkAndPrintSubmissionId(submissionKey)">
                                 		<div ng-switch-when="N/A">
                                 			<a href="#0" title="Service submission id: Not Available!" data-toggle="tooltip" data-placement="top" tooltip>
                                 				N/A
                                 			</a>	
                                 		</div>
                                 		<div ng-switch-default>
                                 			<a href="#0" title="{{checkAndPrintSubmissionId(submissionKey)}}" data-toggle="tooltip" data-placement="top" tooltip>
                                 				{{checkAndPrintSubmissionId(submissionKey)}}
                                 			</a>
                                 		</div>
                                 	</div>
                                 </td>
                                 
                                 <!-- Service submission date -->
                                 <td class="col-md-2">
                                 	<div ng-switch on="checkAndPrintDate(submissionValue['service-submission-date'])">
                                 		<div ng-switch-when="N/A">
                                 			<a href="#0" title="Service submission date: Not Available!" data-toggle="tooltip" data-placement="top" tooltip>
                                 				N/A
                                 			</a>	
                                 		</div>
                                 		<div ng-switch-default>
                                 			{{checkAndPrintDate(submissionValue['service-submission-date'])}}
                                 		</div>
                                 	</div>
                                 </td>
                                 
                                 <!-- Job resource -->
                                 <td class="col-md-3">
                                 	<div ng-switch on="checkAndPrintResource(submissionValue['service-resource'])">
                                 		<div ng-switch-when="N/A">
                                 			<a href="#0" title="Job resource: N/A" data-toggle="tooltip" data-placement="top" tooltip>
                                 				N/A
                                 			</a>	
                                 		</div>
                                 		<div ng-switch-default>
                                 			<a href="#0" title="{{submissionValue['service-resource']}}" data-toggle="tooltip" data-placement="top" tooltip>
                                 				{{checkAndPrintResource(submissionValue['service-resource'])}}
                                 			</a>
                                 		</div>
                                 	</div>
                                 </td>
                                 
                                 <!-- Job status -->
        							<td class="col-md-1">
        								<!--  {{checkAndPrintStatus(submissionValue['job-service-status'])}} -->
        								
                                    	<div ng-switch on="submissionValue['job-service-status']">
                                     	<div ng-switch-when="DONE">
                                         		<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											<span class="glyphicon glyphicon-ok"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-when="CANCELLED">
                                         		<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											<span class="glyphicon glyphicon-ban-circle"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-when="FAILED">
                                         		<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											<span style="color: red" class="glyphicon glyphicon-remove-circle"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-when="RUNNING">
                                         		<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											<i class="fa fa-cog fa-spin" style="font-size:22px;"></i>
        										</a>
                                        	</div>
										<div ng-switch-when="WAITING">
                                         		<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											<span style="color: red" class="glyphicon glyphicon-time"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-when="">
                                         		<a href="#0" title="Job status: Not Available" data-toggle="tooltip" data-placement="top" tooltip>
        											<span class="glyphicon glyphicon-question-sign"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-when="undefined">
                                         		<a href="#0" title="Job status: Not Available" data-toggle="tooltip" data-placement="top" tooltip>
        											<span class="glyphicon glyphicon-question-sign"></span>
        										</a>
                                        	</div>
                                        	<div ng-switch-default>
                                            	<a href="#0" title="Job status: {{submissionValue['job-service-status']}}" data-toggle="tooltip" data-placement="top" tooltip>
        											{{checkAndPrintStatus(submissionValue['job-service-status'])}}
        										</a>
                                        	</div>                                        	
                                    </div>
        							</td>
        							
        							<!-- Number of ligants -->
        							<td class="col-md-2">
        								<a href="#0" title="Number of ligants" data-toggle="tooltip" data-placement="top" tooltip>
        									{{checkAndPrintNumLig(submissionValue['file-args']['l'].length)}}
        								</a>
        							</td>
        							
        							<!-- Action buttons -->
        							<td class="col-md-1">
        								<div ng-show="(submissionKey != 'pending') && (submissionValue['job-service-status'] != 'CANCELLED')">
        									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#showJobServiceModal" ng-click="jobServiceAction(checkAndPrintSubmissionId(submissionKey), jobValue.id, submissionValue['job-service-status'])" >
        										<span class="glyphicon glyphicon-cog" aria-hidden="true" ></span>
        									</button>
        								</div>
        							</td>
							</tr>						
        					</tbody>
        				</table>
        
        			</td>
        
        		</tr>
        	</table>
	</div>
	
	
	
    <!-- Modal to show property file -->
  	<div class="modal fade" id="showPropertyModal" role="dialog">
    		<div class="modal-dialog">
    
            <!-- Modal content-->
          	<div class="modal-content">
            		<div class="modal-header">
              		<button type="button" class="close" data-dismiss="modal">&times;</button>
              		<h4 class="modal-title">Property file</h4>
            		</div>
                <div class="modal-body">
                  	<pre><code>{{propertyFileContent}}</code></pre>
                </div>        
          	</div>
      
    		</div>
  	</div>
  	
  	<!-- Modal to show property file -->
  	<div class="modal fade" id="showJobServiceModal" role="dialog">
    		<div class="modal-dialog">
    
            <!-- Modal content-->
          	<div class="modal-content">
          	
            		<div class="modal-header">
              		<button type="button" class="close" data-dismiss="modal">&times;</button>
              		<h4 class="modal-title" style="text-align: center"> 
              			{{jobServiceActionModel.portalId}}<br>
              			{{jobServiceActionModel.serviceId}}<br>
              			<!-- {{jobServiceActionModel.serviceStatus}}  -->              			
              		</h4>
            		</div>
            		
                <div class="modal-body" style="text-align: center">
                		
                		<!-- Cancel job -->
                		<div class="btn-group-vertical" role="group" aria-label="...">
						<button type="button" class="btn btn-primary" ng-show="jobServiceActionModel.serviceStatus == 'RUNNING'" ng-click="cancelJob(jobServiceActionModel)">Cancel Job</button>
					</div>
					 
					<!-- Download results -->
        				<div class="btn-group-vertical" role="group" aria-label="...">
        					<button type="button" class="btn btn-primary" ng-show="jobServiceActionModel.serviceStatus == 'DONE'" ng-click="downloadResults(jobServiceActionModel);">Download Results</button>
        				</div>
        				
        				<!-- Download error -->
					<div class="btn-group-vertical" role="group" aria-label="...">
						<button type="button" class="btn btn-primary" ng-show="jobServiceActionModel.serviceStatus == 'FAILED'">Download Error Output</button>						
        				</div>
        				
        				<!-- Download log -->
					<div class="btn-group-vertical" role="group" aria-label="...">
						<button type="button" class="btn btn-primary" ng-show="jobServiceActionModel.serviceStatus == 'FAILED' || jobServiceActionModel.serviceStatus == 'DONE'">Download log file</button>
        				</div>
        				
        				<!-- See status -->
        				<!-- 
        				<div class="btn-group-vertical" role="group" aria-label="...">
        					<button type="button" class="btn btn-primary" ng-show="jobServiceActionModel.serviceStatus == 'RUNNING' || jobServiceActionModel.serviceStatus == 'WAITING'" ng-click="getJobStatus(jobServiceActionModel)">See live status</button>
        				</div>
        				 -->
        				
                </div>        
          	</div>
      
    		</div>
  	</div>
  	
  	<!-- Modal notification -->
  	<div class="modal fade" id="modalSuccess" role="dialog">
    		<div class="modal-dialog modal-sm">
    
            <!-- Modal content-->
          	<div class="modal-content">
          	
            		<div class="modal-header">
              		<button type="button" class="close" data-dismiss="modal">&times;</button>
              		<h4 class="modal-title" style="text-align: center">
              			<!-- success/failed -->
              			<b>{{modalNotification.header}}</b>
              		</h4>
            		</div>
            		
                <div class="modal-body" style="text-align: center">
                    <!-- some message related to success/failed -->
					<p>{{modalNotification.message}}</p>
					<div ng-show="modalNotification.header=='Error'">						
						<a href="http://rest.sinapad.lncc.br:8080/rest/codes.jsp" target="_blank">SINAPAD REST error codes</a>
					</div>
                </div>        
          	</div>
      
    		</div>
  	</div>
  	
  	<!-- Modal notification -->
  	<div class="modal fade" id="modalStatusInfo" role="dialog">
    		<div class="modal-dialog modal-sm">
    
            <!-- Modal content-->
          	<div class="modal-content">
          	
            		<div class="modal-header">
              		<button type="button" class="close" data-dismiss="modal">&times;</button>
              		<h4 class="modal-title" style="text-align: center">
              			<b>Job status icons</b>
              		</h4>
            		</div>
            		
                <div class="modal-body" style="text-align: center">
                    
                    <p> 
                    		DONE 
                    		<span class="glyphicon glyphicon-ok"></span> 
                    	</p>
                    	
                    	<p> 
                    		CANCELLED
                        	<span class="glyphicon glyphicon-ban-circle"></span>
                    	</p>
                    	
                    	<p>
                    		FAILED
                    		<span style="color: red" class="glyphicon glyphicon-remove-circle"></span>
					</p>
                    
                    <p>
                    		RUNNING
                    		<i class="fa fa-cog fa-spin" style="font-size:22px;"></i>
					</p>
					
					<p>
                    		WAITING
                    		<span style="color: red" class="glyphicon glyphicon-time"></span>
					</p>
					
					<p>
                    		N/A
                    		<span class="glyphicon glyphicon-question-sign"></span>
					</p>
                    
                </div>        
          	</div>
      
    		</div>
  	</div>
	
</div>
