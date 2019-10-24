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
    $schema_folder_location = __DIR__ . '/../Schema/';
    $output_information['openapi'] = '3.0.0';
    $output_information['info']= json_decode(file_get_contents($schema_folder_location . 'info.json'));
    $output_information['paths']['/site'] = json_decode(file_get_contents($schema_folder_location . 'site_list.json'));
    $output_information['paths']['/site/{resource_id}'] = json_decode(file_get_contents($schema_folder_location . 'site_record.json'));
    $output_information['paths']['/dataset'] = json_decode(file_get_contents($schema_folder_location . 'dataset_list.json'));
    $output_information['paths']['/dataset/{resource_id}'] = json_decode(file_get_contents($schema_folder_location . 'dataset_record.json'));
    $output_information['paths']['/activity'] = json_decode(file_get_contents($schema_folder_location . 'activity_list.json'));
    $output_information['paths']['/activity/{resource_id}'] = json_decode(file_get_contents($schema_folder_location . 'activity_record.json'));
    $output_information['paths']['/sensor'] = json_decode(file_get_contents($schema_folder_location . 'sensor_list.json'));
    $output_information['paths']['/sensor/{resource_id}'] = json_decode(file_get_contents($schema_folder_location . 'sensor_record.json'));

  
    // resource not found error message
    $output_information['components']['schemas']['resourceNotFound'] = json_decode(file_get_contents(__DIR__ . '/../Schema/resource_not_found.json'));
 
    // components for site list
    $output_information['components']['schemas']['siteList']['type'] = "object";
    $output_information['components']['schemas']['siteList']['properties']['name']['type'] = "string";
    $output_information['components']['schemas']['siteList']['properties']['deimsid']['type'] = "object";
    $output_information['components']['schemas']['siteList']['properties']['deimsid']['properties']['prefix']['type'] = "string";
    $output_information['components']['schemas']['siteList']['properties']['deimsid']['properties']['id']['type'] = "string";
    $output_information['components']['schemas']['siteList']['properties']['coordinates']['type'] = "string";
    $output_information['components']['schemas']['siteList']['properties']['changed']['type'] = "string";

    $output_information['components']['schemas']['siteList']['properties']['affiliation']['type'] = "array";
    $output_information['components']['schemas']['siteList']['properties']['affiliation']['items']['$ref'] = '#/components/schemas/affiliationItem';

    $output_information['components']['schemas']['affiliationItem']['type'] = "object";
    $output_information['components']['schemas']['affiliationItem']['properties']['network']['type'] = "string";
    $output_information['components']['schemas']['affiliationItem']['properties']['code']['type'] = "string";
    $output_information['components']['schemas']['affiliationItem']['properties']['verified']['type'] = "boolean";


    // other record lists
    $output_information['components']['schemas']['recordList']['type'] = "object";
    $output_information['components']['schemas']['recordList']['properties']['name']['type'] = "string";
    $output_information['components']['schemas']['recordList']['properties']['path']['type'] = "object";
    $output_information['components']['schemas']['recordList']['properties']['path']['properties']['prefix']['type'] = "string";
    $output_information['components']['schemas']['recordList']['properties']['path']['properties']['id']['type'] = "string";
    $output_information['components']['schemas']['recordList']['properties']['changed']['type'] = "string";

    // TO DO: record components
    // Site
    //$output_information['components']['schemas']['SiteRecord'] = json_decode(file_get_contents(__DIR__ . '/../Schema/site_record.json'));

    // Dataset
    // Sensor
    // Activity

    return new JsonResponse($output_information);
  }
  
}