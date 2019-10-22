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
  public function renderLandingPage() {
    
    // https://app.swaggerhub.com/apis/klimeto/RPI2.0/0.0.1
    $output_information['servers'] = array(array("description"=>"Official DEIMS-SDR API","url"=>"https://deims.org/api"));
	  $output_information['info']['title'] = 'DEIMS-SDR API';
	  $output_information['info']['description'] = 'This is an API for automated data export from the site and dataset registry DEIMS-SDR';
    $output_information['info']['termsOfService'] = 'https://deims.org/terms';
    $output_information['info']['contact']['name'] = 'DEIMS-SDR Support';
    $output_information['info']['contact']['url'] = 'https://deims.org/contact';
    $output_information['info']['license']['name'] = 'CC-BY-NC International 4.0';
    $output_information['info']['license']['url'] = 'https://creativecommons.org/licenses/by-nc/4.0/';
    $output_information['info']['version'] = '0.1';
    $output_information['tags'] = array("sites","datasets","sensors","activities");
    
    return new JsonResponse($output_information);
  }
  
}