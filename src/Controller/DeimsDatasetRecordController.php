<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;


/**
 * This controller lists detailed information about each site as a JSON; only one record at a time based on the provided UUID/DEIMS.ID
 */
class DeimsDatasetRecordController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function renderApi($uuid) {
	return new JsonResponse($this->getResults($uuid));
  }

  /**
   * A helper function returning results.
   */
  public function getResults($uuid) {
	  	  
	$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
	$dataset_information = [];

	// needs to be a loop due to array structure of the loadByProperties result
	if (!empty($nodes)) {
		foreach ($nodes as $node) {
			if ($node->bundle() == 'dataset' && $node->isPublished()) {
				$dataset_information = DeimsDatasetRecordController::parseSiteFields($node);
			}
		}
	}
	else {
		$error_message = [];
		$error_message['status'] = "404";
		$error_message['source'] = ["pointer" => "/api/dataset/{uuid}"];
		$error_message['title'] = 'Resource not found';
		$error_message['detail'] = 'There is no site with the given ID :(';
		$dataset_information['errors'] = $error_message;
	}
    return $dataset_information;
  }
  
  public function parseSiteFields($node) {
		$dataset_information = [];
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();

		$dataset_information['general']['name'] = $node->get('title')->value;
		$dataset_information['general']['uuid'] = $node->get('uuid')->value;
		$dataset_information['general']['abstract'] = (!is_null($node->get('field_abstract')->value)) ? ($node->get('field_abstract')->value) : null; 
		$dataset_information['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$dataset_information['general']['inspire']= $DeimsFieldController->parseEntityReferenceField($node->get('field_inspire_data_theme'));
		$dataset_information['general']['dateRange'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null;
		$dataset_information['general']['language'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_language', true);
		$dataset_information['general']['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));

		$dataset_information['contact']['corresponding'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$dataset_information['contact']['creator'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_creator'));
		$dataset_information['contact']['metadataProvider'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_metadata_provider'));

		$dataset_information['observations']['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$dataset_information['observations']['speciesGroups'] = (!is_null($node->get('field_biological_classification')->value)) ? ($node->get('field_abstract')->value) : null; 
		
		$dataset_information['geographic'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_observation_location'));

		// TO DO: extend to all fields of dataSource
		$dataset_information['onlineDistribution']['dataSource'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_data_sources'));
		$dataset_information['onlineDistribution']['dataPolicyUrl'] = (!is_null($node->get('field_data_policy_url')->value)) ? ($node->get('field_data_policy_url')->value) : null; 
		$dataset_information['onlineDistribution']['doi'] = (!is_null($node->get('field_doi')->value)) ? ($node->get('field_doi')->value) : null; 
		
		$dataset_information['onlineDistribution']['onlineLocation'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_online_locator')); 
		
		$dataset_information['legal']['accessUse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_access_use_termref'));
		$dataset_information['legal']['rights'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights');
		$dataset_information['legal']['legalAct'] = $DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_legal', $single_value_field=true);
		$dataset_information['legal']['citation'] = (!is_null($node->get('field_citation')->value)) ? ($node->get('field_citation')->value) : null; 
		
		$dataset_information['method']['instrumentation'] = $DeimsFieldController->parseMultiText($node->get('field_instrumentation'));
		$dataset_information['method']['qualityAssurance'] = $DeimsFieldController->parseMultiText($node->get('field_quality_assurance'));
		$dataset_information['method']['methodURL']['title'] = $node->get('field_method')->title;
		$dataset_information['method']['methodURL']['uri'] = $node->get('field_method')->uri;
		$dataset_information['method']['methodDescription']= $DeimsFieldController->parseMultiText($node->get('field_method_description'));
		$dataset_information['method']['samplingTimeUnit'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sampling_time_unit'), $single_value_field=true);
		$dataset_information['method']['spatialDesign'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_design'), $single_value_field=true);
		$dataset_information['method']['spatialDesign'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_design'), $single_value_field=true);
		$dataset_information['method']['spatialScale'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'), $single_value_field=true);
		$dataset_information['method']['temporalResolution'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'), $single_value_field=true);
		
		return $dataset_information;
		
  }
}