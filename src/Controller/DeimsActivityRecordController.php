<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each activity as a JSON; only one record at a time based on the provided UUID
 */
class DeimsActivityRecordController extends ControllerBase {
  
  public function parseFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$information['title'] = $node->get('title')->value;
		$information['id']['prefix'] = 'https://deims.org/activity/';
		$information['id']['suffix'] = $node->get('uuid')->value;
		$information['type'] = 'activity';
		$information['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'html_datetime');
		$information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
		$information['attributes']['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$information['attributes']['general']['abstract'] = $node->get('field_abstract')->value;
		$information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$information['attributes']['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$information['attributes']['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;

		$information['attributes']['contact']['corresponding'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$information['attributes']['contact']['metadataProvider'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_metadata_provider'));

		$information['attributes']['geographic']['boundaries'] = (!is_null($node->get('field_related_site')->entity)) ? (($node->get('field_related_site')->entity->field_boundaries->value)) : null;	
		
		$information['attributes']['availability']['digitally'] = (!is_null($node->get('field_data_available')->value)) ? (($node->get('field_data_available')->value == 1) ? true : false) : null;	
		$information['attributes']['availability']['forEcopotential'] = (!is_null($node->get('field_data_available_ecopot')->value)) ? (($node->get('field_data_available_ecopot')->value == 1) ? true : false) : null;	
		$information['attributes']['availability']['openData'] = (!is_null($node->get('field_open_data')->value)) ? (($node->get('field_open_data')->value == 1) ? true : false) : null;	
		$information['attributes']['availability']['notes'] = $node->get('field_notes')->value;
		$information['attributes']['availability']['source']['url'] = $DeimsFieldController->parseRegularField($node->get('field_url'), "url");
		
		$information['attributes']['observation']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));

		$information['attributes']['resolution']['spatial'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'), $single_value_field=true);
		$information['attributes']['resolution']['temporal'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'), $single_value_field=true);

		// list all referenced datasets
		$information['attributes']['relatedResources'] = $DeimsFieldController->findRelatedResources(\Drupal::entityQuery('node')->accessCheck(FALSE)->condition('field_related_activity',$node->id())->execute());


		return $information;
		
  }

}
