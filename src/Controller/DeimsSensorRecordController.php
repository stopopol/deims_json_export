<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each sensor as a JSON; only one record at a time based on the provided UUID
 */
class DeimsSensorRecordController extends ControllerBase {
  
  public function parseSensorFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$sensor_information['title'] = $node->get('title')->value;
		$sensor_information['id']['prefix'] = 'https://deims.org/sensor/';
		$sensor_information['id']['suffix'] = $node->get('uuid')->value;
		$sensor_information['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$sensor_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

		$sensor_information['attributes']['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$sensor_information['attributes']['general']['contact'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$sensor_information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$sensor_information['attributes']['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$sensor_information['attributes']['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$sensor_information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));

		$sensor_information['attributes']['geographic']['coordinates'] = $node->get('field_coordinates')->value;
		$sensor_information['attributes']['geographic']['trajectory'] = $node->get('field_boundaries')->value;
		$sensor_information['attributes']['geographic']['elevation']['value'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null;
		$sensor_information['attributes']['geographic']['elevation']['unit'] = 'msl';

		$sensor_information['attributes']['observation']['sensorType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sensor_type'), true);
		$sensor_information['attributes']['observation']['resultAcquisitionSource'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_result_acquisition_source', $single_value_field=true);	
		$sensor_information['attributes']['observation']['observedProperty'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_observation'));
		
		return $sensor_information;
		
  }

}