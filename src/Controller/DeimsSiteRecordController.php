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
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();
		
		$site_information['_id'] = (!empty($node->get('field_deims_id')->value)) ? 'https://deims.org/' . $node->get('field_deims_id')->value : null;
		$site_information['_name'] = $node->get('field_name')->value;
		$site_information['_coordinates'] = $node->get('field_coordinates')->value;

		$site_information['_data']['affiliation'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));		
		$site_information['_data']['abstract'] = $node->get('field_abstract')->value;	

		// aggregate temperature fields; shorthand ifs to catch empty values
		$site_information['_data']['air_temperature']['avg'] = (!is_null($node->get('field_air_temp_avg')->value)) ? floatval($node->get('field_air_temp_avg')->value) : null;
		$site_information['_data']['air_temperature']['min'] = (!is_null($node->get('field_air_temp_min')->value)) ? floatval($node->get('field_air_temp_min')->value) : null;
		$site_information['_data']['air_temperature']['max'] = (!is_null($node->get('field_air_temp_max')->value)) ? floatval($node->get('field_air_temp_max')->value) : null;
		$site_information['_data']['air_temperature']['unit'] = '°C';

		$site_information['_data']['biogeographical_region'] = $node->get('field_biogeographical_region')->value;
		$site_information['_data']['biome'] = $node->get('field_biome')->value;
		$site_information['_data']['boundaries'] = $node->get('field_boundaries')->value;
		
		$site_information['_data']['country'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_country');
		
		$site_information['_data']['ecosystem_landuse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_ecosystem_land_use'));

		// group experiments
		$site_information['_data']['experiments']['design'] = $node->get('field_design_experiments')->value;
		$site_information['_data']['experiments']['scale'] = $node->get('field_scale_experiments')->value;
		
				
		// aggregate elevation fields; shorthand ifs to catch empty values
		$site_information['_data']['elevation']['avg'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null; 
		$site_information['_data']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$site_information['_data']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$site_information['_data']['elevation']['unit'] = 'msl';
				
		$site_information['_data']['funding_agency'] = $node->get('field_funding_agency')->value;
		$site_information['_data']['geo_bon_biome'] = $node->get('field_geo_bon_biome')->value;
		$site_information['_data']['geology'] = $node->get('field_geology')->value;
		$site_information['_data']['history'] = $node->get('field_history')->value;
		$site_information['_data']['hydrology'] = $node->get('field_hydrology')->value;				
		$site_information['_data']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		
		// special case for boolean fields
		$site_information['_data']['management_resources']['status'] = (!is_null($node->get('field_management_resources')->value)) ? (($node->get('field_management_resources')->value == 1) ? true : false) : null;	
		$site_information['_data']['management_resources']['notes'] = $node->get('field_management_resources_notes')->value;
		
		$site_information['_data']['management_resources']['percentage']  = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_management_resources_pct');

		// group observations
		$site_information['_data']['observations']['design'] = $node->get('field_design_observation')->value;
		$site_information['_data']['observations']['scale'] = $node->get('field_scale_observation')->value;
		
		$site_information['_data']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));

		// special case for boolean fields
		$site_information['_data']['permanent_operation'] = (!is_null($node->get('field_permanent_operation')->value)) ? (($node->get('field_permanent_operation')->value == 1) ? true : false) : null;	
		$site_information['_data']['purpose'] = $node->get('field_purpose')->value;
		
		$site_information['_data']['research_topics'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_research_topics'));

		// parses both referenced fields of content type 'person' and/or 'organisation'
		$site_information['_data']['site_manager'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_manager'));
		$site_information['_data']['site_owner'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_owner'));
		$site_information['_data']['funding_agency'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_funding_agency'));
		
		// shorthand ifs to catch empty values
		$site_information['_data']['size_ha']= (!is_null($node->get('field_size')->value)) ? floatval($node->get('field_size')->value) : null;
	
		$site_information['_data']['soils'] = $node->get('field_soils')->value;
		$site_information['_data']['status'] = $node->get('field_site_status')->value;
		$site_information['_data']['vegetation'] = $node->get('field_vegetation')->value;	
		$site_information['_data']['year_closed'] = intval($node->get('field_year_closed')->value);	 
		$site_information['_data']['year_established'] = intval($node->get('field_year_established')->value);
		
		// available 
		$site_information['_data']['infrastructure']['accessible_all_year'] = (!is_null($node->get('field_accessible_all_year')->value)) ? (($node->get('field_accessible_all_year')->value == 1) ? true : false) : null;

		$site_information['_data']['infrastructure']['access_type'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_access_type');

		$site_information['_data']['infrastructure']['all_parts_accessible'] = (!is_null($node->get('field_all_parts_accessible')->value)) ? (($node->get('field_all_parts_accessible')->value == 1) ? true : false) : null;
		$site_information['_data']['infrastructure']['permanent_power_supply'] = (!is_null($node->get('field_permanent_power_supply')->value)) ? (($node->get('field_permanent_power_supply')->value == 1) ? true : false) : null;
		$site_information['_data']['infrastructure']['notes'] = $node->get('field_infrastructure_notes')->value;
			
		$site_information['_data']['infrastructure']['collection'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_infrastructure'));

		$site_information['_data']['infrastructure']['data']['notes'] = $node->get('field_data_notes')->value;
		$site_information['_data']['infrastructure']['data']['policy']['notes'] = $node->get('field_site_data_policy')->value;
		$site_information['_data']['infrastructure']['data']['policy']['url'] = $node->get('field_data_policy_url')->uri;

		// dedicated function for text list with n values
		$site_information['_data']['infrastructure']['data']['services'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_dataservi');

		return $site_information;
  }

}
