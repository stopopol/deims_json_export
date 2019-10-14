<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

class DeimsSiteReferenceFieldController extends ControllerBase {
	/*
	 * Function specifically for parsing information in 'person' fields of DEIMS-SDR
	 *
	 * Requires a node object as input and the fieldname
	 */
	public function parseEntityReferenceField($field) {
		$RefEntity_collection = [];
		$DeimsSiteReferenceFieldController = new DeimsSiteReferenceFieldController();
		
		// case for empty field or single reference
		if (sizeof ($field) == 1) {
			array_push($RefEntity_collection, $DeimsSiteReferenceFieldController->parseEntityFieldContent($field->entity));
		}
		// case for multiple references
		else {
			foreach ($field->referencedEntities() as $RefEntity) {
				array_push($RefEntity_collection, $DeimsSiteReferenceFieldController->parseEntityFieldContent($RefEntity));
			}
			sort($RefEntity_collection);
		}
		// filter empty values in array, because there are cases when a node is insufficiently filled in or there are drupal leftovers due to the form API
		$sanitized_RefEntity_collection = array_values(array_filter($RefEntity_collection));
		if (!empty($sanitized_RefEntity_collection)) {
			return $sanitized_RefEntity_collection;
		}
		
	}
	
	/*
	 * Function that parses the fields within a referenced field
	 *
	 * Requires an entity as input and returns an array with the formatted values
	 */
	public function parseEntityFieldContent($RefEntity) {
		
		if ($RefEntity) {
			$RefEntity_item = [];
			switch ($RefEntity->bundle()) {
				// case for content type 'person'
				case 'person':
					$RefEntity_item['type'] = 'person';
					$RefEntity_item['name'] = $RefEntity->field_person_name->given . ' ' . $RefEntity->field_person_name->family;
					$RefEntity_item['email'] = $RefEntity->field_email->value;

					break;
				
				// case for content type 'organisation'
				case 'organisation':
					$RefEntity_item['type'] = 'organisation';
					$RefEntity_item['name'] = $RefEntity->field_name->value;
					foreach ($RefEntity->field_url as $url) {
						$RefEntity_item['url'] = $url -> uri;
					}
					break;
				// case for paragraphs of type 'network_pg'
				case 'network_pg':
					if ($RefEntity->field_network->entity) {
						$RefEntity_item['network'] =  $RefEntity->field_network->entity->getTitle();
						$RefEntity_item['code'] = $RefEntity->field_network_specific_site_code->value;
						$RefEntity_item['verified'] = $RefEntity->field_network_verified->value == 1 ? true : false;
					}
					break;
				// cases for taxonomies
				case 'ecosystem_types_and_land_use':
					$RefEntity_item['label'] = $RefEntity->label();
					break;
				case 'parameters':
				case 'research_topics':
					$RefEntity_item['label'] = $RefEntity->label();
					$RefEntity_item['uri'] = $RefEntity->field_uri->uri;
					break;
			}
			return $RefEntity_item;
		}		
	}
}
