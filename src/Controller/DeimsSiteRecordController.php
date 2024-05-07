<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsSiteRecordController extends ControllerBase {
  
  public function parseFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();
		
		// mandatory block for every resource type
		
		$information['title'] = $node->get('title')->value;
		$information['id']['prefix'] = 'https://deims.org/';
		$information['id']['suffix'] = $node->get('field_deims_id')->value;
		$information['type'] = 'site';
		$information['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
		$information['attributes']['affiliation']['networks'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
		$information['attributes']['affiliation']['projects'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_projects'));
		
		// parses both referenced fields of content type 'person' and/or 'organisation'
		$information['attributes']['contact']['siteManager'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_manager'));
		$information['attributes']['contact']['operatingOrganisation'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_site_owner'));
		$information['attributes']['contact']['metadataProvider'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_metadata_provider'));
		$information['attributes']['contact']['fundingAgency'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_funding_agency'));
		$information['attributes']['contact']['siteUrl'] = $DeimsFieldController->parseRegularField($node->get('field_url'), "url");

		$information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$information['attributes']['general']['citation'] = $node->get('field_citation')->value;
		$information['attributes']['general']['relatedIdentifiers'] = $DeimsFieldController->parseRegularField($node->get('field_related_identifiers'), "url");
		$information['attributes']['general']['status'] =  $DeimsFieldController->parseEntityReferenceField($node->get('field_status'), $single_value_field=true);
		$information['attributes']['general']['yearEstablished'] = (!is_null($node->get('field_year_established')->value)) ? intval($node->get('field_year_established')->value) : null;
		$information['attributes']['general']['yearClosed'] = (!is_null($node->get('field_year_closed')->value)) ? intval($node->get('field_year_closed')->value) : null;
	  	$information['attributes']['general']['hierarchy']['parent'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parent_site'));
		$information['attributes']['general']['hierarchy']['children'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_subsite_name'));
		$information['attributes']['general']['relatedSites'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_sites_paragraph'));
		$information['attributes']['general']['siteName'] = $node->get('field_name')->value;
		$information['attributes']['general']['shortName'] = $node->get('field_name_short')->value;
		$information['attributes']['general']['siteType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_site_type', $single_value_field=true);
		$information['attributes']['general']['protectionLevel'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_protection_level'));
		$information['attributes']['general']['landUse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_management_of_resources'));

		// aggregate temperature fields; shorthand ifs to catch empty values
		$information['attributes']['environmentalCharacteristics']['airTemperature']['yearlyAverage'] = (!is_null($node->get('field_air_temp_avg')->value)) ? floatval($node->get('field_air_temp_avg')->value) : null;
		$information['attributes']['environmentalCharacteristics']['airTemperature']['monthlyAverage'] = $DeimsFieldController->parseRegularField($node->get('field_air_temp'), "number_float");
		$information['attributes']['environmentalCharacteristics']['airTemperature']['unit'] = '°C';
		$information['attributes']['environmentalCharacteristics']['airTemperature']['referencePeriod'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_standard_reference_period'), $single_value_field=true);
		$information['attributes']['environmentalCharacteristics']['precipitation']['yearlyAverage'] = (!is_null($node->get('field_precipitation_annual')->value)) ? floatval($node->get('field_precipitation_annual')->value) : null;
		$information['attributes']['environmentalCharacteristics']['precipitation']['monthlyAverage'] = $DeimsFieldController->parseRegularField($node->get('field_precipitation'), "number_integer");
		$information['attributes']['environmentalCharacteristics']['precipitation']['unit'] = 'mm';
		$information['attributes']['environmentalCharacteristics']['precipitation']['referencePeriod'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_standard_reference_period'), $single_value_field=true);
		$information['attributes']['environmentalCharacteristics']['biogeographicalRegion'] = $node->get('field_biogeographical_region')->value;
		$information['attributes']['environmentalCharacteristics']['biome'] = $node->get('field_biome')->value;
		$information['attributes']['environmentalCharacteristics']['ecosystemType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_ecosystem_land_use'));
		$information['attributes']['environmentalCharacteristics']['eunisHabitat'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_eunis_habitat'));
		$information['attributes']['environmentalCharacteristics']['landforms'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_landforms'));	
	  	$information['attributes']['environmentalCharacteristics']['geoBonBiome'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_geo_bon_biome');
		$information['attributes']['environmentalCharacteristics']['geology'] = $node->get('field_geology')->value;
		$information['attributes']['environmentalCharacteristics']['hydrology'] = $node->get('field_hydrology')->value;
		$information['attributes']['environmentalCharacteristics']['soils'] = $node->get('field_soils')->value;
		$information['attributes']['environmentalCharacteristics']['vegetation'] = $node->get('field_vegetation')->value;
		
		$information['attributes']['geographic']['boundaries'] = $node->get('field_boundaries')->value;
		$information['attributes']['geographic']['coordinates'] = $node->get('field_coordinates')->value;
		$information['attributes']['geographic']['country'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_country');
		$information['attributes']['geographic']['elevation']['avg'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null; 
		$information['attributes']['geographic']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$information['attributes']['geographic']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$information['attributes']['geographic']['elevation']['unit'] = 'msl';
		$information['attributes']['geographic']['size']['value']= (!is_null($node->get('field_size_ha')->value)) ? floatval($node->get('field_size_ha')->value) : null;
		$information['attributes']['geographic']['size']['unit']= 'ha';
		$information['attributes']['geographic']['relatedLocations'] = $DeimsFieldController->findRelatedLocations(\Drupal::entityQuery('node')->accessCheck(FALSE)->condition('field_related_site',$node->id())->condition('type', 'observation_location')->execute());

		// group observations
		$information['attributes']['focusDesignScale']['experiments']['design'] = $node->get('field_design_experiments')->value;
		$information['attributes']['focusDesignScale']['experiments']['scale'] = $node->get('field_scale_experiments')->value;
		$information['attributes']['focusDesignScale']['observations']['design'] = $node->get('field_design_observation')->value;
		$information['attributes']['focusDesignScale']['observations']['scale'] = $node->get('field_scale_observation')->value;
		$information['attributes']['focusDesignScale']['observedProperties'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));		
		$information['attributes']['infrastructure']['accessibleAllYear'] = (!is_null($node->get('field_accessible_all_year')->value)) ? (($node->get('field_accessible_all_year')->value == 1) ? true : false) : null;
		$information['attributes']['infrastructure']['accessType'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_access_type', $single_value_field=true);		
		$information['attributes']['infrastructure']['allPartsAccessible'] = (!is_null($node->get('field_all_parts_accessible')->value)) ? (($node->get('field_all_parts_accessible')->value == 1) ? true : false) : null;
		$information['attributes']['infrastructure']['maintenanceInterval']= (!is_null($node->get('field_maintenance_interval')->value)) ? floatval($node->get('field_maintenance_interval')->value) : null;
		$information['attributes']['infrastructure']['permanentPowerSupply'] = (!is_null($node->get('field_permanent_power_supply')->value)) ? (($node->get('field_permanent_power_supply')->value == 1) ? true : false) : null;
		$information['attributes']['infrastructure']['operation']['permanent'] = (!is_null($node->get('field_permanent_operation')->value)) ? (($node->get('field_permanent_operation')->value == 1) ? true : false) : null;	
		$information['attributes']['infrastructure']['operation']['notes'] = $node->get('field_operation_notes')->value;
		$information['attributes']['infrastructure']['operation']['siteVisitInterval']= (!is_null($node->get('field_site_visit_interval')->value)) ? floatval($node->get('field_site_visit_interval')->value) : null;
		$information['attributes']['infrastructure']['notes'] = $node->get('field_infrastructure_notes')->value;
		$information['attributes']['infrastructure']['collection'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_infrastructure'));
		$information['attributes']['infrastructure']['data']['policy']['url'] = $DeimsFieldController->parseRegularField($node->get('field_data_policy_url'), "url");
		$information['attributes']['infrastructure']['data']['policy']['rights'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights');
		$information['attributes']['infrastructure']['data']['policy']['notes'] = $node->get('field_site_data_policy')->value;

		// to be refined?
		$information['attributes']['general']['images'] = $DeimsFieldController->parseImageField($node->get('field_images'));

		// list all referenced activities, datasets, sensors
		$information['attributes']['relatedResources'] = $DeimsFieldController->findRelatedResources(\Drupal::entityQuery('node')->accessCheck(FALSE)->condition('field_related_site',$node->id())->execute());

	  	$information['attributes']['projectRelated']['lter']['lterSiteClassification'] = $node->get('field_lter_site_classification')->value;
	  

		return $information;
  }

}
