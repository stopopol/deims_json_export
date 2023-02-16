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
  
  public function parseFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$information['type'] = "Feature";
		$information['geometry'] = json_decode(\Drupal::service('geofield.geophp')->load($node->get('field_boundaries')->value)->out('json'));

		// all properties information
		$information['properties']['title'] = $node->get('title')->value;
		$information['properties']['id']['prefix'] = 'https://deims.org/locations/';
		$information['properties']['id']['suffix'] = $node->get('uuid')->value;
		$information['properties']['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$information['properties']['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		$information['properties']['locationType'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_location_type'), $single_value_field=true);
		$information['properties']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'), $single_value_field=true);
		$information['properties']['abstract'] = $node->get('field_abstract')->value;	
		$information['properties']['elevation']['min'] = (!is_null($node->get('field_elevation_min')->value)) ? floatval($node->get('field_elevation_min')->value)  : null;
		$information['properties']['elevation']['max'] = (!is_null($node->get('field_elevation_max')->value)) ? floatval($node->get('field_elevation_max')->value)  : null;
		$information['properties']['elevation']['unit'] = 'msl';
		$information['properties']['images'] = null;
		
		return $information;
		
  }
	
}
