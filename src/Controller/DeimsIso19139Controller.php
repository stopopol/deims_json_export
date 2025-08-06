<?php

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

class DEIMSIso19139Controller extends ControllerBase {

  public function Iso19139Response($record_information ) {
    $data = [
      'title' => $record_information["title"],
      'date' => date('c'),
      'items' => [
        ['id' => 1, 'name' => 'Item One'],
        ['id' => 2, 'name' => 'Item Two'],
      ],
    ];

    // Generate XML string
    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
          <gmd:MD_Metadata xmlns:gmd="http://www.isotc211.org/2005/gmd"
          xmlns:gco="http://www.isotc211.org/2005/gco"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xmlns:gml="http://www.opengis.net/gml"
          xsi:schemaLocation="http://www.isotc211.org/2005/gmd
          http://schemas.opengis.net/iso/19139/20070417/gmd/gmd.xsd">'
    );
    
    $xml->addChild('title', $data['title']);
    $xml->addChild('date', $data['date']);

    $itemsNode = $xml->addChild('items');
    foreach ($data['items'] as $item) {
      $itemNode = $itemsNode->addChild('item');
      $itemNode->addChild('id', $item['id']);
      $itemNode->addChild('name', $item['name']);
    }

    $response = new Response($xml->asXML());
    $response->headers->set('Content-Type', 'application/xml');
    return $response;
  }

}
