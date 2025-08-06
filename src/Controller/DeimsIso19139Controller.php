<?php

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

class DEIMSIso19139Controller extends ControllerBase {

  public function Iso19139Response($record_information) {
    $title = htmlspecialchars($record_information["title"]);

    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    // Root element
    $root = $doc->createElementNS("http://www.isotc211.org/2005/gmd", "gmd:MD_Metadata");
    $doc->appendChild($root);

    // Add necessary namespaces
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:gco", "http://www.isotc211.org/2005/gco");
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:gml", "http://www.opengis.net/gml");
    $root->setAttribute("xsi:schemaLocation", "http://www.isotc211.org/2005/gmd http://schemas.opengis.net/iso/19139/20070417/gmd/gmd.xsd");

    // Add title
    $titleElem = $doc->createElement("gmd:title");
    $charStr = $doc->createElement("gco:CharacterString", $title);
    $titleElem->appendChild($charStr);
    $root->appendChild($titleElem);

    // Add date
    $dateElem = $doc->createElement("gmd:date");
    $dateVal = $doc->createElement("gco:Date", date('Y-m-d'));
    $dateElem->appendChild($dateVal);
    $root->appendChild($dateElem);

    // Add dummy items (non-standard, for demonstration)
    $itemsElem = $doc->createElement("gmd:items");
    foreach ([['id' => 1, 'name' => 'Item One'], ['id' => 2, 'name' => 'Item Two']] as $item) {
      $itemElem = $doc->createElement("gmd:item");
      $itemElem->appendChild($doc->createElement("gmd:id", $item['id']));
      $itemElem->appendChild($doc->createElement("gmd:name", htmlspecialchars($item['name'])));
      $itemsElem->appendChild($itemElem);
    }
    $root->appendChild($itemsElem);

    // Return response
    $response = new Response($doc->saveXML());
    $response->headers->set('Content-Type', 'application/xml');
    return $response;
  }

}
