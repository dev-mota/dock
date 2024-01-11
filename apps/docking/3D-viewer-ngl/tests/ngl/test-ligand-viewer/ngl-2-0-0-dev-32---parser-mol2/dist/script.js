// Setup to load data from rawgit
NGL.DatasourceRegistry.add(
    "data", new NGL.StaticDatasource( "//cdn.rawgit.com/arose/ngl/v2.0.0-dev.32/data/" )
);

// Create NGL Stage object
var stage = new NGL.Stage( "viewport" );

// Handle window resizing
window.addEventListener( "resize", function( event ){
    stage.handleResize();
}, false );

stage.setParameters({
	  backgroundColor: "white"
})
	
// Code for example: parser/mol2

//stage.loadFile("data://adrenalin.mol2").then(function (o) {
//stage.loadFile("../testFiles/ligand_7e08e900ab_1.sdf").then(function (o) {
//stage.loadFile("../testFiles/ligand_7e08e900ab.pdb").then(function (o) {
// stage.loadFile("../testFiles/new_ligand_7e08e900ab_1.sdf").then(function (o) {
stage.loadFile("../testFiles/test_zinc.mol2").then(function (o) {
  o.addRepresentation("ball+stick", { multipleBond: "symmetric" })
  o.autoView()
})