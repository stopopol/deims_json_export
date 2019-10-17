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

	  $output_information['title'] = 'DEIMS-SDR API';
	  $output_information['description'] = 'This is an API for automated data export from the site and dataset registry DEIMS-SDR';
    $output_information['termsOfService'] = 'https://deims.org/terms';
    $output_information['contact']['name'] = 'DEIMS-SDR Support';
    $output_information['contact']['url'] = 'https://deims.org/contact';
    $output_information['license']['name'] = 'CC-BY-NC International 4.0';
    $output_information['license']['url'] = 'https://creativecommons.org/licenses/by-nc/4.0/';
    $output_information['version'] = '0.1';
        
    return $output_information;
  }
  
}