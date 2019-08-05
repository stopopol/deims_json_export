<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsDatasetRecordController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function renderApi($uuid) {
	return new JsonResponse($this->getResults($uuid));
  }

  /**
   * A helper function returning results.
   */
  public function getResults($uuid) {
	  	  
	$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
	$dataset_information = [];

	// needs to be a loop due to array structure of the loadByProperties result
	if (!empty($nodes)) {
		foreach ($nodes as $node) {
			if ($node->bundle() == 'dataset' && $node->isPublished()) {
				$dataset_information = DeimsDatasetRecordController::parseSiteFields($node);
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/dataset/{uuid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no site with the given ID :(';
		$dataset_information['errors'] = $error_message;
	}
    return $dataset_information;
  }
  
  public function parseSiteFields($node) {
		$dataset_information = [];
		
		$dataset_information['name'] = $node->get('title')->value;
		$dataset_information['uuid'] = $node->get('uuid')->value;
		$dataset_information['abstract'] = (!is_null($node->get('field_abstract')->value)) ? ($node->get('field_abstract')->value) : null; 
		
		return $dataset_information;
  }

}