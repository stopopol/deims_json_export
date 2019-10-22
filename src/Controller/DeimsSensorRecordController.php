<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsSensorRecordController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function renderApi($uuid) {
	$record_information = [];
	$DeimsRecordRetrievalController = new DeimsRecordRetrievalController();
	$record_information = $DeimsRecordRetrievalController->record_retrieval($uuid, 'sensor');
	return new JsonResponse($record_information);
  }
  
  public function parseSensorFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$sensor_information['general']['name'] = $node->get('title')->value;
		$sensor_information['general']['uuid'] = $node->get('uuid')->value;
		$sensor_information['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$sensor_information['general']['contact'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$sensor_information['general']['abstract'] = $node->get('field_abstract')->value;
		$sensor_information['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$sensor_information['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$sensor_information['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));

		$sensor_information['geographic']['coordinates'] = $node->get('field_coordinates')->value;
		$sensor_information['geographic']['trajectory'] = $node->get('field_boundaries')->value;
		$sensor_information['geographic']['elevation'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null;

		$sensor_information['observation']['sensorType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sensor_type'), true);
		$sensor_information['observation']['resultAcquisitionSource'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_result_acquisition_source', $single_value_field=true);	
		$sensor_information['observation']['observedProperty'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_observation'));
		
		return $sensor_information;
		
  }
}