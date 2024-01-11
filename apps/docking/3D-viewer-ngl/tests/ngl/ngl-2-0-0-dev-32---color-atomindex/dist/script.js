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


// Code for example: color/atomindex

stage.loadFile("../../testFiles/1caq_prep.pdb").then(function (o) {
  o.addRepresentation("ball+stick", { color: "atomindex" })
  o.autoView()
})