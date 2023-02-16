<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each sensor as a JSON; only one record at a time based on the provided UUID
 */
class DeimsSensorRecordController extends ControllerBase {
  
  public function parseFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$information['title'] = $node->get('title')->value;
		$information['id']['prefix'] = 'https://deims.org/sensors/';
		$information['id']['suffix'] = $node->get('uuid')->value;
		$information['type'] = 'sensor';
		$information['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

		$information['attributes']['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$information['attributes']['general']['contact'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$information['attributes']['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$information['attributes']['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));

		$information['attributes']['geographic']['coordinates'] = $node->get('field_coordinates')->value;
		$information['attributes']['geographic']['trajectory'] = $node->get('field_boundaries')->value;
		$information['attributes']['geographic']['elevation']['value'] = (!is_null($node->get('field_elevation_avg')->value)) ? floatval($node->get('field_elevation_avg')->value)  : null;
		$information['attributes']['geographic']['elevation']['unit'] = 'msl';

		$information['attributes']['observation']['sensorType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sensor_type'), true);
		$information['attributes']['observation']['resultAcquisitionSource'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_result_acquisition_source', $single_value_field=true);	
		$information['attributes']['observation']['observedProperty'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_observation'));
		
		return $information;
		
  }

}
