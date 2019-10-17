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

		$dataset_information['name'] = $node->get('title')->value;
		$dataset_information['uuid'] = $node->get('uuid')->value;
		$dataset_information['abstract'] = (!is_null($node->get('field_abstract')->value)) ? ($node->get('field_abstract')->value) : null; 
		$dataset_information['biological_classification'] = (!is_null($node->get('field_biological_classification')->value)) ? ($node->get('field_abstract')->value) : null; 
		$dataset_information['contact']['corresponding'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_contact'));
		$dataset_information['contact']['creator']= $DeimsFieldController->parseEntityReferenceField($node->get('field_creator'));
		$dataset_information['data_policy_url'] = (!is_null($node->get('field_data_policy_url')->value)) ? ($node->get('field_data_policy_url')->value) : null; 
		$dataset_information['field_date_range'] = (!is_null($node->get('field_date_range')->value)) ? ($node->get('field_date_range')->value) : null; 
		$dataset_information['field_doi'] = (!is_null($node->get('field_doi')->value)) ? ($node->get('field_doi')->value) : null; 
		$dataset_information['_data']['general']['keywords']= $DeimsFieldController->parseEntityReferenceField($node->get('field_keywords'));
		$dataset_information['_data']['general']['inspire']= $DeimsFieldController->parseEntityReferenceField($node->get('field_inspire_data_theme'));

		$dataset_information['instrumentation'] = (!is_null($node->get('field_instrumentation')->value)) ? ($node->get('field_instrumentation')->value) : null; 
		$dataset_information['rights'] = reset($DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_rights'));
		$dataset_information['language'] = reset($DeimsFieldController->parseTextListField($node, $fieldname = 'field_language'));
		$dataset_information['legalAct'] = reset($DeimsFieldController->parseTextListField($node, $fieldname = 'field_dataset_legal'));

		$dataset_information['contact']['metadataProvider']= $DeimsFieldController->parseEntityReferenceField($node->get('field_metadata_provider'));
		$dataset_information['methodURL']['title'] = $node->get('field_method')->title;
		$dataset_information['methodURL']['uri'] = $node->get('field_method')->uri;
		$dataset_information['methodDescription'] = (!is_null($node->get('field_method_description')->value)) ? ($node->get('field_method_description')->value) : null; 
		
		$dataset_information['parameters'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_parameters'));
		$dataset_information['accessUse'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_access_use_termref'));
		$dataset_information['qualityAssurance'] = (!is_null($node->get('field_quality_assurance')->value)) ? ($node->get('field_quality_assurance')->value) : null; 
		$dataset_information['citation'] = (!is_null($node->get('field_citation')->value)) ? ($node->get('field_citation')->value) : null; 
		$dataset_information['relatedSite'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_related_site'));
		$dataset_information['temporalResolution'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_temporal_resolution'));
		$dataset_information['samplingTimeUnit'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_sampling_time_unit'));
		$dataset_information['spatialDesign'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_design'));
		$dataset_information['spatialScale'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_spatial_scale'));

		// TO DO:
		//field_data_sources
		//field_observation_location
		//field_online_locator

		return $dataset_information;

  }

}