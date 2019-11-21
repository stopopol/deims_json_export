<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each dataset as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsDatasetRecordController extends ControllerBase {
 
  public function parseDatasetFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$dataset_information['title'] = $node->get('title')->value;
		$dataset_information['type'] = 'dataset';
		$dataset_information['id']['prefix'] = 'https://deims.org/dataset/';
		$dataset_information['id']['suffix'] = $node->get('uuid')->value;
		$dataset_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
		$dataset_information['attributes']['general']['abstract'] = (!is_null($node->get('field_abstract')->value)) ? ($node->get('field_abstract')->value) : null; 
		$dataset_information['attributes']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$dataset_information['attributes']['general']['inspire']= $DeimsFieldController->parseEntityReferenceField($node->get('field_inspire_data_theme'));
		$dataset_information['attributes']['general']['dateRange']['from'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$dataset_information['attributes']['general']['dateRange']['to'] = (!is_null($node->get('field_date_range')->end_value)) ? ($node->get('field_date_range')->end_value) : null;
		$dataset_information['attributes']['general']['language'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_language', true);
		$dataset_information['attributes']['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));

		$dataset_information['attributes']['contact']['corresponding'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$dataset_information['attributes']['contact']['creator'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_creator'));
		$dataset_information['attributes']['contact']['metadataProvider'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_metadata_provider'));

		$dataset_information['attributes']['observations']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$dataset_information['attributes']['observations']['speciesGroups'] = (!is_null($node->get('field_biological_classification')->value)) ? ($node->get('field_abstract')->value) : null; 
		
		$dataset_information['attributes']['geographic'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_observation_location'));

		$dataset_information['attributes']['onlineDistribution']['dataPolicyUrl']= $DeimsFieldController->parseRegularField($node->get('field_data_policy_url'), "url");
		$dataset_information['attributes']['onlineDistribution']['doi'] = (!is_null($node->get('field_doi')->value)) ? ($node->get('field_doi')->value) : null; 
		$dataset_information['attributes']['onlineDistribution']['onlineLocation'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_online_locator')); 
		
		$dataset_information['attributes']['legal']['accessUse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_access_use_termref'));
		$dataset_information['attributes']['legal']['rights'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights');
		$dataset_information['attributes']['legal']['legalAct'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_legal', $single_value_field=true);
		$dataset_information['attributes']['legal']['citation'] = (!is_null($node->get('field_citation')->value)) ? ($node->get('field_citation')->value) : null; 
		
		$dataset_information['attributes']['method']['instrumentation'] = $DeimsFieldController->parseRegularField($node->get('field_instrumentation'), "multiText");
		$dataset_information['attributes']['method']['qualityAssurance'] = $DeimsFieldController->parseRegularField($node->get('field_quality_assurance'), "multiText");		
		$dataset_information['attributes']['method']['methodUrl']= $DeimsFieldController->parseRegularField($node->get('field_method'), "url");

		$dataset_information['attributes']['method']['methodDescription']= $DeimsFieldController->parseRegularField($node->get('field_method_description'), "multiText");
		$dataset_information['attributes']['method']['samplingTimeUnit'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sampling_time_unit'), $single_value_field=true);
		$dataset_information['attributes']['method']['spatialDesign'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_design'), $single_value_field=true);
		$dataset_information['attributes']['method']['spatialScale'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'), $single_value_field=true);
		$dataset_information['attributes']['method']['temporalResolution'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'), $single_value_field=true);
		
		return $dataset_information;
		
  }
}