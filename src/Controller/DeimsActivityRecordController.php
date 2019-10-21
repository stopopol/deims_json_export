<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsActivityRecordController extends ControllerBase {

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
	$activity_information = [];

	// needs to be a loop due to array structure of the loadByProperties result
	if (!empty($nodes)) {
		foreach ($nodes as $node) {
			if ($node->bundle() == 'activity' && $node->isPublished()) {
				$activity_information = DeimsActivityRecordController::parseActivityFields($node);
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/activity/{uuid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no activity with the given ID :(';
		$activity_information['errors'] = $error_message;
	}
    return $activity_information;
  }
  
  public function parseActivityFields($node) {
		$activity_information = [];
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$activity_information['general']['name'] = $node->get('title')->value;
		$activity_information['general']['uuid'] = $node->get('uuid')->value;

		
		return $activity_information;
		
  }
}