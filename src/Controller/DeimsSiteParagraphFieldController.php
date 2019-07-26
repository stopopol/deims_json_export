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
		// case for empty field or single paragraph
		if (sizeof ($node->get('field_affiliation')) == 1) {		
			if ($node->get('field_affiliation')->entity->field_network_name->target_id) {
				$paragraph_item = $node->get('field_affiliation')->entity;				
				$network_item = DeimsSiteParagraphFieldController::parseAffiliationFields($paragraph_item);
				array_push($paragraph_collection,$network_item);
			}
			else {
				return null;
			}
		}
		// case for multiple paragraphs
		else {
			foreach ($node->get('field_affiliation')->referencedEntities() as $paragraph_item) {
				if ($paragraph_item->field_network_name->target_id) {
					$network_item = DeimsSiteParagraphFieldController::parseAffiliationFields($paragraph_item);
					array_push($paragraph_collection, $network_item);
				}
			}
			sort($paragraph_collection);
		}
		return $paragraph_collection;
	}
	
	/*
	 * Function that parses the fields within a affiliation paragraph field
	 *
	 * Requires a paragraph entity as input and returns and array with the formatted values
	 */
	public function parseAffiliationFields($paragraph_item) {
		$network_item = [];
		$network_name_tid = $paragraph_item->field_network_name->target_id;
		$full_taxonomy_term_object = taxonomy_term_load($network_name_tid);
					
		if ($paragraph_item->field_network_verified->value == 1) {
			$verified = true;
		}
		else {
			$verified = false;
		}		
		$network_item['name'] = $full_taxonomy_term_object->getName();
		$network_item['code'] = $paragraph_item->field_network_specific_site_code->value;
		$network_item['verified'] = $verified;
					
		return $network_item;
	}
}