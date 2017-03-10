<?php
require __DIR__ . '/../../vendor/autoload.php';

$graph = null;

// Map static info about parkings in Ghent to the graph
// (Name, lat, long, ID, number of spaces, opening times)
\otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/", $graph);

// Map dynamic info about parkings in Ghent to the graph
// (ID, occupancy, availability status, opening status)
\otn\linkeddatex2\GhentToRDF::map("http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkingsstatus", $graph);

// Add metadata (hydra and license)
\otn\linkeddatex2\Metadata::addToGraph($graph);

// If no preferred content type is specified, prefer turtle
if (!$_SERVER['HTTP_ACCEPT']) {
    $_SERVER['HTTP_ACCEPT'] = 'text/turtle';
}

// Set HTTP headers and serialize graph
\otn\linkeddatex2\View::view($_SERVER['HTTP_ACCEPT'],$graph);