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

	// only return defined contents
	switch ($content_type) {
	
		case 'site':

			$DeimsFieldController = new DeimsFieldController();
			$nids = \Drupal::entityQuery('node')->condition('type', $content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

			// site filter parameters
			$query_value_network = \Drupal::request()->query->get('network') ?: null;
			$query_value_sitecode = \Drupal::request()->query->get('sitecode') ?: null;
			$query_value_verified = \Drupal::request()->query->get('verified') ?: null;

			$number_of_parsed_nodes = 0;
			$number_of_listed_nodes = 0;
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {

					$search_criteria_matched = true;
					// check for network-related filters
					if ($query_value_network || $query_value_sitecode || $query_value_verified) {
						$affiliation = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));

						if ($affiliation) {

							if ($query_value_sitecode) {
								// Site Code Filter	
								$site_code_matched = null;	
								foreach ($affiliation as $network_item) {
									if (is_int(stripos($network_item['siteCode'], $query_value_sitecode)))	{
										$site_code_matched = true; // case insensitive string
									}
								}
							}
							
							// if a network id is provided, filter accordingly
							if ($query_value_network) {
								
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

							} 

						} else $search_criteria_matched = false;
							// check for network-related filter}

						if ($query_value_network && !isset($network_id_match))  $search_criteria_matched = false;
						if ($query_value_sitecode && !isset($site_code_matched)) $search_criteria_matched = false;
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
						//$node_information['affiliation'] =  $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
		
						array_push($node_list, $node_information);
						$number_of_listed_nodes++;
					}

					if ($limit && $number_of_listed_nodes == $limit)	break;					
				}
			}
			break;
		
		case 'activity':
		case 'sensor':
		case 'dataset':
			$nids = \Drupal::entityQuery('node')->condition('type', $content_type)->execute();
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
					$node_information['id']['prefix'] = "https://deims.org/" . $content_type . "/";
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
			$error_message['detail'] = "This is not a valid request because DEIMS-SDR doesn't have a resource type with this name :(";
			$node_list['errors'] = $error_message;
	}

	// case for csv export
	if ($format == "csv" && !isset($node_list['errors'])) {

		$fp = fopen('php://output', 'w');
		fputcsv($fp, array("title","id_prefix","id_suffix","coordinates","changed"), ";");

		foreach ($node_list as $fields) {
			$splitted_fields = array($fields["title"],$fields["id"]['prefix'],$fields["id"]['suffix'],$fields["coordinates"],$fields["changed"]);
			fputcsv($fp, $splitted_fields, ";"); 
		}
		return new Response();

	}
    return new JsonResponse($node_list);
  }
}
