<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/*
 * Controlls all error messages of api
 *
 * path input parameter
 */
class DeimsRecordRetrievalController extends ControllerBase {
	
	public function record_retrieval($uuid, $path_parameter) {
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
		$record_information = [];

		// needs to be a loop due to array structure of the loadByProperties result
		if (!empty($nodes)) {
			foreach ($nodes as $node) {
				if ($node->bundle() == $path_parameter && $node->isPublished()) {
					switch ($path_parameter) {
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
			return $record_information;
		}
		else {
			$error_message = [];
			$error_message['status'] = "404";
			$error_message['source'] = ["pointer" => '/api/' . $path_parameter . '/{id}'];
			$error_message['title'] = 'Resource not found';
			$error_message['detail'] = 'There is no ' . $path_parameter . ' with the given ID :(';
			$record_information['errors'] = $error_message;
		}
	}
	
}