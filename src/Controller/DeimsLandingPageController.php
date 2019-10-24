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
    //$output_information['servers'] = array(array("description"=>"Official DEIMS-SDR API","url"=>"https://deims.org/api"));
    $definition_location = __DIR__ . '/../Custom/Path/';
    $output_information['openapi'] = '3.0.0';
    $output_information['info']= json_decode(file_get_contents($definition_location . 'info.json'));
    $output_information['paths']['/site'] = json_decode(file_get_contents($definition_location . 'site_list.json'));
    $output_information['paths']['/site/{resource_id}'] = json_decode(file_get_contents($definition_location . 'site_record.json'));
    $output_information['paths']['/dataset'] = json_decode(file_get_contents($definition_location . 'dataset_list.json'));
    $output_information['paths']['/dataset/{resource_id}'] = json_decode(file_get_contents($definition_location . 'dataset_record.json'));
    $output_information['paths']['/activity'] = json_decode(file_get_contents($definition_location . 'activity_list.json'));
    $output_information['paths']['/activity/{resource_id}'] = json_decode(file_get_contents($definition_location . 'activity_record.json'));
    $output_information['paths']['/sensor'] = json_decode(file_get_contents($definition_location . 'sensor_list.json'));
    $output_information['paths']['/sensor/{resource_id}'] = json_decode(file_get_contents($definition_location . 'sensor_record.json'));

    $definition_location = __DIR__ . '/../Custom/Component/';
    $output_information['components']['schemas']['resourceNotFound'] = json_decode(file_get_contents($definition_location . '/resource_not_found.json'));
    $output_information['components']['schemas']['siteList'] = json_decode(file_get_contents($definition_location . '/siteList.json'));
    $output_information['components']['schemas']['affiliationItem'] = json_decode(file_get_contents($definition_location . '/affiliationItem.json'));
    $output_information['components']['schemas']['recordList'] = json_decode(file_get_contents($definition_location . '/recordList.json'));


    // TO DO: record components
    // Site
    // Dataset
    // Sensor
    // Activity

    return new JsonResponse($output_information);
  }
  
}