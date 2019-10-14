<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsLandingPageController extends ControllerBase {

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
	  	  
	$output_information = [];

	$output_information['version'] = '0.1';
    $output_information['about'] = 'https://deims.org/about';
    $output_information['terms'] = 'https://deims.org/terms';
    $output_information['resources'] = array("site","dataset","activity","sensor");
        
	
    return $output_information;
  }
  
}