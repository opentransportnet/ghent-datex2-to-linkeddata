<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

Class GhentToRDF
{
    public static function map($url, &$graph){
        // \EasyRdf_Namespace::set registers a new prefix
        \EasyRdf_Namespace::set("datex","http://vocab.datex.org/terms#");
        \EasyRdf_Namespace::set("schema","http://schema.org/"); // TODO Is this not used?
        \EasyRdf_Namespace::set("dct","http://purl.org/dc/terms/"); // TODO Why is this renamed to dc?
        \EasyRdf_Namespace::set("geo","http://www.w3.org/2003/01/geo/wgs84_pos#"); // TODO Is this not used?

        // Map parking ID's to their URI's
        $parkingURIs = [
            "1bcd7c6f-563b-4c07-803d-a2ad05014c9f" => "https://stad.gent/id/parking/P7",
            "a13c076c-4088-4623-bfcb-41ab45cb8f9f" => "https://stad.gent/id/parking/P10",
            "ac864c7c-5bf0-495a-a92f-2c3c4fcd834d" => "https://stad.gent/id/parking/P1",
            "0c225a81-204f-4c7c-9eda-14b297967c38" => "https://stad.gent/id/parking/P4",
            "49334d1d-b47a-4f3b-a0af-0fa1bcdc7c8e" => "https://stad.gent/id/parking/P8",
            "83f2b0c2-6e74-4700-a862-3bc9cd6a03f4" => "https://stad.gent/id/parking/P2"
        ];

        // Initialize the graph if necessary. The URI in the argument serves as name.
        if (!isset($graph)) {
            $graph = new \EasyRdf_Graph("http://linked.open.gent/parking");
        }

        // mapping to City of Ghent URIs to Linked Geo Data
        $sameAs = [
            "https://stad.gent/id/parking/P10" => "http://linkedgeodata.org/triplify/node204735155", #GSP
            "https://stad.gent/id/parking/P7" => "http://linkedgeodata.org/triplify/node310469809", #SM
            "https://stad.gent/id/parking/P1" => "http://linkedgeodata.org/triplify/node2547503851", #vrijdagmarkt
            "https://stad.gent/id/parking/P4" =>"http://linkedgeodata.org/triplify/node346358328", #savaanstraat
            "https://stad.gent/id/parking/P8" => "http://linkedgeodata.org/triplify/node497394185", #Ramen
            "https://stad.gent/id/parking/P2" => "http://linkedgeodata.org/triplify/node1310104245", #Reep
        ];

        // Add the first triplet for each parking subject: its geodata node.
        foreach ($sameAs as $key => $val) {
            $gentID = $graph->resource($key);
            $gentID->add("owl:sameAs",$graph->resource($val));
        }

        // Send a GET request to the URL in the argument, expecting an XML file in return
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $xmldoc = new \SimpleXMLElement($res->getBody());

        //Process Parking Status messages (dynamic)
        if ($xmldoc->payloadPublication->genericPublicationExtension->parkingStatusPublication) {
            foreach ($xmldoc->payloadPublication->genericPublicationExtension->parkingStatusPublication->parkingRecordStatus as $parkingStatus) {
                
                $parkingResource = $graph->resource($parkingURIs[(string) $parkingStatus->parkingRecordReference["id"]]);
                //TODO: should this be reified in a Parking Record Status resource?
                $parkingResource->set('datex:parkingNumberOfVacantSpaces',$parkingStatus->parkingOccupancy->parkingNumberOfVacantSpaces);
                //TODO: should this be a resource or a data type property?
                $parkingResource->set('datex:parkingSiteStatus', $parkingStatus->parkingSiteStatus);
                $parkingResource->set('datex:parkingSiteOpeningStatus',$parkingStatus->parkingSiteOpeningStatus);
            }
        }

        //Process Parking data that does not change that often (Name, lat, long, etc. Static)
        if ($xmldoc->payloadPublication->genericPublicationExtension->parkingTablePublication) {
            foreach ($xmldoc->payloadPublication->genericPublicationExtension->parkingTablePublication->parkingTable->parkingRecord->parkingSite as $parking) {
                //var_dump($parking);
                $parkingResource = $graph->resource((string)$parkingURIs[(string) $parking["id"]]);
                $parkingResource->set('rdf:type',$graph->resource('http://vocab.datex.org/terms#UrbanParkingSite'));

                $parkingResource->set('rdfs:label',(string)$parking->parkingName->values[0]->value);
                $parkingResource->set('dct:description',(string)$parking->parkingDescription->values[0]->value);
                $parkingResource->set('datex:parkingNumberOfSpaces',$parking->parkingNumberOfSpaces);
                
            }
        }
    }
}
