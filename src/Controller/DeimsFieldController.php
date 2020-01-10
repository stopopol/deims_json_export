<?php

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

class DeimsFieldController extends ControllerBase {
	/*
	 * Function specifically for parsing information entity reference fields of DEIMS-SDR
	 *
	 * Requires a node object as input and the fieldname
	 */
	public function parseEntityReferenceField($field, $single_value_field = null) {

		if (count($field) > 0) {
			$data_values = array();
			// case for single reference
			if (count($field) == 1) {
				array_push($data_values, $this->parseEntityFieldContent($field->entity));
				if ($single_value_field) {
					$data_values=reset($data_values);
				}
			}
			// case for multiple references
			else {
				foreach ($field->referencedEntities() as $RefEntity) {
					array_push($data_values, $this->parseEntityFieldContent($RefEntity));
				}
				sort($data_values);
			}
			return $data_values;
		}

	}

	public function parseRegularField($field, $field_type, $single_value_field = null) {
		if (count($field) > 0) {
			$data_values = array();
			// single-value case
			if (count($field) == 1) {
				switch ($field_type) {
					case "multiText":
						array_push($data_values, $field->value);
						break;
					case "url":
						array_push($data_values, array('title'=>$field->title,'uri'=>$field->uri));
						break;
				}
				
			}
			// multi-value case
			else {
				foreach ($field as $item) {
					switch ($field_type) {
						case "multiText":
							array_push($data_values, $item->value);
							break;
						case "url":
							array_push($data_values, array('title'=>$item->title,'uri'=>$item->uri));
							break;
					}
				}
			}
			// only return result when field is filled, else empty
			return $data_values;
		}
		
	}

	/*
	 * Function that parses the fields within a referenced field
	 *
	 * Requires an entity as input and returns an array with the formatted values
	 */
	public function parseTextListField($node, $fieldname, $single_value_field = null) {

		if (count($node->get($fieldname)) > 0) {
			$data_values_list_labels = $node->getFieldDefinition($fieldname)->getSetting('allowed_values');
			$data_values = array();
			// single-value case
			if (count($node->get($fieldname)) == 1) {
				// case if this is a field with a 0:1 cardinality
				if ($single_value_field) {
					$data_values = $data_values_list_labels[$node->get($fieldname)->value];
				}
				else {
					array_push($data_values, $data_values_list_labels[$node->get($fieldname)->value]);
				}
			}
			// multi-value case
			else {
				foreach ($node->get($fieldname) as $item) {
					array_push($data_values, $data_values_list_labels[$item->value]);
				}
			}
			return $data_values;
		}

	}
	
	// print list of all resources that are related to record
	public function findRelatedResources($nids) {
		$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
		$node_list = array();
		foreach ($nodes as $node) {
			if ($node->isPublished()) {
				$node_information = [];
				$content_type = $node->bundle();
				
				if ($content_type == 'observation_location') {
					continue;
				}
				
				switch ($content_type) {
					case 'activity':
						$node_information['id']['prefix'] = 'https://deims.org/activity/';
						break;
					case 'dataset':
						$node_information['id']['prefix'] = 'https://deims.org/dataset/';
						break;
					case 'sensor':
						$node_information['id']['prefix'] = 'https://deims.org/sensor/';
						break;	
				}
				$node_information['title'] = $node->get('title')->value;
				$node_information['id']['suffix'] = $node->get('field_uuid')->value;
				$node_information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'html_datetime');
				
				array_push($node_list, $node_information);
			} 
		}
		
		if(!empty($node_list)) {
			return $node_list;
		}
		else {
			return null;
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
				// to do: case for content type "dataset" and "activity"
				case 'activity':
					$RefEntity_item['type'] = 'activity';
					$RefEntity_item['name'] = $RefEntity->field_name->value;
					$RefEntity_item['id']['prefix'] = 'https://deims.org/activity/';
					$RefEntity_item['id']['suffix'] = $RefEntity->field_uuid->value;
					break;
				case 'dataset':
					$RefEntity_item['type'] = 'dataset';
					$RefEntity_item['name'] = $RefEntity->field_name->value;
					$RefEntity_item['id']['prefix'] = 'https://deims.org/dataset/';
					$RefEntity_item['id']['suffix'] = $RefEntity->field_uuid->value;
					break;
				// case for content type 'site'
				case 'site':
					$RefEntity_item['type'] = 'site';
					$RefEntity_item['name'] = $RefEntity->field_name->value;
					$RefEntity_item['id']['prefix'] = 'https://deims.org/';
					$RefEntity_item['id']['suffix'] = $RefEntity->field_deims_id->value;
					break;
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
						$RefEntity_item['network']['name'] = $RefEntity->field_network->entity->getTitle();
						$RefEntity_item['network']['id']['prefix'] = 'https://deims.org/network/';
						$RefEntity_item['network']['id']['suffix'] = $RefEntity->get('uuid')->value;
						$RefEntity_item['siteCode'] = $RefEntity->field_network_specific_site_code->value;
						$RefEntity_item['verified'] = $RefEntity->field_network_verified->value == 1 ? true : false;
					}
					break;
				// case for paragraphs of type 'protection_programme'
				case 'protection_programme':
					// always just 0:1 values
					$RefEntity_item['name'] = $RefEntity->field_protection_programme->entity->getName();
					$data_values_list_labels = $RefEntity->getFieldDefinition('field_protection_programme_cover')->getSetting('allowed_values');
					$RefEntity_item['cover'] = $data_values_list_labels[$RefEntity->field_protection_programme_cover->value];
					$RefEntity_item['notes'] = $RefEntity->field_protection_programme_notes->value;
					break;
				// paragraphs of type 'observation'
				case 'observation':
					$RefEntity_item['property'] = $RefEntity->field_media_monitored->entity->getName();
					$RefEntity_item['unitOfMeasurement'] = 'not implemented';
					break;
				// case for 'data_source'; currently incomplete
				case 'data_source':
					$RefEntity_item['title'] = $RefEntity->getTitle();
					break;
				case 'observation_location':
					$RefEntity_item['boundaries'] = $RefEntity->field_boundaries->value;
					$RefEntity_item['abstract'] = $RefEntity->field_abstract->value;
					$RefEntity_item['elevation']['min'] = $RefEntity->field_elevation_min->value;
					$RefEntity_item['elevation']['max'] = $RefEntity->field_elevation_max->value;
					$RefEntity_item['elevation']['unit'] = 'msl';
					break;
				case 'online_locator':
					$RefEntity_item['function'] = $RefEntity->field_distribution_function->value;
					$RefEntity_item['url']['title'] = $RefEntity->field_distribution_url->title;
					$RefEntity_item['url']['value'] = $RefEntity->field_distribution_url->uri;
					$RefEntity_item['email'] = $RefEntity->field_email->value;
					break;
				// case for taxonomies without uri fields
				case 'spatial_design':
				case 'spatial_scale':
				case 'temporal_resolution':
				case 'data_policy':
				case 'projects':
				case 'eunis_habitat':
				case 'lter_controlled_vocabulary':
				case 'infrastructure':
				case 'ecosystem_types_and_land_use':
				case 'sensortype':
					$RefEntity_item['label'] = $RefEntity->label();
					$RefEntity_item['uri'] = null;
					break;
				// case for taxonomies with uri fields
				case 'inspire_data_themes':
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
