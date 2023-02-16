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

		// needs to be a loop due to array structure of the loadByProperties result
		if (!empty($nodes)) {
			foreach ($nodes as $node) {
				if ($node->bundle() == $content_type && $node->isPublished()) {
					switch ($content_type) {
						case 'site':
							$DeimsRecordController = new DeimsSiteRecordController();
							break;
						case 'dataset':
							$DeimsRecordController = new DeimsDatasetRecordController();
							break;
						case 'activity':
							$DeimsRecordController = new DeimsActivityRecordController();
							break;
						case 'sensor':
							$DeimsRecordController = new DeimsSensorRecordController();
							break;
						case 'observation_location':
							$DeimsRecordController = new DeimsLocationRecordController();
							break;
						case 'network':
							$DeimsRecordController = new DeimsNetworkRecordController();
							break;
					}
					$record_information = $DeimsRecordController->parseFields($node);
				}
			}
		}
		else {
			$error_message['status'] = "404";
			switch ($content_type) {
			  case 'site':
			    $content_type_label = "sites";
			    break;
			  case 'dataset':
			    $content_type_label = "datasets";
			    break;
			  case 'activity':
			    $content_type_label = "activities";
			    break;
			  case 'sensor':
			    $content_type_label = "sensors";
			    break;
			  case 'observation_location':
			    $content_type_label = "locations";
			    break;
			  case 'network':
			    $content_type_label = "networks";
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
