<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This controller lists all sites on DEIMS-SDR in a JSON object including a selected subset of information
 */
class DeimsDatasetListController {

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
	
	$dataset_list = [];
	
	$nids = \Drupal::entityQuery('node')->condition('type','dataset')->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
	
	
	foreach ($nodes as $node) {
		
		if ($node->isPublished()) {
			
			$dataset_information = [];
			$dataset_information['name'] = $node->get('title')->value;
			$dataset_information['uuid'] = $node->get('uuid')->value;
			
			array_push($dataset_list, $dataset_information);
		}
	} 
	
    return $dataset_list;
  }

}