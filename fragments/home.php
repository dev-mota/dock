<div class="page-container">
	<?php if(isset($_GET['PDF']) && $_GET['PDF']==true){ ?>
		<!-- <div class="alert alert-success" role="alert"> -->
		<div id="pdfdiv" class="row">
			<div class="col-md-4" >
			</div>
			<div class="col-md-4">
				<div class="alert alert-success alert-dismissable fade in" style="width:250px" align="center">
					<a href="index.php" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					File sent successfully! 
				</div>
			</div>
			<div class="col-md-4">
			</div>
		</div>
	<?php }?>
	
	<!--	
  	<div id="warning_div" class="row">   
  		<div class="col-md-12"> 
 			 <div class="alert alert-warning alert-dismissable fade in" align="center"> 
  				<a href="index.php" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
 				<b>Attention</b>: Virtual Screening submissions will be temporarily unavailable on <b>February 02-22, 2019</b>. Sorry for the inconvenience. 
 			</div>  
 		</div> 
 	</div>
	-->
		
	<!--
	<div id="warning_div" class="row">   
  		<div class="col-md-12"> 
 			 <div class="alert alert-warning alert-dismissable fade in" align="center"> 
  				<a href="index.php" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
 				<b>Attention</b>: Virtual Screening submissions will be temporarily unavailable due to technical problems. Sorry for the inconvenience. 
 			</div>  
 		</div> 
 	</div>
	-->

<!--	
	<div id="warning_div" class="row">   
		<div class="col-md-12"> 
				<div class="alert alert-warning alert-dismissable fade in" align="center"> 
				   <a href="index.php" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
				   <b>Attention:</b> Docking submissions may be temporarily unavailable until May 30, 2020. Sorry for the inconvenience.
				</div>  
		</div> 
	</div>
-->	
	
	<div id="warning_div" class="row">   
		<div class="col-md-12"> 
			<div class="alert alert-info" align="center"> 			   
			   <b><u>COVID-19:</u></b> We provide to the DockThor users structures of COVID-19 <b>potential targets</b> already prepared for docking at the Protein tab. New targets and structures will be available soon. <br><br>
				Guedes, I. A. et al. <i>Drug design and repurposing with DockThor-VS web server focusing on SARS-CoV-2 therapeutic targets and their non-synonym variants.</i> <b>Sci Rep</b> 11, 5543 (2021).
			</div>  
		</div> 
	</div>
	
<!--
	<div id="warning_div" class="row">   
		<div class="col-md-12"> 
				 <div class="alert alert-warning alert-dismissable fade in" align="center"> 
						<a href="index.php" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
						<b>Attention</b>: Docking submissions might be temporarily unavailable on <b>March 05-12, 2021</b>. Sorry for the incon$
				</div>  
		</div> 
	</div>
-->

<!--	
	<div id="warning_div" class="row">   
		<div class="col-md-12"> 
			<div class="alert alert-warning" align="center"> 			   
			   Some website features may be temporarily unavailable until <b>May 15, 2020</b> due to a system upgrade and development of new features. Docking submissions may be temporarily unavailable. Sorry for the incovenience.
			</div>  
		</div> 
	</div>
-->	
	<div align="center">
		<h1 class="welcome-title">Welcome to DockThor</h1>
		<h3 class="welcome-subtitle">A Free Web Server for Protein-ligand Docking</h3>
		<img class="img-responsive welcome-image" alt="Home picture" src="./images/dockthor_portal_4.png">
	</div>
	
	<br>
	<hr class="hr-line">

	<div class="row welcome-section" align="center">
		   <div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Protein</h2>
		     <p>Add missing hydrogen atoms, complete side chains, change protonation states. Simple and easy!</p>
		  </div><div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Small molecules</h2>
		     <p>Add hydrogen atoms (pH 7), freeze rotatable bonds, get MMFF94S atom types and partial charges. Fast and automatic!</p>
		  </div>
		  <div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Cofactors</h2>
		     <p>Consider cofactors and structural waters on virtual screening experiments with automatic MMFF94S parametrization.</p>
		  </div>
	</div>
	
	<div class="row welcome-section" align="center">
		  <div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Redocking</h2>
		     <p>Validate docking protocol with redocking experiments. We provide the RMSD between reference and docked poses.</p>
		  </div>
		  <div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Blind Docking</h2>
		     <p>Searching for binding sites? Perform blind docking on the entire protein and find cavities!</p>
		  </div>
		  <div class="col-lg-4">
		     <img class="rounded-circle" alt="Generic placeholder image" src="./images/logo_dockthor.png" width="80" height="90">
		     <h2>Virtual Screening</h2>
		     <p>Perform large scale docking experiments exploring multiple binding modes. Dock them all!</p>
		  </div>
	</div>
	
	<br>
	<hr class="hr-line">
	
	<div class="row featurette">
        <div class="col-md-7">
          <h2 class="featurette-heading" style="margin-top: 10%;">Interactive Analyses. <span class="text-muted">Explore docking poses and predict affinity.</span></h2>
          <p class="lead">Investigate different binding modes and predict binding affinities. Visualize predicted complexes with JSMol.</p>
        </div>
        <div class="col-md-5">
          <img class="welcome-section-image" alt="500x500" src="./images/index/results_all.jpg">
        </div>
    </div>
    
    <br>
	<hr class="hr-line">
	
	<div class="row featurette">
	    <div class="col-md-5">
          <img class="welcome-section-image" alt="500x500" src="./images/index/sdumont.jpg">
        </div>
        <div class="col-md-7">
          <h2 class="featurette-heading" style="margin-top: 10%;">SDumont Supercomputer. <span class="text-muted">Virtual screening experiments even faster.</span></h2>
          <p class="lead">Run virtual screening experiments at Santos Dumont supercomputer, located at LNCC, Petrópolis - Brazil.</p>
        </div>
    </div>
</div>
<script>
<!--
	
//-->
$(document).ready(function(){
	//setTimeout("$('#pdfdiv').hide().fadeOut()",2000);
	setTimeout("$('#pdfdiv').fadeOut(2000)",2000);
});
</script>
