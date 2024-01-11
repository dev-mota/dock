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


// Code for example: component/structure-biomolSimple

stage.loadFile("data://1U19.cif").then(function (o) {
  o.addRepresentation("licorice")
  o.addRepresentation("cartoon", {
    assembly: "BU1", color: 0xFF1111
  })
  o.addRepresentation("cartoon", {
    assembly: "BU2", color: 0x11FF11
  })
  o.autoView()
})
