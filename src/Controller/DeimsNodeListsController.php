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
  public function renderApi($content_type) {
    return new JsonResponse($this->getResults($content_type));
  }

  /**
   * A helper function returning results.
   */
  public function getResults($content_type) {

	$node_list = [];

	// only return defined contents
	switch ($content_type) {
	
		case 'site':
			$DeimsFieldController = new DeimsFieldController();
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('field_name')->value;
					$node_information['deimsid']['prefix'] = 'https://deims.org/';
					$node_information['deimsid']['id'] = $node->get('field_deims_id')->value;
					$node_information['coordinates'] = $node->get('field_coordinates')->value;
					$node_information['coordinates'] = $node->get('field_coordinates')->value;
					$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
					$node_information['affiliation'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_affiliation'));
					
					array_push($node_list, $node_information);
				}
			}
			break;
		
		case 'activity':
		case 'sensor':
		case 'dataset':
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('title')->value;
					$node_information['path']['prefix'] = "https://deims.org/" . $content_type . "/";
					$node_information['path']['id'] = $node->get('uuid')->value;
					$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');

					array_push($node_list, $node_information);
				}
			} 
			break;

		default:
			$error_message = [];
			$error_message['status'] = "404";
			$error_message['source'] = ["pointer" => "/api/{type}"];
			$error_message['title'] = 'Resource not found';
			$error_message['detail'] = 'This is not a valid request :(';
			$node_list['errors'] = $error_message;
	}
	
    return $node_list;
  }

}
