<?php

namespace Drupal\deims_json_export\Controller;

/**
 *  get all URIs of terms in a taxonomy (requires a URI field to work)
 */
 
class DeimsTaxonomyInformationController {
	
	public function get_taxonomy_uris($vocabulary_name) {

		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary_name);
		$list_of_uris = array();
		foreach ($terms as $term) {
			array_push($list_of_uris, $term->field_uri);
		}
		return $list_of_uris;

	}
	
}
