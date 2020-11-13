<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
 
class DeimsRecordRetrievalController extends ControllerBase {

	/* 
	 * Function to call the record renderer for each content type
	 */
	public function renderRecord($uuid, $content_type) {
		
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
		$record_information = [];

		// needs to be a loop due to array structure of the loadByProperties result
		if (!empty($nodes)) {
			foreach ($nodes as $node) {
				if ($node->bundle() == $content_type && $node->isPublished()) {
					switch ($content_type) {
						case 'site':
							$DeimsSiteRecordController = new DeimsSiteRecordController();
							$record_information = $DeimsSiteRecordController->parseSiteFields($node);
							break;
						case 'dataset':
							$DeimsDatasetRecordController = new DeimsDatasetRecordController();
							$record_information = $DeimsDatasetRecordController->parseDatasetFields($node);
							break;
						case 'activity':
							$DeimsActivityRecordController = new DeimsActivityRecordController();
							$record_information = $DeimsActivityRecordController->parseActivityFields($node);
							break;
						case 'sensor':
							$DeimsSensorRecordController = new DeimsSensorRecordController();
							$record_information = $DeimsSensorRecordController->parseSensorFields($node);
							break;
					}
				}
			}
		}
		else {
			$error_message['status'] = "404";
			switch ($content_type) {
			  case site:
			    $content_type_label = "sites";
			    break;
			  case dataset:
			    $content_type_label = "datasets";
			    break;
			  case activity:
			    $content_type_label = "activities";
			    break;
			  case sensor:
			    $content_type_label = "sensors";
			    break;
			}
			$error_message['source'] = ["pointer" => '/api/' . $content_type_label . '/{id}'];
			$error_message['title'] = 'Resource not found';
			$error_message['detail'] = "There is no " . $content_type . " with the ID '" . $uuid . "' :(";
			$record_information['errors'] = $error_message;
		}

		return new JsonResponse($record_information);
	}

}
