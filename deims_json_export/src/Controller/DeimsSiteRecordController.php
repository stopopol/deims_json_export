<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsSiteRecordController extends ControllerBase {
  
  public function parseSiteFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();
		
		// mandatory block for every resource type
		
		$site_information['title'] = $node->get('title')->value;
		$site_information['type'] = "site";
		$site_information['id']['prefix'] = 'https://deims.org/';
		$site_information['id']['suffix'] = $node->get('field_deims_id')->value;
		$site_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
		$site_information['attributes']['affiliation']['network'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
		$site_information['attributes']['affiliation']['project'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_projects'));
		$site_information['attributes']['affiliation']['protectionProgramme'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_protection_programme'));	

		// parses both referenced fields of content type 'person' and/or 'organisation'
		$site_information['attributes']['contact']['siteManager'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_manager'));
		$site_information['attributes']['contact']['siteOwner'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_owner'));
		$site_information['attributes']['contact']['fundingAgency'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_funding_agency'));
		$site_information['attributes']['contact']['SiteURL'] = $DeimsFieldController->parseRegularField($node->get('field_url'), "url");

		$site_information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$site_information['attributes']['general']['history'] = $node->get('field_history')->value;
		$site_information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$site_information['attributes']['general']['purpose'] = $node->get('field_purpose')->value;
		$site_information['attributes']['general']['status'] = $node->get('field_site_status')->value;
		$site_information['attributes']['general']['yearClosed'] = intval($node->get('field_year_closed')->value);	 
		$site_information['attributes']['general']['yearEstablished'] = intval($node->get('field_year_established')->value);
		$site_information['attributes']['general']['hierarchy']['parent'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parent_site'));	
		$site_information['attributes']['general']['hierarchy']['children'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_subsite_name'));
		$site_information['attributes']['general']['siteName'] = $node->get('field_name')->value;
		$site_information['attributes']['general']['shortName'] = $node->get('field_name_short')->value;
		$site_information['attributes']['general']['siteType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_type', $single_value_field=true);

		// aggregate temperature fields; shorthand ifs to catch empty values
		$site_information['attributes']['environmentalCharacteristics']['airTemperature']['avg'] = (!is_null($node->get('field_air_temp_avg')->value)) ? floatval($node->get('field_air_temp_avg')->value) : null;
		$site_information['attributes']['environmentalCharacteristics']['airTemperature']['min'] = (!is_null($node->get('field_air_temp_min')->value)) ? floatval($node->get('field_air_temp_min')->value) : null;
		$site_information['attributes']['environmentalCharacteristics']['airTemperature']['max'] = (!is_null($node->get('field_air_temp_max')->value)) ? floatval($node->get('field_air_temp_max')->value) : null;
		$site_information['attributes']['environmentalCharacteristics']['airTemperature']['unit'] = '°C';
		$site_information['attributes']['environmentalCharacteristics']['biogeographicalRegion'] = $node->get('field_biogeographical_region')->value;
		$site_information['attributes']['environmentalCharacteristics']['biome'] = $node->get('field_biome')->value;
		$site_information['attributes']['environmentalCharacteristics']['ecosystemAndLanduse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_ecosystem_land_use'));
		$site_information['attributes']['environmentalCharacteristics']['eunisHabitat'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_eunis_habitat'));
		$site_information['attributes']['environmentalCharacteristics']['geoBonBiome'] = $node->get('field_geo_bon_biome')->value;
		$site_information['attributes']['environmentalCharacteristics']['geology'] = $node->get('field_geology')->value;
		$site_information['attributes']['environmentalCharacteristics']['hydrology'] = $node->get('field_hydrology')->value;
		$site_information['attributes']['environmentalCharacteristics']['soils'] = $node->get('field_soils')->value;	
		$site_information['attributes']['environmentalCharacteristics']['vegetation'] = $node->get('field_vegetation')->value;
		
		$site_information['attributes']['geographic']['boundaries'] = $node->get('field_boundaries')->value;
		$site_information['attributes']['geographic']['coordinates'] = $node->get('field_coordinates')->value;
		$site_information['attributes']['geographic']['country'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_country');
		$site_information['attributes']['geographic']['elevation']['avg'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null; 
		$site_information['attributes']['geographic']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$site_information['attributes']['geographic']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$site_information['attributes']['geographic']['elevation']['unit'] = 'msl';
		$site_information['attributes']['geographic']['size']['value']= (!is_null($node->get('field_size')->value)) ? floatval($node->get('field_size')->value) : null;
		$site_information['attributes']['geographic']['size']['unit']= "ha";

		// special case for boolean fields
		$site_information['attributes']['managementOfResources']['status'] = (!is_null($node->get('field_management_resources')->value)) ? (($node->get('field_management_resources')->value == 1) ? true : false) : null;	
		$site_information['attributes']['managementOfResources']['notes'] = $node->get('field_management_resources_notes')->value;
		$site_information['attributes']['managementOfResources']['percentage']  = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_management_resources_pct');

		// group observations
		$site_information['attributes']['focusDesignScale']['experiments']['design'] = $node->get('field_design_experiments')->value;
		$site_information['attributes']['focusDesignScale']['experiments']['scale'] = $node->get('field_scale_experiments')->value;
		$site_information['attributes']['focusDesignScale']['observations']['design'] = $node->get('field_design_observation')->value;
		$site_information['attributes']['focusDesignScale']['observations']['scale'] = $node->get('field_scale_observation')->value;
		$site_information['attributes']['focusDesignScale']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$site_information['attributes']['focusDesignScale']['researchTopics'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_research_topics'));
		
		$site_information['attributes']['infrastructure']['accessibleAllYear'] = (!is_null($node->get('field_accessible_all_year')->value)) ? (($node->get('field_accessible_all_year')->value == 1) ? true : false) : null;
		$site_information['attributes']['infrastructure']['accessType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_access_type', $single_value_field=true);		
		$site_information['attributes']['infrastructure']['allPartsAccessible'] = (!is_null($node->get('field_all_parts_accessible')->value)) ? (($node->get('field_all_parts_accessible')->value == 1) ? true : false) : null;
		$site_information['attributes']['infrastructure']['maintenanceInterval']= (!is_null($node->get('field_maintenance_interval')->value)) ? floatval($node->get('field_maintenance_interval')->value) : null;
		$site_information['attributes']['infrastructure']['permanentPowerSupply'] = (!is_null($node->get('field_permanent_power_supply')->value)) ? (($node->get('field_permanent_power_supply')->value == 1) ? true : false) : null;
		$site_information['attributes']['infrastructure']['operation']['permanent'] = (!is_null($node->get('field_permanent_operation')->value)) ? (($node->get('field_permanent_operation')->value == 1) ? true : false) : null;	
		$site_information['attributes']['infrastructure']['operation']['notes'] = $node->get('field_operation_notes')->value;
		$site_information['attributes']['infrastructure']['operation']['SiteVisitInterval']= (!is_null($node->get('field_site_visit_interval')->value)) ? floatval($node->get('field_site_visit_interval')->value) : null;
		$site_information['attributes']['infrastructure']['notes'] = $node->get('field_infrastructure_notes')->value;
		$site_information['attributes']['infrastructure']['collection'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_infrastructure'));
		$site_information['attributes']['infrastructure']['data']['notes'] = $node->get('field_data_notes')->value;
		$site_information['attributes']['infrastructure']['data']['policy']['notes'] = $node->get('field_site_data_policy')->value;
		$site_information['attributes']['infrastructure']['data']['policy']['url'] = $node->get('field_data_policy_url')->uri;
		$site_information['attributes']['infrastructure']['data']['policy']['rights'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights');
		$site_information['attributes']['infrastructure']['data']['services'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_dataservi');
		$site_information['attributes']['infrastructure']['data']['location'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_datastorloc', $single_value_field=true);	

		// TO DO:
		// field_images -> TBD when necessary
		$site_information['attributes']['general']['images'] = null;

		// list all referenced sites and datasets
		$site_information['attributes']['relatedResources'] = $DeimsFieldController->findRelatedResources(\Drupal::entityQuery('node')->condition('field_related_site',$node->id())->execute());


		return $site_information;
  }

}