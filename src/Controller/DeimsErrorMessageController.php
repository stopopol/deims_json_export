<?php

namespace Drupal\deims_json_export\Controller;

/**
 * This controller formats error messages. How very exciting
 */
 
class DeimsErrorMessageController {

	public function generateErrorMessage($status, $pointer, $details) {
	
		switch ($status) {
			case 400:
				$title = "Bad Request";
				break;
			case 404:
				$title = "Not Found";
				break;
		}
		
		return array (
			"errors" => [
				"status" => $status, 
				"source" => ["pointer" => $pointer], 
				"title" => $title,
				"detail" =>  $details 
			]
		);
  
	}

}