<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This controller lists all sites on DEIMS-SDR in a JSON object including a selected subset of information
 */
class DeimsSiteListController {

  /**
   * Callback for the API.
   */
  public function renderApi() {
    return new JsonResponse($this->getResults());
  }

  /**
   * A helper function returning results.
   */
  public function getResults() {
	
	$site_list = [];
	
	$nids = \Drupal::entityQuery('node')->condition('type','site')->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids); 
 	
	foreach ($nodes as $node) {
		
		if ($node->isPublished()) {
			
			$site_information = [];
			$site_information['name'] = $node->get('field_name')->value;
			$site_information['coordinates'] = $node->get('field_coordinates')->value;
			$site_information['deimsid'] = 'https://deims.org/' . $node->get('field_deims_id')->value;
			// DeimsSiteParagraphFieldController::parseAffiliation is located in a different controller file, but doesn't have to be included in order to be called
			$site_information['affiliation'] = DeimsSiteParagraphFieldController::parseAffiliation($node);
			
			array_push($site_list, $site_information);
		}
	} 
	
    return $site_list;
  }

}