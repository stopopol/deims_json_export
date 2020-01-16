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

	$limit = \Drupal::request()->query->get('limit') ?: 'null';
	$offset = \Drupal::request()->query->get('offset') ?: 'null';
	$node_limit_number = (int)$limit;
	$node_offset_number = (int)$offset;

	// only return defined contents
	switch ($content_type) {
	
		case 'site':

			$DeimsFieldController = new DeimsFieldController();
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

			// site filter parameters
			$query_value_network = \Drupal::request()->query->get('network') ?: null;
			$query_value_sitecode = \Drupal::request()->query->get('sitecode') ?: null;
			$query_value_verified = \Drupal::request()->query->get('verified') ?: null;

			$i = 0;
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {

					$i++;
					// continue if offset
					if (is_int($node_offset_number)) {
						if ($i <= $node_offset_number) {
							continue;
						}
					}
					
					$node_information['title'] = $node->get('title')->value;
					$node_information['id']['prefix'] = 'https://deims.org/';
					$node_information['id']['suffix'] = $node->get('field_deims_id')->value;
					$node_information['coordinates'] = $node->get('field_coordinates')->value;
					$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
					$node_information['affiliation'] =  $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));

					if ($query_value_network || $query_value_sitecode || $query_value_verified) {
						$affiliation = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
					}

					// if a network id is provided, filter accordingly
					if ($query_value_network) {
						
						if ($affiliation) {
							$network_id_match = false;
							$verified_member_match = false;

							foreach ($affiliation as $network_item) {
								if ($network_item['network']['id']['suffix'] == $query_value_network) {
									$network_id_match = true;
								}
								else {
									continue;
								}
								// if verified parameter is provided, check if site is a verified network member
								if ($query_value_verified) {
									
									// need to cast true/false boolean to true/false string
									$verified_value_string = $network_item['verified'] ? 'true' : 'false';
								
									if ($query_value_verified == $verified_value_string) {
										$verified_member_match = true;
									}

								} 
							}
							if (!$network_id_match) {
								continue;
							}
							if ($query_value_verified) {
								if (!$verified_member_match) {
									continue;
								}
							}
						
						}
						else {
							continue;
						}
					}

					// austria
					// http://training.deims.org/api/site?network=d45c2690-dbef-4dbc-a742-26ea846edf28
					//$node_information['network'] = $network;
					//$node_information['siteCode'] = $siteCode;
					
					array_push($node_list, $node_information);
					
					if (is_int($node_limit_number)) {
						if ($i == $node_limit_number) {
							break;
						}
					}
					

				}
			}
			break;
		
		case 'activity':
		case 'sensor':
		case 'dataset':
			$nids = \Drupal::entityQuery('node')->condition('type', $content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			$i = 0;

			foreach ($nodes as $node) {

				if ($node->isPublished()) {

					$i++;
					// continue if offset
					if (is_int($node_offset_number)) {
						if ($i <= $node_offset_number) {
							continue;
						}
					}
					
					$node_information['title'] = $node->get('title')->value;
					$node_information['id']['prefix'] = "https://deims.org/" . $content_type . "/";
					$node_information['id']['suffix'] = $node->get('uuid')->value;
					$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

					array_push($node_list, $node_information);

					// end on limit
					if (is_int($node_limit_number)) {
						if ($i == $node_limit_number) {
							break;
						}
					}

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
    return new JsonResponse($node_list);
  }
}