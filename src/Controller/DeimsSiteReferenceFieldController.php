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
		
		// case for empty field or single person
		if (sizeof ($field) == 1) {
			if ($field->entity) {	
				$RefEntity_item = $DeimsSiteReferenceFieldController->parseEntityFieldContent($field->entity);
				if ($RefEntity_item) {
					array_push($RefEntity_collection, $RefEntity_item);
				}
			}
		}
		// case for multiple person references
		else {
			foreach ($field->referencedEntities() as $RefEntity) {
				if ($RefEntity) {
					$RefEntity_item = $DeimsSiteReferenceFieldController->parseEntityFieldContent($RefEntity);
					array_push($RefEntity_collection, $RefEntity_item);
				}
			}
			sort($RefEntity_collection);
		}
		
		return (!empty($RefEntity_collection)) ? $RefEntity_collection : null;
	}
	
	/*
	 * Function that parses the fields within a referenced field
	 *
	 * Requires an entity as input and returns an array with the formatted values
	 */
	public function parseEntityFieldContent($RefEntity) {
		
		$RefEntity_item = [];
		
		// case for content type 'person'
		if ($RefEntity->bundle() == 'person') {
			$RefEntity_item['type'] = 'person';
			$RefEntity_item['name'] = $RefEntity->field_person_name->given . ' ' . $RefEntity->field_person_name->family;
			$RefEntity_item['email'] = $RefEntity->field_email->value;
		}
		
		// case for content type 'organisation'
		if ($RefEntity->bundle() == 'organisation') {
			$RefEntity_item['type'] = 'organisation';
			$RefEntity_item['name'] = $RefEntity->field_name->value;
			foreach ($RefEntity->field_url as $url) {
				$RefEntity_item['url'] = $url -> uri;
			}
		}
		
		// case for paragraphs of type 'network'
		if ($RefEntity->bundle() == 'network') {
			if ($RefEntity->field_network_name->target_id) {
				$RefEntity_item['name'] =  taxonomy_term_load($RefEntity->field_network_name->target_id)->getName();
				$RefEntity_item['code'] = $RefEntity->field_network_specific_site_code->value;
				$RefEntity_item['verified'] = $RefEntity->field_network_verified->value == 1 ? true : false;
			}
		}
		
		return $RefEntity_item;
	}
}