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
			$DeimsSiteReferenceFieldController = new DeimsSiteReferenceFieldController();
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('field_name')->value;
					$node_information['coordinates'] = $node->get('field_coordinates')->value;
					$node_information['deimsid'] = 'https://deims.org/' . $node->get('field_deims_id')->value;
					$node_information['affiliation'] = $DeimsSiteReferenceFieldController->parseEntityReferenceField($node->get('field_affiliation'));
					
					array_push($node_list, $node_information);
				}
			}
			break;
			
		case 'dataset':
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('title')->value;
					$node_information['uuid'] = $node->get('uuid')->value;
					
					array_push($node_list, $node_information);
				}
			} 
			break;
			
		case 'activity':
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('title')->value;
					$node_information['uuid'] = $node->get('uuid')->value;
					
					array_push($node_list, $node_information);
				}
			} 
			break;
		
		case 'sensor':
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->execute();
			$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
			
			foreach ($nodes as $node) {
				
				if ($node->isPublished()) {
					
					$node_information = [];
					$node_information['name'] = $node->get('title')->value;
					$node_information['uuid'] = $node->get('uuid')->value;
					
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
