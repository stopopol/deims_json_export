<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This controller lists all nodes on DEIMS-SDR in a JSON object including a selected subset of information
 */
class DeimsNodeListsController {

  /**
   * Callback for the API.
   */
  public function renderRecordList($content_type) {
	$node_list = array();

	// load url parameters and make them lower case
	$url_parameters = array_change_key_case(\Drupal::request()->query->all(), CASE_LOWER);
	
	// get integer values of parameters limit and offset
	$limit =  array_key_exists('limit', $url_parameters) ? ((int)$url_parameters['limit']) : null;
	$offset = array_key_exists('offset', $url_parameters) ? ((int)$url_parameters['offset']) : null;
	$format = array_key_exists('format', $url_parameters) ? $url_parameters['format']: null;
	
	$allowed_query_parameters = array('format', 'limit', 'offset');
	$allowed_entity_types = array('sites', 'activities', 'sensors', 'datasets', 'networks', 'locations');
	
	if (in_array($content_type, $allowed_entity_types)) {
	
		// catch invalid filter parameters which depend on the content type
		if (isset($url_parameters)) {
			switch ($content_type) {
				case 'sites':
					array_push($allowed_query_parameters, 'network', 'sitecode', 'verified', 'observedproperty', 'name', 'country');
					
					// site filter parameters
					$query_value_network = array_key_exists('network', $url_parameters) ? $url_parameters['network']: null;
					$query_value_sitecode = array_key_exists('sitecode', $url_parameters) ? $url_parameters['sitecode']: null;
					$query_value_verified = array_key_exists('verified', $url_parameters) ? $url_parameters['verified']: null;
					$query_value_observedProperties = array_key_exists('observedproperty', $url_parameters) ? $url_parameters['observedproperty']: null;
					$query_value_sitename = array_key_exists('name', $url_parameters) ? $url_parameters['name']: null;
					$query_value_country = array_key_exists('country', $url_parameters) ? $url_parameters['country']: null;
					
					// throw error if verified flag has been provided but no network
					if (isset($query_value_verified) && is_null($query_value_network)) {
						$error_message['status'] = "400";
						$error_message['source'] = ["pointer" => "/api/sites?verified="];
						$error_message['title'] = 'Bad request';
						$error_message['detail'] = "The 'verified' filter must be tied to the 'network' filter.";
						$node_list['errors'] = $error_message;
						return new JsonResponse($node_list);
					}
						
					break;
				case 'locations':
					array_push($allowed_query_parameters, 'type', 'relatedsite');
					$query_value_locationType = array_key_exists('type', $url_parameters) ? $url_parameters['type']: null;
					$query_value_relatedSite = array_key_exists('relatedsite', $url_parameters) ? $url_parameters['relatedsite']: null;
					break;
			}
			
			foreach (array_keys($url_parameters) as $parameter) {
				if (!in_array($parameter, $allowed_query_parameters)) {
					$error_message['status'] = "400";
					$error_message['source'] = ["pointer" => "/api/{$content_type}?{$parameter}="];
					$error_message['title'] = 'Bad request';
					$error_message['detail'] = "An invalid filter parameter has been provided. '" . $parameter . "' does not exist.";
					$node_list['errors'] = $error_message;
					return new JsonResponse($node_list);
				}
			}
		
		}
		
		$query = \Drupal::entityQuery('node');
		$query->condition('status', 1);
		
		// add content_type related filter to query
		switch ($content_type) {
			
			case 'sites':
				
				$landing_page_label = '';				
				$query->condition('type', 'site');
				
				// Create the orConditionGroup
				$orGroup = $query
					->orConditionGroup()
					->condition('field_status.entity:taxonomy_term.tid', 54180, '!=') // exclude all inactive/closed sites
					->condition('field_status', NULL, 'IS NULL'); // but still consider all sites that haven't filled in the field
				$query->condition($orGroup);
				
				if (isset($url_parameters)) {
					// if filters are provided, add additional filter conditions
					if ($query_value_observedProperties) {
						$query->condition('field_parameters.entity:taxonomy_term.field_uri', $query_value_observedProperties);
					}
					
					if ($query_value_network) {
						$query->condition('field_affiliation.entity:paragraph.field_network.entity:node.uuid', $query_value_network);			
					}
					
					if ($query_value_verified) {
						
						if ($query_value_verified == 'true') {
							$query->condition('field_affiliation.entity:paragraph.field_network_verified', true);			
						}
						if ($query_value_verified == 'false') {
							$query->condition('field_affiliation.entity:paragraph.field_network_verified', false);			
						}
						
					} 
					
					if ($query_value_sitecode) {
						$query->condition('field_affiliation.entity:paragraph.field_network_specific_site_code', $query_value_sitecode, 'LIKE');
					}
					
					// ISO two digit code
					if ($query_value_country) {
						$query->condition('field_country', $query_value_country);
					}
					
					if ($query_value_sitename) {
						$query->condition('field_name', $query_value_sitename, 'CONTAINS');
					}
					
				}
						
				break;
			
			case 'activities':
				$landing_page_label = 'activity/';
				$query->condition('type', 'activity');
				break;
			case 'sensors':
				$landing_page_label = 'sensors/';
				$query->condition('type', 'sensor');
				break;
			case 'datasets':
				$landing_page_label = 'dataset/';
				$query->condition('type', 'dataset');
				break;
			case 'networks':
				$landing_page_label = 'networks/';
				$query->condition('type', 'network');
				break;
			case 'locations':
				$landing_page_label = 'locations/';
				$query->condition('type', 'observation_location');
				
				if (isset($url_parameters)) {
					// if filters are provided, add additional filter conditions
					if ($query_value_locationType) {
						$query->condition('field_location_type.entity:taxonomy_term.field_uri', $query_value_locationType);
					}
					if ($query_value_relatedSite) {
						$query->condition('field_related_site.entity:node.uuid', $query_value_relatedSite);
					}
				}
				
				break;

		}
		
		$nids = $query->execute();			
		$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
				
		$number_of_parsed_nodes = 0;
		$number_of_listed_nodes = 0;
		foreach ($nodes as $node) {

			if ($node->isPublished()) {

				// continue if offset
				if ($number_of_parsed_nodes < $offset) {
					$number_of_parsed_nodes++;
					continue;
				}
						
				$node_information['title'] = $node->get('title')->value;
				$node_information['id']['prefix'] = "https://deims.org/" . $landing_page_label;
				$node_information['id']['suffix'] = $node->get('uuid')->value;
				$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
				
				// if site add coordinates
				if ($content_type == "sites") {
					$node_information['coordinates'] = $node->get('field_coordinates')->value;
				}

				array_push($node_list, $node_information);

				$number_of_listed_nodes++;
				if ($number_of_listed_nodes == $limit) break;

			}
		}	
		
	}
	else {
		$error_message['status'] = "400";
		$error_message['source'] = ["pointer" => "/api/{$content_type}"];
		$error_message['title'] = 'Bad request';
		$error_message['detail'] = "This is not a valid request because the DEIMS-SDR API doesn't have a resource type that is called '" . $content_type . "' :(";
		$node_list['errors'] = $error_message;
	}

	
	// export as csv if requested
	if ($format == "csv" && !isset($node_list['errors'])) {
		
		$DeimsCsvExportController = new DeimsCsvExportController();
		return $DeimsCsvExportController->createCSV($content_type, $node_list);

	}
    return new JsonResponse($node_list);
  }
  
}
