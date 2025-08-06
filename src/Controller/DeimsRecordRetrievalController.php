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
					
					$url_parameters = array_change_key_case(\Drupal::request()->query->all(), CASE_LOWER);
					$format = array_key_exists('format', $url_parameters) ? $url_parameters['format']: null;
						
					if ($format == "iso19139" && $content_type == "site") {
						$DEIMSIso19139Controller = new $DEIMSIso19139Controller();
						$Iso19139Response = $DeimsIso19139Controller->Iso19139Response();
						$response = new Response(Iso19139Response);
						$response->headers->set('Content-Type', 'application/xml');
  						return $response;
					}
				}
			}
		}
		else {
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
			
			$DeimsErrorMessageController = new DeimsErrorMessageController();
			return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(404, "/api/{$content_type_label}/{$uuid}", "There is no {$content_type} with the ID {$uuid} :("));
		
		}

		return new JsonResponse($record_information);
	}

}




