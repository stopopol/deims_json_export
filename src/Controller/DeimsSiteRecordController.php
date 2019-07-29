<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
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
				$site_information = DeimsSiteRecordController::parseSiteFields($node);
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/site/{deimsid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no site with the given DEIMS.ID :(';
		$site_information['errors'] = $error_message;
	}
    return $site_information;
  }
  
  public function parseSiteFields($node) {
		$site_information = [];
		
		$site_information['_id'] = (!empty($node->get('field_deims_id')->value)) ? 'https://deims.org/' . $node->get('field_deims_id')->value : null;
		$site_information['_name'] = $node->get('field_name')->value;
		$site_information['_coordinates'] = $node->get('field_coordinates')->value;
		$site_information['_data']['affiliation'] = DeimsSiteParagraphFieldController::parseAffiliation($node);
				
		// aggregate temperature fields; shorthand ifs to catch empty values
		$site_information['_data']['air_temperature']['avg_c'] = (!is_null($node->get('field_air_temp_avg')->value)) ? floatval($node->get('field_air_temp_avg')->value) : null;
		$site_information['_data']['air_temperature']['min_c'] = (!is_null($node->get('field_air_temp_min')->value)) ? floatval($node->get('field_air_temp_min')->value) : null;
		$site_information['_data']['air_temperature']['max_c'] = (!is_null($node->get('field_air_temp_max')->value)) ? floatval($node->get('field_air_temp_max')->value) : null;

		$site_information['_data']['biogeographical_region'] = $node->get('field_biogeographical_region')->value;
		$site_information['_data']['biome'] = $node->get('field_biome')->value;
		$site_information['_data']['boundaries'] = $node->get('field_boundaries')->value;
		
				
		// print label of key-value pair instead of key
		$country_values_list = $node->getFieldDefinition('field_country')->getSetting('allowed_values');
		$site_information['_data']['country'] = $country_values_list[$node->get('field_country')->value];
		
		$site_information['_data']['ecosystem_landuse'] = $node->get('field_ecosystem_land_use')->value;
		
		// group experiments
		$site_information['_data']['experiments']['design'] = $node->get('field_design_experiments')->value;
		$site_information['_data']['experiments']['scale'] = $node->get('field_scale_experiments')->value;
		
				
		// aggregate elevation fields; shorthand ifs to catch empty values
		$site_information['_data']['elevation']['avg_msl'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null; 
		$site_information['_data']['elevation']['min_msl'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$site_information['_data']['elevation']['max_msl'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
				
		$site_information['_data']['funding_agency'] = $node->get('field_funding_agency')->value;
		$site_information['_data']['geo_bon_biome'] = $node->get('field_geo_bon_biome')->value;
		$site_information['_data']['geology'] = $node->get('field_geology')->value;
		$site_information['_data']['history'] = $node->get('field_history')->value;
		$site_information['_data']['hydrology'] = $node->get('field_hydrology')->value;				
		$site_information['_data']['keywords'] = $node->get('field_keywords')->value;
		
		// special case for boolean fields
		$site_information['_data']['management_resources']['status'] = (!is_null($node->get('field_management_resources')->value)) ? (($node->get('field_management_resources')->value == 1) ? true : false) : null;	
		$site_information['_data']['management_resources']['notes'] = $node->get('field_management_resources_notes')->value;

		// print label of key-value pair instead of key
		$management_resources_pct_values_list = $node->getFieldDefinition('field_management_resources_pct')->getSetting('allowed_values');
		$site_information['_data']['management_resources']['percentage'] = $management_resources_pct_values_list[$node->get('field_management_resources_pct')->value];
		
		// group observations
		$site_information['_data']['observations']['design'] = $node->get('field_design_observation')->value;
		$site_information['_data']['observations']['scale'] = $node->get('field_scale_observation')->value;
		
		// special case for boolean fields
		$site_information['_data']['permanent_operation'] = (!is_null($node->get('field_permanent_operation')->value)) ? (($node->get('field_permanent_operation')->value == 1) ? true : false) : null;	
		$site_information['_data']['purpose'] = $node->get('field_purpose')->value;
		

		
		// shorthand ifs to catch empty values
		$site_information['_data']['size_ha']= (!is_null($node->get('field_size')->value)) ? floatval($node->get('field_size')->value) : null;
	
		$site_information['_data']['soils'] = $node->get('field_soils')->value;
		$site_information['_data']['status'] = $node->get('field_site_status')->value;
		$site_information['_data']['vegetation'] = $node->get('field_vegetation')->value;	
		$site_information['_data']['year_closed'] = intval($node->get('field_year_closed')->value);	 
		$site_information['_data']['year_established'] = intval($node->get('field_year_established')->value);

		return $site_information;
  }

}