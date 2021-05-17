<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\geofield\GeoPHP\GeoPHPInterface;

/**
 * This controller lists detailed information about each location as a GeoJSON; only one record at a time based on the provided UUID
 */
class DeimsLocationRecordController extends ControllerBase {
  
  public function parseLocationFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$location_information['type'] = "Feature";
		$location_information['geometry'] = json_decode(\Drupal::service('geofield.geophp')->load($node->get('field_boundaries')->value)->out('json'));

		// all properties information
		$location_information['properties']['title'] = $node->get('title')->value;
		$location_information['properties']['id']['prefix'] = 'https://deims.org/locations/';
		$location_information['properties']['id']['suffix'] = $node->get('uuid')->value;
		$location_information['properties']['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$location_information['properties']['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		$location_information['properties']['locationType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_location_type'), $single_value_field=true);
		$location_information['properties']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'), $single_value_field=true);
		$location_information['properties']['abstract'] = $node->get('field_abstract')->value;	
		$location_information['properties']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$location_information['properties']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$location_information['properties']['elevation']['unit'] = 'msl';
		$location_information['properties']['images'] = null;
		
		return $location_information;
		
  }
	
}
