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
		$site_information['_changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

		$site_information['_data']['affiliation']['network'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
		$site_information['_data']['affiliation']['project'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_projects'));
		$site_information['_data']['affiliation']['protectionProgramme'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_protection_programme'));	

		// parses both referenced fields of content type 'person' and/or 'organisation'
		$site_information['_data']['contact']['siteManager'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_manager'));
		$site_information['_data']['contact']['siteOwner'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_owner'));
		$site_information['_data']['contact']['fundingAgency'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_funding_agency'));

		$site_information['_data']['general']['abstract'] = $node->get('field_abstract')->value;
		$site_information['_data']['general']['history'] = $node->get('field_history')->value;
		$site_information['_data']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$site_information['_data']['general']['purpose'] = $node->get('field_purpose')->value;
		$site_information['_data']['general']['status'] = $node->get('field_site_status')->value;
		$site_information['_data']['general']['yearClosed'] = intval($node->get('field_year_closed')->value);	 
		$site_information['_data']['general']['yearEstablished'] = intval($node->get('field_year_established')->value);
		$site_information['_data']['general']['hierarchy']['parent'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parent_site'));	
		$site_information['_data']['general']['hierarchy']['children'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_subsite_name'));	
		$site_information['_data']['general']['shortName'] = $node->get('field_name_short')->value;
		$site_information['_data']['general']['siteType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_type', $single_value_field=true);
		$site_information['_data']['general']['SiteURL'] = $DeimsFieldController->parseURLField($node->get('field_url'));

		// aggregate temperature fields; shorthand ifs to catch empty values
		$site_information['_data']['environmentalCharacteristics']['airTemperature']['avg'] = (!is_null($node->get('field_air_temp_avg')->value)) ? floatval($node->get('field_air_temp_avg')->value) : null;
		$site_information['_data']['environmentalCharacteristics']['airTemperature']['min'] = (!is_null($node->get('field_air_temp_min')->value)) ? floatval($node->get('field_air_temp_min')->value) : null;
		$site_information['_data']['environmentalCharacteristics']['airTemperature']['max'] = (!is_null($node->get('field_air_temp_max')->value)) ? floatval($node->get('field_air_temp_max')->value) : null;
		$site_information['_data']['environmentalCharacteristics']['airTemperature']['unit'] = '°C';
		$site_information['_data']['environmentalCharacteristics']['biogeographicalRegion'] = $node->get('field_biogeographical_region')->value;
		$site_information['_data']['environmentalCharacteristics']['biome'] = $node->get('field_biome')->value;
		$site_information['_data']['environmentalCharacteristics']['ecosystemAndLanduse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_ecosystem_land_use'));
		$site_information['_data']['environmentalCharacteristics']['eunisHabitat'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_eunis_habitat'));
		$site_information['_data']['environmentalCharacteristics']['geo_bon_biome'] = $node->get('field_geo_bon_biome')->value;
		$site_information['_data']['environmentalCharacteristics']['geology'] = $node->get('field_geology')->value;
		$site_information['_data']['environmentalCharacteristics']['hydrology'] = $node->get('field_hydrology')->value;
		$site_information['_data']['environmentalCharacteristics']['soils'] = $node->get('field_soils')->value;	
		$site_information['_data']['environmentalCharacteristics']['vegetation'] = $node->get('field_vegetation')->value;
		
		$site_information['_data']['geographic']['boundaries'] = $node->get('field_boundaries')->value;
		$site_information['_data']['geographic']['country'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_country');
		$site_information['_data']['geographic']['elevation']['avg'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null; 
		$site_information['_data']['geographic']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$site_information['_data']['geographic']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$site_information['_data']['geographic']['elevation']['unit'] = 'msl';
		$site_information['_data']['geographic']['size']['value']= (!is_null($node->get('field_size')->value)) ? floatval($node->get('field_size')->value) : null;
		$site_information['_data']['geographic']['size']['unit']= "ha";

		$site_information['_data']['experiments']['design'] = $node->get('field_design_experiments')->value;
		$site_information['_data']['experiments']['scale'] = $node->get('field_scale_experiments')->value;

		// special case for boolean fields
		$site_information['_data']['managementOfResources']['status'] = (!is_null($node->get('field_management_resources')->value)) ? (($node->get('field_management_resources')->value == 1) ? true : false) : null;	
		$site_information['_data']['managementOfResources']['notes'] = $node->get('field_management_resources_notes')->value;
		$site_information['_data']['managementOfResources']['percentage']  = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_management_resources_pct');

		// group observations
		$site_information['_data']['observations']['design'] = $node->get('field_design_observation')->value;
		$site_information['_data']['observations']['scale'] = $node->get('field_scale_observation')->value;
		
		$site_information['_data']['focusDesignScale']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$site_information['_data']['focusDesignScale']['research_topics'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_research_topics'));
		
		$site_information['_data']['infrastructure']['accessibleAllYear'] = (!is_null($node->get('field_accessible_all_year')->value)) ? (($node->get('field_accessible_all_year')->value == 1) ? true : false) : null;
		$site_information['_data']['infrastructure']['accessType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_access_type', $single_value_field=true);		
		$site_information['_data']['infrastructure']['allPartsAccessible'] = (!is_null($node->get('field_all_parts_accessible')->value)) ? (($node->get('field_all_parts_accessible')->value == 1) ? true : false) : null;
		$site_information['_data']['infrastructure']['maintenanceInterval']= (!is_null($node->get('field_maintenance_interval')->value)) ? floatval($node->get('field_maintenance_interval')->value) : null;
		$site_information['_data']['infrastructure']['permanentPowerSupply'] = (!is_null($node->get('field_permanent_power_supply')->value)) ? (($node->get('field_permanent_power_supply')->value == 1) ? true : false) : null;
		$site_information['_data']['infrastructure']['operation']['permanent'] = (!is_null($node->get('field_permanent_operation')->value)) ? (($node->get('field_permanent_operation')->value == 1) ? true : false) : null;	
		$site_information['_data']['infrastructure']['operation']['notes'] = $node->get('field_operation_notes')->value;
		$site_information['_data']['infrastructure']['operation']['SiteVisitInterval']= (!is_null($node->get('field_site_visit_interval')->value)) ? floatval($node->get('field_site_visit_interval')->value) : null;
		$site_information['_data']['infrastructure']['notes'] = $node->get('field_infrastructure_notes')->value;
		$site_information['_data']['infrastructure']['collection'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_infrastructure'));
		$site_information['_data']['infrastructure']['data']['notes'] = $node->get('field_data_notes')->value;
		$site_information['_data']['infrastructure']['data']['policy']['notes'] = $node->get('field_site_data_policy')->value;
		$site_information['_data']['infrastructure']['data']['policy']['url'] = $node->get('field_data_policy_url')->uri;
		$site_information['_data']['infrastructure']['data']['policy']['rights'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights');
		$site_information['_data']['infrastructure']['data']['services'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_dataservi');
		$site_information['_data']['infrastructure']['data']['location'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_datastorloc', $single_value_field=true);	

		// TO DO:
		// field_images -> TBD when necessary
		$site_information['_data']['images'] = null;

		return $site_information;
  }

}