<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsActivityRecordController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function renderApi($uuid) {
	$record_information = [];
	$DeimsRecordRetrievalController = new DeimsRecordRetrievalController();
	$record_information = $DeimsRecordRetrievalController->record_retrieval($uuid, 'activity');
	return new JsonResponse($record_information);
  }
  
  public function parseActivityFields($node) {
		$activity_information = [];
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$activity_information['general']['name'] = $node->get('title')->value;
		$activity_information['general']['uuid'] = $node->get('uuid')->value;
		$activity_information['general']['contact'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$activity_information['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$activity_information['general']['abstract'] = $node->get('field_abstract')->value;
		$activity_information['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$activity_information['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$activity_information['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$activity_information['boundaries'] = (!is_null($node->get('field_related_site')->entity)) ? (($node->get('field_related_site')->entity->field_boundaries->value)) : null;	
		$activity_information['availability']['digitally'] = (!is_null($node->get('field_data_available')->value)) ? (($node->get('field_data_available')->value == 1) ? true : false) : null;	
		$activity_information['availability']['forEcopotential'] = (!is_null($node->get('field_data_available_ecopot')->value)) ? (($node->get('field_data_available_ecopot')->value == 1) ? true : false) : null;	
		$activity_information['availability']['openData'] = (!is_null($node->get('field_open_data')->value)) ? (($node->get('field_open_data')->value == 1) ? true : false) : null;	
		$activity_information['availability']['notes'] = $node->get('field_notes')->value;
		$activity_information['availability']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$activity_information['availability']['source']['url'] = $DeimsFieldController->parseURLField($node->get('field_url'));
		$activity_information['resolution']['spatial'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'), $single_value_field=true);
		$activity_information['resolution']['temporal'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'), $single_value_field=true);

		return $activity_information;
		
  }

}