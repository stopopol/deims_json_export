<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each network as a JSON; only one record at a time based on the provided UUID
 */
class DeimsNetworkRecordController extends ControllerBase {
  
  public function parseFields($node) {
		
		// loading controller functions
		$DeimsFieldController = new DeimsFieldController();
		
		$uuid = $node->get('uuid')->value;
		$site_list_path = 'https://' . $_SERVER['SERVER_NAME'] . '/api/sites?network=' . $uuid . '&verified=';

		$information['title'] = $node->get('title')->value;
		$information['id']['prefix'] = 'https://deims.org/networks/';
		$information['id']['suffix'] = $uuid;
		$information['type'] = 'network';
		$information['created'] = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'Y-m-d\TH:i:sP');
		$information['changed'] = \Drupal::service('date.formatter')->format($node->getChangedTime(), 'custom', 'Y-m-d\TH:i:sP');

		$information['attributes']['abstract'] = $node->get('field_abstract')->value;
		$information['attributes']['url'] = $DeimsFieldController->parseRegularField($node->get('field_url'), "url");
		$information['attributes']['logo'] = $DeimsFieldController->parseImageField($node->get('field_images'));
		
		$information['attributes']['belongsTo'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_belongs_to'));
		$information['attributes']['consistsOf'] = $DeimsFieldController->parseEntityReferenceField($node->get('field_consists_of'));
		
		$information['attributes']['verifiedMemberSites']['title'] = 'API call for listing all verified member sites of the network';
		$information['attributes']['verifiedMemberSites']['href'] = $site_list_path . 'true';
		$information['attributes']['verifiedMemberSites']['type'] = 'application/json';
		$information['attributes']['unverifiedMemberSites']['title'] = 'API call for listing all unverified sites claiming to be part of the network';
		$information['attributes']['unverifiedMemberSites']['href'] = $site_list_path . 'false';
		$information['attributes']['unverifiedMemberSites']['type'] = 'application/json';
		
		return $information;
		
  }

}

