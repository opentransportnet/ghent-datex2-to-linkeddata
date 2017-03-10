<?php
/**
 * For usage instructions, see README.md
 *
 * @author Pieter Colpaert <pieter.colpaert@ugent.be>
 */

namespace otn\linkeddatex2;

Class Metadata
{
    public static function addToGraph($graph){
        // Hydra is a template for the Triple Pattern Fragments specification
        // (https://www.hydra-cg.com/spec/latest/triple-pattern-fragments/),
        // which will allow you to use this document as a queryable resource in the Linked Data Fragments client
        // (http://client.linkeddatafragments.org/)
        \EasyRdf_Namespace::set("hydra","http://www.w3.org/ns/hydra/core#");

        // Define a resource for the dataset
        $dataset = $graph->resource("http://linked.open.gent/parking/#dataset");

        // Define a resource for the document
        $document = $graph->resource("http://linked.open.gent/parking/");

        // Amount of triples is 200?? (set vs. add: set overwrites the property)
        $document->set('void:triples', "200");

        // Define a resource for the search operation and link it: dataset-hydra:search-search
        $search = $graph->resource("http://linked.open.gent/parking/#search");
        $dataset->add("hydra:search",$search);

        // RDF link: search-hydra:template-literal document url (??)
        $search->set("hydra:template", "http://linked.open.gent/parking/");

        // Define S-P-O mappings using hydra
        $mappingS = $graph->resource("http://linked.open.gent/parking/#mappingS");
        $mappingS->set("hydra:variable","s");
        $mappingS->add("hydra:property",$graph->resource("rdf:subject"));

        $mappingP = $graph->resource("http://linked.open.gent/parking/#mappingP");
        $mappingP->set("hydra:variable","p");
        $mappingP->add("hydra:property",$graph->resource("rdf:predicate"));

        $mappingO = $graph->resource("http://linked.open.gent/parking/#mappingO");
        $mappingO->set("hydra:variable","o");
        $mappingO->add("hydra:property",$graph->resource("rdf:object"));

        $search->add("hydra:mapping",$mappingS);
        $search->add("hydra:mapping",$mappingP);
        $search->add("hydra:mapping",$mappingO);

        //add a license for legal interoperability
        $document = $graph->resource("http://linked.open.gent/parking");
        $document->set("rdfs:label", "Dynamic parking data in Ghent");
        $document->set("rdfs:comment", "This document is a mapping from the Datex2 by Pieter Colpaert as part of the Open Transport Net project");
        $document->add("foaf:homepage", $graph->resource("https://github.com/opentransportnet/ghent-datex2-to-linkeddata"));
        $document->add("cc:license",$graph->resource("https://data.stad.gent/algemene-licentie"));
    }
}
