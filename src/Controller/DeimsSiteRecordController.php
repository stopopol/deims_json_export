<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * Implementing our example JSON api.
 */
class DeimsSiteRecordController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function renderApi($deimsid) {
	return new JsonResponse($this->getResults($deimsid));
  }

  /**
   * A helper function returning results.
   */
  public function getResults($deimsid) {
	  	  
	$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $deimsid]);
	$site_information = [];

	// needs to be a loop due to array structure of the loadByProperties result
	if (!empty($nodes)) {
		foreach ($nodes as $node) {
			
			if ($node->bundle() == 'site' && $node->isPublished()) {
		
				$site_information['deimsid'] = $deimsid;
				$site_information['name'] = $node->get('field_name')->value;
				$site_information['short_name'] = $node->get('field_name_short')->value;
				$site_information['site_status'] = $node->get('field_site_status')->value;
				$site_information['coordinates'] = $node->get('field_coordinates')->value;
				$site_information['boundaries'] = $node->get('field_boundaries')->value;
				$site_information['purpose'] = $node->get('field_purpose')->value;
				$site_information['biome'] = $node->get('field_biome')->value;
				// DeimsSiteParagraphFieldController::parseAffiliation is located in different controller file, but doesn't have to be included in order to be called
				$site_information['affiliation'] = DeimsSiteParagraphFieldController::parseAffiliation($node);
					
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/site/{deimsid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no site with the given DEIMS.ID :(';
		$site_information["errors "] = $error_message;
	}
    return $site_information;
  }

}