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
	  $output_information['openapi'] = '3.0.0';
	  $output_information['info']['title'] = 'DEIMS-SDR API';
	  $output_information['info']['description'] = 'This is an API for automated data export from the site and dataset registry DEIMS-SDR';
    $output_information['info']['termsOfService'] = 'https://deims.org/terms';
    $output_information['info']['contact']['name'] = 'DEIMS-SDR Support';
    $output_information['info']['contact']['url'] = 'https://deims.org/contact';
    $output_information['info']['license']['name'] = 'CC-BY-NC International 4.0';
    $output_information['info']['license']['url'] = 'https://creativecommons.org/licenses/by-nc/4.0/';
    $output_information['info']['version'] = '0.1';
    //$output_information['tags'] = array("name" = "sites","datasets","sensors","activities");

    $output_information['paths']['/site']['get']['description'] = 'Returns sites published on DEIMS-SDR';
    $output_information['paths']['/site']['get']['responses']['200']['description'] = 'A list of sites';
    $output_information['paths']['/site']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/site']['get']['responses']['200']['content']['application/json']['schema']['items']['$ref'] = 'to be implemented';
   
    $output_information['paths']['/site/{resource_id}']['get']['description'] = 'Returns a single site record';
    $output_information['paths']['/site/{resource_id}']['get']['parameters'] = 
      array(
        array(
          "name" => "resource_id",
          "in" => "path",
          "description" => 'The DEIMS.ID of the site record',
          "required" => true,
          "schema" => array("type" => "string"),
          "example" => '8eda49e9-1f4e-4f3e-b58e-e0bb25dc32a6'
          )
    );
    $output_information['paths']['/site/{resource_id}']['get']['responses']['200']['description'] = 'JSON object containing all site information';
    $output_information['paths']['/site/{resource_id}']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/site/{resource_id}']['get']['responses']['default']['description'] = 'error payload';
    $output_information['paths']['/site/{resource_id}']['get']['responses']['default']['content']['text/html'] = 'to be implemented';

    $output_information['paths']['/dataset']['get']['description'] = 'Returns datasets published on DEIMS-SDR';
    $output_information['paths']['/dataset']['get']['responses']['200']['description'] = 'A list of sites';
    $output_information['paths']['/dataset']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/dataset']['get']['responses']['200']['content']['application/json']['schema']['items']['$ref'] = 'to be implemented';

    $output_information['paths']['/dataset/{resource_id}']['get']['description'] = 'Returns a single dataset record';
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['in'] = 'path';
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['name'] = 'resource_id';
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['description'] = 'The uuid of the dataset record';
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['required'] = true;
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['schema']['type'] = 'string';
    $output_information['paths']['/dataset/{resource_id}']['get']['parameters']['example'] = '63b2325e-4eca-11e4-a597-005056ab003f';
    $output_information['paths']['/dataset/{resource_id}']['get']['responses']['200']['description'] = 'JSON object containing all dataset information';
    $output_information['paths']['/dataset/{resource_id}']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/dataset/{resource_id}']['get']['responses']['default']['description'] = 'error payload';
    $output_information['paths']['/dataset/{resource_id}']['get']['responses']['default']['content']['text/html'] = 'to be implemented';

    $output_information['paths']['/activity']['get']['description'] = 'Returns activities published on DEIMS-SDR';
    $output_information['paths']['/activity']['get']['responses']['200']['description'] = 'A list of sites';
    $output_information['paths']['/activity']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/activity']['get']['responses']['200']['content']['application/json']['schema']['items']['$ref'] = 'to be implemented';

    $output_information['paths']['/activity/{resource_id}']['get']['description'] = 'Returns a activity dataset record';
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['in'] = 'path';
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['name'] = 'resource_id';
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['description'] = 'The uuid of the activity record';
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['required'] = true;
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['schema']['type'] = 'string';
    $output_information['paths']['/activity/{resource_id}']['get']['parameters']['example'] = '8689b125-ee46-4d09-9e46-640f9c5c6eab';
    $output_information['paths']['/activity/{resource_id}']['get']['responses']['200']['description'] = 'JSON object containing all activity information';
    $output_information['paths']['/activity/{resource_id}']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/activity/{resource_id}']['get']['responses']['default']['description'] = 'error payload';
    $output_information['paths']['/activity/{resource_id}']['get']['responses']['default']['content']['text/html'] = 'to be implemented';

    $output_information['paths']['/sensor']['get']['description'] = 'Returns sensors published on DEIMS-SDR';
    $output_information['paths']['/sensor']['get']['responses']['200']['description'] = 'A list of sites';
    $output_information['paths']['/sensor']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/sensor']['get']['responses']['200']['content']['application/json']['schema']['items']['$ref'] = 'to be implemented';

    $output_information['paths']['/sensor/{resource_id}']['get']['description'] = 'Returns a single sensor record';
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['in'] = 'path';
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['name'] = 'resource_id';
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['description'] = 'The uuid of the sensor record';
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['required'] = true;
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['schema']['type'] = 'string';
    $output_information['paths']['/sensor/{resource_id}']['get']['parameters']['example'] = 'fb583610-fe71-4793-b1a9-43097ed5c3e3';
    $output_information['paths']['/sensor/{resource_id}']['get']['responses']['200']['description'] = 'JSON object containing all sensor information';
    $output_information['paths']['/sensor/{resource_id}']['get']['responses']['200']['content']['application/json']['schema']['type'] = 'object';
    $output_information['paths']['/sensor/{resource_id}']['get']['responses']['default']['description'] = 'error payload';
    $output_information['paths']['/sensor/{resource_id}']['get']['responses']['default']['content']['text/html'] = 'to be implemented';
    
    return new JsonResponse($output_information);
  }
  
}