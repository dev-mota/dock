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


// Code for example: parser/cube-protein

// stage.loadFile("data://3ek3.cif", { defaultRepresentation: true })
stage.loadFile("../../testFiles/1caq_prep.pdb", { defaultRepresentation: true })

stage.loadFile("data://3ek3-2fofc.cub").then(function (o) {
  o.addRepresentation("surface", { opacity: 0.6 })
  o.autoView()
})