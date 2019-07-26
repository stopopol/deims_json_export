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
		
		$site_information['affiliation'] = DeimsSiteParagraphFieldController::parseAffiliation($node);
				
		// aggregate temperature fields; shorthand ifs to catch empty values
		$site_information['air_temperature']['avg'] = (!is_null($node->get('field_air_temp_avg')->value)) ? $node->get('field_air_temp_avg')->value . ' °C' : null;
		$site_information['air_temperature']['min'] = (!is_null($node->get('field_air_temp_min')->value)) ? $node->get('field_air_temp_min')->value . ' °C' : null;
		$site_information['air_temperature']['max'] = (!is_null($node->get('field_air_temp_max')->value)) ? $node->get('field_air_temp_max')->value . ' °C' : null;

		$site_information['biogeographical_region'] = $node->get('field_biogeographical_region')->value;
		$site_information['biome'] = $node->get('field_biome')->value;
		$site_information['boundaries'] = $node->get('field_boundaries')->value;
		$site_information['coordinates'] = $node->get('field_coordinates')->value;
				
		// print label of key-value pair instead of key
		$country_values_list = $node->getFieldDefinition('field_country')->getSetting('allowed_values');
		$site_information['country'] = $country_values_list[$node->get('field_country')->value];
		$site_information['deimsid'] = (!empty($node->get('field_deims_id')->value)) ? 'https://deims.org/' . $node->get('field_deims_id')->value : null;
		
		$site_information['design_experiments'] = $node->get('field_design_experiments')->value;
		$site_information['design_observation'] = $node->get('field_design_observation')->value;
		$site_information['ecosystem_landuse'] = $node->get('field_ecosystem_land_use')->value;
				
		// aggregate elevation fields; shorthand ifs to catch empty values
		$site_information['elevation']['avg'] = (!is_null($node->get('field_elevation_avg')->value)) ? $node->get('field_elevation_avg')->value . ' m' : null;
		$site_information['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? $node->get('field_elevation_min')->value . ' m' : null;
		$site_information['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? $node->get('field_elevation_max')->value . ' m' : null;
				
		$site_information['funding_agency'] = $node->get('field_funding_agency')->value;
		$site_information['geo_bon_biome'] = $node->get('field_geo_bon_biome')->value;
		$site_information['geology'] = $node->get('field_geology')->value;
		$site_information['history'] = $node->get('field_history')->value;
		$site_information['hydrology'] = $node->get('field_hydrology')->value;				
		$site_information['keywords'] = $node->get('field_keywords')->value;				
		$site_information['name'] = $node->get('field_name')->value;
		$site_information['purpose'] = $node->get('field_purpose')->value;
		$site_information['short_name'] = $node->get('field_name_short')->value;
		$site_information['site_status'] = $node->get('field_site_status')->value;
				
		// shorthand ifs to catch empty values
		$site_information['size']= (!is_null($node->get('field_size')->value)) ? $node->get('field_size')->value . ' ha' : null;
				
		$site_information['soils'] = $node->get('field_soils')->value;
		$site_information['vegetation'] = $node->get('field_vegetation')->value;	
		$site_information['year_closed'] = $node->get('field_year_closed')->value;	
		$site_information['year_established'] = $node->get('field_year_established')->value;

		return $site_information;
  }

}