<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsActivityRecordController extends ControllerBase {
  
  public function parseActivityFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$activity_information['id']['prefix'] = 'https://deims.org/activity/';
		$activity_information['id']['suffix'] = $node->get('uuid')->value;
		$activity_information['type'] = "site";
		$activity_information['name'] = $node->get('title')->value;
		$activity_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
		$activity_information['attributes']['general']['contact'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$activity_information['attributes']['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$activity_information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$activity_information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$activity_information['attributes']['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$activity_information['attributes']['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$activity_information['attributes']['geographic']['boundaries'] = (!is_null($node->get('field_related_site')->entity)) ? (($node->get('field_related_site')->entity->field_boundaries->value)) : null;	
		$activity_information['attributes']['availability']['digitally'] = (!is_null($node->get('field_data_available')->value)) ? (($node->get('field_data_available')->value == 1) ? true : false) : null;	
		$activity_information['attributes']['availability']['forEcopotential'] = (!is_null($node->get('field_data_available_ecopot')->value)) ? (($node->get('field_data_available_ecopot')->value == 1) ? true : false) : null;	
		$activity_information['attributes']['availability']['openData'] = (!is_null($node->get('field_open_data')->value)) ? (($node->get('field_open_data')->value == 1) ? true : false) : null;	
		$activity_information['attributes']['availability']['notes'] = $node->get('field_notes')->value;
		$activity_information['attributes']['availability']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$activity_information['attributes']['availability']['source']['url'] = $DeimsFieldController->parseRegularField($node->get('field_url'), "url");
		$activity_information['attributes']['resolution']['spatial'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'), $single_value_field=true);
		$activity_information['attributes']['resolution']['temporal'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'), $single_value_field=true);

		return $activity_information;
		
  }

}