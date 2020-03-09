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
   * renders the capabilities of the API following the OpenAPI 3.0.0. specification
   */
  public function renderLandingPage() {
    
    // https://app.swaggerhub.com/apis/klimeto/RPI2.0/0.0.1
    $output_information['openapi'] = '3.0.0';
    $output_information['info']= json_decode(file_get_contents(__DIR__ . '/../json/info.json'));
    //$output_information['servers'] = array(array("description"=>"Official DEIMS-SDR API","url"=>"https://deims.org/api"));

    $definition_location = __DIR__ . '/../json/path/';
    $output_information['paths']['/sites'] = json_decode(file_get_contents($definition_location . 'site_list.json'));
    $output_information['paths']['/sites/{resource_id}'] = json_decode(file_get_contents($definition_location . 'site_record.json'));
    $output_information['paths']['/datasets'] = json_decode(file_get_contents($definition_location . 'dataset_list.json'));
    $output_information['paths']['/datasets/{resource_id}'] = json_decode(file_get_contents($definition_location . 'dataset_record.json'));
    $output_information['paths']['/activities'] = json_decode(file_get_contents($definition_location . 'activity_list.json'));
    $output_information['paths']['/activities/{resource_id}'] = json_decode(file_get_contents($definition_location . 'activity_record.json'));
    $output_information['paths']['/sensors'] = json_decode(file_get_contents($definition_location . 'sensor_list.json'));
    $output_information['paths']['/sensors/{resource_id}'] = json_decode(file_get_contents($definition_location . 'sensor_record.json'));

    $definition_location = __DIR__ . '/../json/component/';
    $output_information['components']['schemas']['resourceNotFound'] = json_decode(file_get_contents($definition_location . '/resource_not_found.json'));
    $output_information['components']['schemas']['siteList'] = json_decode(file_get_contents($definition_location . '/site_list.json'));
    $output_information['components']['schemas']['affiliationItem'] = json_decode(file_get_contents($definition_location . '/affiliation_item.json'));
    $output_information['components']['schemas']['recordList'] = json_decode(file_get_contents($definition_location . '/record_list.json'));
    $output_information['components']['schemas']['completeSiteRecord'] = json_decode(file_get_contents($definition_location . '/complete_site_record.json'));
    $output_information['components']['schemas']['completeDatasetRecord'] = json_decode(file_get_contents($definition_location . '/complete_dataset_record.json'));
    $output_information['components']['schemas']['completeActivityRecord'] = json_decode(file_get_contents($definition_location . '/complete_activity_record.json'));
    $output_information['components']['schemas']['completeSensorRecord'] = json_decode(file_get_contents($definition_location . '/complete_sensor_record.json'));
    $output_information['components']['schemas']['personRecord'] = json_decode(file_get_contents($definition_location . '/person_record.json'));
    $output_information['components']['schemas']['organisationRecord'] = json_decode(file_get_contents($definition_location . '/organisation_record.json'));
    $output_information['components']['schemas']['taxonomyTerm'] = json_decode(file_get_contents($definition_location . '/taxonomy_term.json'));
    $output_information['components']['schemas']['referencedRecord'] = json_decode(file_get_contents($definition_location . '/referenced_record.json'));
    $output_information['components']['schemas']['urlObject'] = json_decode(file_get_contents($definition_location . '/url_object.json'));

    return new JsonResponse($output_information);
  }
  
}