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
		
		// load url parameters and make them lower case
		$url_parameters = array_change_key_case(\Drupal::request()->query->all(), CASE_LOWER);
		
		// general filter parameters available for all entity types
		$allowed_query_parameters = array('limit', 'offset', 'format', 'filename');
		$allowed_boolean_parameters = array("true", "false");
		
		// get integer values of parameters limit and offset
		$limit =  array_key_exists('limit', $url_parameters) ? ((int)$url_parameters['limit']) : null;
		$offset = array_key_exists('offset', $url_parameters) ? ((int)$url_parameters['offset']) : null;
		$format = array_key_exists('format', $url_parameters) ? $url_parameters['format']: null;
		$filename = array_key_exists('filename', $url_parameters) ? $url_parameters['filename']: null;
		
		if (isset($format) && $format !='csv') {
			$DeimsErrorMessageController = new DeimsErrorMessageController();	
			return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/{$content_type}?format={$format}", "The 'format' filter can only be set to 'csv'. JSON is the standard export format."));				
		}
					
		$query = \Drupal::entityQuery('node');
		$query->condition('status', 1);
			
		// add content_type related filter to query
		switch ($content_type) {
				
			case 'sites':
				
				$landing_page_label = '';				
				$query->condition('type', 'site');
				$query->accessCheck(FALSE);
					
				// Create the orConditionGroup to exclude inactive or closed sites
				$exclude_closed_sites = $query
					->orConditionGroup()
					->condition('field_status.entity:taxonomy_term.tid', 54180, '!=') // exclude all inactive/closed sites
					->condition('field_status', NULL, 'IS NULL'); // but still consider all sites that haven't filled in the field
									
				if (isset($url_parameters)) {
					
					array_push($allowed_query_parameters, 'network', 'sitecode', 'verified', 'observedproperty', 'name', 'country', 'includeclosed', 'status');
						
					// site filter parameters
					$query_value_network = array_key_exists('network', $url_parameters) ? $url_parameters['network']: null;
					$query_value_sitecode = array_key_exists('sitecode', $url_parameters) ? $url_parameters['sitecode']: null;
					$query_value_verified = array_key_exists('verified', $url_parameters) ? $url_parameters['verified']: null;
					$query_value_observedProperties = array_key_exists('observedproperty', $url_parameters) ? $url_parameters['observedproperty']: null;
					$query_value_sitename = array_key_exists('name', $url_parameters) ? $url_parameters['name']: null;
					$query_value_country = array_key_exists('country', $url_parameters) ? $url_parameters['country']: null;
					$query_value_includeclosed = array_key_exists('includeclosed', $url_parameters) ? $url_parameters['includeclosed']: null;
					$query_value_status = array_key_exists('status', $url_parameters) ? $url_parameters['status']: null;
					
					if (isset($query_value_includeclosed)) {
						// throw error if incorrect verified flag has been provided
						if (!in_array($query_value_includeclosed, $allowed_boolean_parameters)) {
							$DeimsErrorMessageController = new DeimsErrorMessageController();	
							return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?includeclosed={$query_value_includeclosed}", "The 'includeclosed' filter can only accept 'true' or 'false' as input values"));
						}
						
						if ($query_value_status) {
							$DeimsErrorMessageController = new DeimsErrorMessageController();
							return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?includeclosed={$query_value_includeclosed}&status={$query_value_status}", "Status and includeclosed filters cannot be applied at the same time."));
						}
						
						if ($query_value_includeclosed == "false") {
							$query->condition($exclude_closed_sites);
						}
					}
					else {
						// if either apply status or exclude closed sites
						if ($query_value_status) {
							$DeimsTaxonomyInformationController = new DeimsTaxonomyInformationController();
							$list_of_queriable_values = $DeimsTaxonomyInformationController->get_taxonomy_uris('site_reporting_status');
							
							if (!in_array($query_value_status, $list_of_queriable_values)) {
								$DeimsErrorMessageController = new DeimsErrorMessageController();
								return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?&status={$query_value_status}", "$query_value_status is not in the list of values that can be queried (" . implode(", ", $list_of_queriable_values)));
							}
							
							$query->condition('field_status.entity:taxonomy_term.field_uri', $query_value_status);
						}
						else {
							$query->condition($exclude_closed_sites);
						}
						
					}
						
					if (isset($query_value_verified)) {
						// throw error if verified flag has been provided but no network
						if (is_null($query_value_network)) {
							$DeimsErrorMessageController = new DeimsErrorMessageController();	
							return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?verified=", "The 'verified' filter must be tied to the 'network' filter."));
						}
						// throw error if incorrect verified flag has been provided
						if (!in_array($query_value_verified, $allowed_boolean_parameters)) {
							$DeimsErrorMessageController = new DeimsErrorMessageController();	
							return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?verified={$query_value_verified}", "The 'verified' filter can only accept 'true' or 'false' as input values"));
						}
					}
					
					
					// if filters are provided, add additional filter conditions
					// add [and] and [or] filters
					if ($query_value_observedProperties) {
						$DeimsTaxonomyInformationController = new DeimsTaxonomyInformationController();
						$list_of_queriable_values = $DeimsTaxonomyInformationController->get_taxonomy_uris('parameters');
							
						if (!in_array($query_value_observedProperties, $list_of_queriable_values)) {
							$DeimsErrorMessageController = new DeimsErrorMessageController();
							return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/sites?&observedproperty={$query_value_observedProperties}", "$query_value_observedProperties is not in the list of values that can be queried. URIs coming from the envthes have to be used, e.g. http://vocabs.lter-europe.net/EnvThes/21647"));
						}
						$query->condition('field_parameters.entity:taxonomy_term.field_uri', $query_value_observedProperties);	
					}
						
					if ($query_value_network) $query->condition('field_affiliation.entity:paragraph.field_network.entity:node.uuid', $query_value_network);
						
					if ($query_value_verified) {
						if ($query_value_verified == 'true') $query->condition('field_affiliation.entity:paragraph.field_network_verified', true);
						if ($query_value_verified == 'false') $query->condition('field_affiliation.entity:paragraph.field_network_verified', false);
					} 
						
					if ($query_value_sitecode) $query->condition('field_affiliation.entity:paragraph.field_network_specific_site_code', $query_value_sitecode, 'LIKE');
						
					// ISO two digit code
					if ($query_value_country) {
						if (str_contains($query_value_country, '[or]')) {
							$query->condition('field_country', explode("[or]", $query_value_country), 'IN');
						}
						else {
							$query->condition('field_country', $query_value_country);
						}
					}
						
					if ($query_value_sitename) $query->condition('field_name', $query_value_sitename, 'CONTAINS');
						
				}
				// if not filter parameters are provided exclude closed sites
				else {
					$query->condition($exclude_closed_sites);
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
					
					array_push($allowed_query_parameters, 'type', 'relatedsite');
					$query_value_locationType = array_key_exists('type', $url_parameters) ? $url_parameters['type']: null;
					$query_value_relatedSite = array_key_exists('relatedsite', $url_parameters) ? $url_parameters['relatedsite']: null;
					
					// if filters are provided, add additional filter conditions
					if ($query_value_locationType) $query->condition('field_location_type.entity:taxonomy_term.field_uri', $query_value_locationType);
					if ($query_value_relatedSite) $query->condition('field_related_site.entity:node.uuid', $query_value_relatedSite);
				}
					
				break;
					
			default:
				$DeimsErrorMessageController = new DeimsErrorMessageController();	
				return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/{$content_type}", "This is not a valid request because the DEIMS-SDR API doesn't have a resource type that is called '" . $content_type . "' :("));

		}
		
		foreach (array_keys($url_parameters) as $parameter) {
			if (!in_array($parameter, $allowed_query_parameters)) {
				$DeimsErrorMessageController = new DeimsErrorMessageController();
				return new JsonResponse($DeimsErrorMessageController->generateErrorMessage(400, "/api/{$content_type}?{$parameter}=", "An invalid filter parameter has been provided. '" . $parameter . "' does not exist or cannot be applied to this content type."));
			}
		}
			
		$nids = $query->accessCheck(FALSE)->execute();			
		$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
		$node_list = array();
					
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
				$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'custom', 'Y-m-d\TH:i:sP');

				// if site add coordinates
				if ($content_type == "sites") $node_information['coordinates'] = $node->get('field_coordinates')->value;
					
				array_push($node_list, $node_information);

				$number_of_listed_nodes++;
				if ($number_of_listed_nodes == $limit) break;

			}
		}	
		
		// export as csv if requested
		if ($format == "csv") {
			$DeimsCsvExportController = new DeimsCsvExportController();
			return $DeimsCsvExportController->createCSV($content_type, $node_list, $filename);
		}
		
		$reponse = new JsonResponse($node_list);
		
		if ($filename) $reponse->headers->set('Content-disposition', "attachment;filename={$filename}.json");
		
		return $reponse;
		
	}
  
}

