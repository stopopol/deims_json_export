<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

class DeimsSitePersonFieldController extends ControllerBase {
	/*
	 * Function specifically for parsing information in 'person' fields of DEIMS-SDR
	 *
	 * Requires a node object as input and the fieldname
	 */
	public function parsePersonField($field) {
		$RefEntity_collection = [];
		$DeimsSitePersonFieldController = new DeimsSitePersonFieldController();
		
		// case for empty field or single person
		if (sizeof ($field) == 1) {
			if ($field->entity) {	
				$RefEntity_item = $DeimsSitePersonFieldController->parseRefEntityField($field->entity);
				array_push($RefEntity_collection, $RefEntity_item);
			}
			else {
				return null;
			}
		}
		// case for multiple person references
		else {
			foreach ($field->referencedEntities() as $RefEntity) {
				if ($RefEntity) {
					$RefEntity_item = $DeimsSitePersonFieldController->parseRefEntityField($RefEntity);
					array_push($RefEntity_collection, $RefEntity_item);
				}
			}
			sort($RefEntity_collection);
		} 
		return $RefEntity_collection;
	}
	
	/*
	 * Function that parses the fields within a person field
	 *
	 * Requires a paragraph entity as input and returns and array with the formatted values
	 */
	public function parseRefEntityField($RefEntity) {
		
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
		
		return $RefEntity_item;
	}
}