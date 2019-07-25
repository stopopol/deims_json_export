<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

class DeimsSiteParagraphFieldController extends ControllerBase {
	/*
	 * Function specifically for parsing information in the "affiliation" paragraphs field of DEIMS-SDR
	 *
	 * Requires a node object as input and will look for an "affiliation" field within the node
	 */
	public function parseAffiliation($node) {
		$paragraph_collection = [];
		if (sizeof ($node->get('field_affiliation')) == 1) {
			$paragraph = $node->get('field_affiliation');
				
			if ($paragraph->entity->field_network_name->target_id) {
				$network_item = [];
				
				$network_name_tid = $paragraph->entity->field_network_name->target_id;
				$full_taxonomy_term_object = taxonomy_term_load($network_name_tid);
						
				if ($paragraph->entity->field_network_verified->value == 1) {
					$verified = true;
				}
				else {
					$verified = false;
				}
					
				$network_item['name'] = $full_taxonomy_term_object->getName();
				$network_item['code'] = $paragraph->entity->field_network_specific_site_code->value;
				$network_item['verified'] = $verified;
				
				// to make sure that the structure of the json object is always the same and indepedendant from the actual number of networks of a site
				array_push($paragraph_collection,$network_item);
				return $paragraph_collection;				
			}
			else {
				return false;
			}
			
		}
		// case for multiple paragraphs
		else {
			
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
			return $paragraph_collection;
		}
	}
}