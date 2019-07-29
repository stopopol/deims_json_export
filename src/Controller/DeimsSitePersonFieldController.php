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
		$person_collection = [];
		$DeimsSitePersonFieldController = new DeimsSitePersonFieldController();
		// case for empty field or single person
		if (sizeof ($field) == 1) {
			if ($field->entity) {	
				$person_item = $DeimsSitePersonFieldController->parsePersonFields($field->entity);
				array_push($person_collection, $person_item);
			}
			else {
				return null;
			}
		}
		// case for multiple person references
		else {
			foreach ($field->referencedEntities() as $person) {
				if ($person) {
					$person_item = $DeimsSitePersonFieldController->parsePersonFields($person);
					array_push($person_collection, $person_item);
				}
			}
			sort($person_collection);
		} 
		return $person_collection;
	}
	
	/*
	 * Function that parses the fields within a person field
	 *
	 * Requires a paragraph entity as input and returns and array with the formatted values
	 */
	public function parsePersonFields($person) {
		
		$person_item = [];
		$person_item['name'] = $person->field_person_name->given . ' ' . $person->field_person_name->family;
		$person_item['email'] = $person->field_email->value;
					
		return $person_item;
	}
}