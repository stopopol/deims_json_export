<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet

// EntityReferenceRevisionsFieldItemList

namespace Drupal\deims_site_list\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Implementing our example JSON api.
 */
class DeimsSiteListController {

  /**
   * Callback for the API.
   */
  public function renderApi() {
    return new JsonResponse($this->getResults());
  }

  /**
   * A helper function returning results.
   */
  public function getResults() {
	
	$site_list = [];
	
	$nids = \Drupal::entityQuery('node')->condition('type','site')->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids); 
 	
	foreach ($nodes as $node) {
		
		if ($node->isPublished()) {
			
			$temp_array = [];
			
			$temp_array['name'] = $node->get('title')->value;
			$temp_array['coordinates'] = $node->get('field_coordinates')->value;
			$temp_array['deimsid'] = 'https://deims.org/' . $node->get('field_deims_id')->value;
				

			if (sizeof ($node->get('field_affiliation')) == 1) {
				$paragraph = $node->get('field_affiliation');
				if ($paragraph->entity->field_network_name->target_id) {
					$network_name_tid = $paragraph->entity->field_network_name->target_id;
					$full_taxonomy_term_object = taxonomy_term_load($network_name_tid);
					
					$paragraph_array['name'] = $full_taxonomy_term_object->getName();
					$paragraph_array['code'] = $paragraph->entity->field_network_specific_site_code->value;
					if ($paragraph->entity->field_network_verified->value == 1) {
						$paragraph_array['verified'] = true;
					}
					else {
						$paragraph_array['verified'] = false;
					}
					$temp_array['affiliation'] = $paragraph_array;				
				}
				else {
					$temp_array['affiliation'] = false;
				}
				
			}
			// case for multiple paragraphs
			else {

				$paragraph_collection = [];
				$paragraph = $node->get('field_affiliation');
				
				foreach ($paragraph->referencedEntities() as $paragraph_item) {
					$network_item = [];
					if ($paragraph_item->field_network_name->target_id) {
						$network_name_tid = $paragraph_item->field_network_name->target_id;
						$full_taxonomy_term_object = taxonomy_term_load($network_name_tid);
						
						$network_item['name'] = $full_taxonomy_term_object->getName();
						$network_item['code'] = $paragraph_item->field_network_specific_site_code->value;
						if ($paragraph_item->field_network_verified->value == 1) {
							$network_item['verified'] = true;
						}
						else {
							$network_item['verified'] = false;
						}
						array_push($paragraph_collection, $network_item);
					}
					
				}
				sort($paragraph_collection);
				$temp_array['affiliation'] = $paragraph_collection;
			}
			array_push($site_list, $temp_array);
		}
	} 
    return $site_list;
  }

}