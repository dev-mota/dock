<div class="page-container">
  <div class="row">
    <div class="col-md-12 text-justify">
      <h2>New Features</h2>
      <br>
      
      <h3 class="subtitle">General</h3>
        <br>
        <ul>
          <li> <i>04/27/2020 - </i>Three-dimensional structures now are visualized with the NGL Viewer.</li>
          <li> <i>04/10/2020 - </i>New redocking Tutorial with new test files available (download the tutorial at <b>Support -> Help</b>).</li>
          <li> <i>01/24/2020 - </i>Download of the curated LEADS-PEP data set is available in the References tab.</li>
        </ul>
      
      <h3 class="subtitle">Protein</h3>
      <ul>
        <br>
        <li> <i>06/08/2020 - </i>Curated structures of relevant therapeutic targets of SARS-CoV-2 are now available. The dataset will be
        constantly updated with new structures (wild type and selected mutations).</li>
        <li> <i>04/27/2020 - </i>Neutral cisteine (CYSH) set as the default protonation state.</li>
        <li>
          Hydrogen atoms added to the protein with external tools (<i>e.g.</i> Maestro, Protoss, PDB2PQR) are automatically recognized and the residues protonation states are identified. Residues protonation states could also be modified later.
        </li>
        <li>
          Download options:
          <ul>
          	<li><i>Prepared file (.pdb)</i> containing the prepared protein file with the final protonation states and polar hydrogen atoms added.</li>
          	<li><i>Topology file(s) (.in)</i> for the uploaded target protein.</li>
          	<li><i>Compacted folder (.zip)</i> with all input and output files.</li>
          </ul>
        </li>
        <li>Bug fixes.</li>
      </ul>

      <br>
      <h3 class="subtitle">Cofactors</h3>
      <br>
      <ul>
        <li>
          Upload up to 10 cofactor files.
        </li>
        <li>MOL2 and SDF format files are now accepted in addition to the PDB and TOP types.</li>
        <li>Download options:
            <ul>
                <li><i>Map file (.csv)</i> containing the relationship between the original file names and random IDs provided by DockThor.</li>
                <li><i>Topology file(s) (.top)</i> for the uploaded compounds.</li>
                <li><i>Compacted folder (.zip)</i> with all input and output files.</li>
            </ul>
        </li>
        <li>Bug fixes.</li>
      </ul>

      <br>
      <h3 class="subtitle">Ligands</h3>
      <br>
      <ul>
        <li> <i>08/12/2020 - </i>Curated datasets for drug repurposing at reference pH are now available! New datasets will be available soon.</li>
        <li><i>08/12/2020 - </i>Virtual screening with up to <b>200</b> small molecules as <u>guest user</u>.</li>
        <li><i>05/22/2020 - </i> Virtual screening now with up to <b>5000</b> small molecules as <u>registered user</u>.</li>
        <li><i>01/17/2020 - </i> Dock highly flexible ligands such as peptides with up to <b>40 residues</b> and <b>60 dihedrals</b>.</li>
        <li>Upload single or multiple structures file (only available for MOL2 and SDF
files).</li>
        <li> Compounds are filtered to remove invalid structures:</li>
            <ul>
                <li>Molecular weight > 1500 Da;</li>
                <li>Rotatable bonds > 60;</li>
                <li>2D structure;</li>
                <li>At least one atom type not recognized by the MMFF94S force field.</li>
            </ul>
        <li>Recovery the original file names with the table <i>mapfile.csv</i>. </li>
        <li>Enable/disable all rotatable bonds with just one click. </li>
        <li>Calculate compound properties with Obprop/OpenBabel (provided in table <i>obprop.csv</i>).</li>
        <li>Download options:</li>
            <ul>
                <li><i>Map file (.csv)</i> containing the relationship between the original file names and random IDs provided by DockThor.</li>
                <li><i>Properties table (s) (.csv)</i> containing the compounds properties calculated by Obprop/OpenBabel.</li>
                <li><i>Topology file(s) (.top)</i> for the uploaded compounds.</li>
                <li><i>Compacted folder (.zip)</i> with all input and output files.</li>
            </ul>
        <li>Bug fixes. </li>
        </ul>
    
        <br>
        <h3 class="subtitle">Docking</h3>
        <br>
        <ul>
          <li><i>04/27/2020 - </i> The input grid size on each dimension corresponds now to the total size of the grid box on each axis (instead of the half value).</li>
          <li><i>04/10/2020 - Blind docking</i> option that centers the energy grid at the center of mass of the protein and defines the grid dimensions to cover the entire protein (or the maximum size allowed). <b>Attention:</b> experimental, use it with caution.</li>
          <li><i>01/24/2020 - </i> Softening the MMFF94S Buf-14-7 potential now available (buffering constant = 0.35).</li>
          <li>Faster virtual screening with the <b>Santos Dumont supercomputer</b>:
              <ul>
                  <li><u>Minutes</u> for screening up to <b>200</b> compounds;</li>
                  <li><u>Few hours</u> for screening up to <b>5000</b> molecules.</li>
              </ul>
          <li>Summary of uploaded files.</li>
          <li>Iterative configuration of the grid integrated with the protein visualization.</li>
          <li>Pre-defined configurations of the search algorithm:</li>
              <ul>
                  <li><i>Standard</i>: optimized configuration explored through in-house benchmarking studies.</li>
                  <li><i>Virtual screening</i>: faster protocol for screening compound databases with good accuracy.</li>
                  <li><i>Explorer</i>: change the parameters as you wish (only available for docking with up to 100 compounds).</li>
              </ul>
          <li>Receive the e-mail with the results in up to five addresses.</li>
          <li>Subscribe DockThor e-Newsletters to receive DockThor news.</li>
          <li>Job name simplified to <i>label + random ID</i>.</li>
          <li>An e-mail is sent when your job is submitted and a second one when the job is finished.</li>
        </ul>
    
        <br>
        <h3 class="subtitle">Analyses</h3>
        <br>
        <ul>
            <li> <i>04/27/2020 - </i>Number of top-energy binding modes are limited to 3 for virtual screening and 10 for single docking experiments.</li>
            <li> <i>04/10/2020 - </i>Corrected the protein file *_prep.pdb in the docking compacted folder (.zip file) at the directory PROTEIN/.</li>
            <li>Predict binding affinity with a new empirical scoring function composed by terms accounting for intermolecular interactions, ligand entropy, desolvation and lipophilic contacts.</li>
            <li>Explore multiple binding modes even in virtual screening experiments.</li>
            <li>Download your docking results:</li>
                <ul>
                    <li>Ranked compounds according to the affinity prediction (<i>bestranking files</i>).</li>
                    <li>Top-ranked binding modes for each compound after clustering (<i>result- files</i>).</li>
                </ul>
            <li>Explore different clustering parameters when docking a single ligand.</li>
        </ul>
    </div>
  </div>
</div>
