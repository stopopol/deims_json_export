<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsSensorRecordController extends ControllerBase {

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
	$sensor_information = [];

	// needs to be a loop due to array structure of the loadByProperties result
	if (!empty($nodes)) {
		foreach ($nodes as $node) {
			if ($node->bundle() == 'sensor' && $node->isPublished()) {
				$sensor_information = DeimsSensorRecordController::parseSensorFields($node);
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/sensor/{uuid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no sensor with the given ID :(';
		$sensor_information['errors'] = $error_message;
	}
    return $sensor_information;
  }
  
  public function parseSensorFields($node) {
		$activity_information = [];
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$sensor_information['general']['name'] = $node->get('title')->value;
		$sensor_information['general']['uuid'] = $node->get('uuid')->value;

		
		return $sensor_information;
		
  }
}