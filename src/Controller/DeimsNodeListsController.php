<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
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

	// get integer values of parameters limit and offset
	$limit = \Drupal::request()->query->get('limit') ? ((int)\Drupal::request()->query->get('limit')) : null;
	$offset = \Drupal::request()->query->get('offset') ? ((int)\Drupal::request()->query->get('offset')) : null;
	$format = \Drupal::request()->query->get('format') ? ((int)\Drupal::request()->query->get('format')) : null;

	// only return defined content types
	switch ($content_type) {
	
		case 'sites':

			$DeimsFieldController = new DeimsFieldController();
			// for future filters refer to 
			// https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21Query%21QueryInterface.php/function/QueryInterface%3A%3Acondition/8.2.x
			
			// site filter parameters
			$query_value_network = \Drupal::request()->query->get('network') ?: null;
			$query_value_sitecode = \Drupal::request()->query->get('sitecode') ?: null;
			$query_value_verified = \Drupal::request()->query->get('verified') ?: null;
			$query_value_observedProperties = \Drupal::request()->query->get('observedproperty') ?: null;
						
			$query = \Drupal::entityQuery('node');
			$query->condition('type', 'site');
			
			// if filters are provided, add additional filter conditions
			if ($query_value_observedProperties) {
				$query->condition('field_parameters.entity:taxonomy_term.field_uri', $query_value_observedProperties);
			}
			
			if ($query_value_network) {
				$query->condition('field_affiliation.entity:paragraph.field_network.entity:node.uuid', $query_value_network);			
			}
			
			if ($query_value_sitecode) {	
				$query->condition('field_affiliation.entity:paragraph.field_network_specific_site_code', $query_value_sitecode, 'LIKE');
			}
			
			$nids = $query->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

			$number_of_parsed_nodes = 0;
			$number_of_listed_nodes = 0;
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {

					$search_criteria_matched = true;
					// check for network-related filters
					if ($query_value_network && $query_value_verified) {
						$affiliation = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
							
							// if a network id is provided, filter accordingly
							$network_id_match = null;
							$verified_member_match = null;
							foreach ($affiliation as $network_item) {
									
								if ($network_item['network']['id']['suffix'] == $query_value_network) {
									$network_id_match = true;
									// if verified parameter is provided, check if site is a verified network member
									if ($query_value_verified) {
										// need to cast true/false boolean to true/false string
										$verified_value_string = $network_item['verified'] ? 'true' : 'false';
										if ($query_value_verified == $verified_value_string) $verified_member_match = true;
									} 
								}
							}	

						if ($query_value_network && !isset($network_id_match))  $search_criteria_matched = false;
						if ($query_value_verified && !isset($verified_member_match)) $search_criteria_matched = false;

					}

					if ($search_criteria_matched == true) {

						// offset not working properly
						if ($offset && ($number_of_parsed_nodes < $offset)) {
							$number_of_parsed_nodes++;
							continue;
						} 

						// get record values
						$node_information['title'] = $node->get('title')->value;
						$node_information['id']['prefix'] = 'https://deims.org/';
						$node_information['id']['suffix'] = $node->get('field_deims_id')->value;
						$node_information['coordinates'] = $node->get('field_coordinates')->value;
						$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
		
						array_push($node_list, $node_information);
						$number_of_listed_nodes++;
					}

					if ($limit && $number_of_listed_nodes == $limit)	break;					
				}
			}
			break;
		
		case 'activities':
		case 'sensors':
		case 'datasets':
		case 'locations':
		
			if ($content_type == 'activities') {
				$entity_machine_name = 'activity';
				$landing_page_label = 'activity';
			}
			if ($content_type == 'sensors') {
				$entity_machine_name = 'sensor';
				$landing_page_label = 'sensors';
			}
			if ($content_type == 'locations') {
				$entity_machine_name = 'observation_location';
				$landing_page_label = 'locations';
			}
			if ($content_type == 'datasets') {
				$entity_machine_name = 'dataset';
				$landing_page_label = 'dataset';
			}

			$nids = \Drupal::entityQuery('node')->condition('type', $entity_machine_name)->execute();
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
					$node_information['id']['prefix'] = "https://deims.org/" . $landing_page_label . "/";
					$node_information['id']['suffix'] = $node->get('uuid')->value;
					$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

					array_push($node_list, $node_information);

					$number_of_listed_nodes++;
					if ($number_of_listed_nodes == $limit) break;

				}
			} 
			break;

		default:
			$error_message['status'] = "404";
			$error_message['source'] = ["pointer" => "/api/{type}"];
			$error_message['title'] = 'Resource type not found';
			$error_message['detail'] = "This is not a valid request because DEIMS-SDR doesn't have a resource type that is called '" . $content_type . "' :(";
			$node_list['errors'] = $error_message;
	}

	// case for csv export
	if ($format == "csv" && !isset($node_list['errors'])) {
			
		$delimiter = ";";
		$enclosure = '"';

		$fp = fopen('php://temp', 'r+b');
		$header = array("title", "id_prefix", "id_suffix", "coordinates", "changed");
		fputcsv($fp, $header, $delimiter, $enclosure);

		foreach ($node_list as $node) {
			$line = array($node["title"],$node["id"]['prefix'],$node["id"]['suffix'],$node["coordinates"],$node["changed"]);
			fputcsv($fp, $line, $delimiter, $enclosure);
		}
				
		rewind($fp);
		// ... read the entire line into a variable...
		$data = fread($fp, 1048576);
		fclose($fp);
		// ... and return the $data to the caller, with the trailing newline from fgets() removed.
		$result_csv = rtrim($data, "\n");

		$response = new Response($result_csv);
        $response->headers->set('Content-Type', 'Content-Encoding: UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment;filename="result_list.csv"');
		// necessary for excel to realise it's utf-8 ... stupid excel
		echo "\xEF\xBB\xBF";
        
		return $response;

	}
    return new JsonResponse($node_list);
  }
  
}
