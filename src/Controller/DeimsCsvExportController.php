<?php

// https://www.metaltoad.com/blog/drupal-8-entity-api-cheat-sheet
// https://www.drupal.org/docs/8/api/routing-system/parameters-in-routes/using-parameters-in-routes

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

/**
 * This controller lists detailed information about each sensor as a JSON; only one record at a time based on the provided UUID
 */
class DeimsCsvExportController extends ControllerBase {
  
	public function createCSV($content_type, $node_list, $filename) {
			
		$delimiter = ";";
		$enclosure = '"';

		$fp = fopen('php://temp', 'r+b');
		$header = array("title", "id_prefix", "id_suffix", "changed");
		
		if ($content_type == "sites") {
			array_push($header, 'coordinates');
		}
		
		fputcsv($fp, $header, $delimiter, $enclosure);

		foreach ($node_list as $node) {
			$line = array($node["title"],$node["id"]['prefix'],$node["id"]['suffix'],$node["changed"]);
			
			if ($content_type == "sites") {
				array_push($line, $node["coordinates"]);
			}
			
			fputcsv($fp, $line, $delimiter, $enclosure);
		}
				
		rewind($fp);
		// ... read the entire line into a variable...
		$data = fread($fp, 1048576);
		fclose($fp);
		// ... and return the $data to the caller, with the trailing newline from fgets() removed.
		$result_csv = rtrim($data, "\n");

		$csv = new Response($result_csv);
        $csv->headers->set('Content-Type', 'Content-Encoding: UTF-8');
        $csv->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $csv->headers->set('Content-Type', 'application/vnd.ms-excel');
        $csv->headers->set('Content-Type', 'application/octet-stream');
        $csv->headers->set('Content-Type', 'application/force-download');
		
		if (!isset ($filename)) {
			$filename = 'result_list';
		}
		$file_string = 'attachment;filename="' . $filename . '.csv"';
		
        $csv->headers->set('Content-Disposition', $file_string);
		// necessary for excel to realise it's utf-8 ... stupid excel
		echo "\xEF\xBB\xBF";
		
		return $csv;
		
	}

}
