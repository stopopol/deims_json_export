<?php

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

class DEIMSIso19139Controller extends ControllerBase {
    public function Iso19139Response($record_information) {
		
        $doc = new \DOMDocument("1.0", "UTF-8");
        $doc->formatOutput = true;

        // Root element
        $root = $doc->createElementNS("http://www.isotc211.org/2005/gmd","gmd:MD_Metadata");
        $doc->appendChild($root);

        // Namespaces
        $root->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:gco","http://www.isotc211.org/2005/gco");
        $root->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $root->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:gml","http://www.opengis.net/gml");
        $root->setAttribute("xsi:schemaLocation","http://www.isotc211.org/2005/gmd http://schemas.opengis.net/iso/19139/20070417/gmd/gmd.xsd");

        // fileIdentifier
        $fileIdentifier = $doc->createElement("gmd:fileIdentifier");
        $charStr = $doc->createElement("gco:CharacterString",$record_information["id"]["suffix"]);
        $fileIdentifier->appendChild($charStr);
        $root->appendChild($fileIdentifier);

        // language
        $language = $doc->createElement("gmd:language");
        $langCode = $doc->createElement("gmd:LanguageCode", "eng");
        $langCode->setAttribute("codeList","http://www.loc.gov/standards/iso639-2/");
        $langCode->setAttribute("codeListValue", "eng");
        $language->appendChild($langCode);
        $root->appendChild($language);

        // characterSet
        $charSet = $doc->createElement("gmd:characterSet");
        $charCode = $doc->createElement("gmd:MD_CharacterSetCode", "utf8");
        $charCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#MD_CharacterSetCode");
        $charCode->setAttribute("codeListValue", "utf8");
        $charSet->appendChild($charCode);
        $root->appendChild($charSet);

        // hierarchyLevel
        $hierarchy = $doc->createElement("gmd:hierarchyLevel");
        $scopeCode = $doc->createElement("gmd:MD_ScopeCode", "dataset");
        $scopeCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#MD_ScopeCode");
        $scopeCode->setAttribute("codeListValue", "dataset");
        $hierarchy->appendChild($scopeCode);
        $root->appendChild($hierarchy);
		
		// Create <gmd:dataQualityInfo>
		$dataQualityInfo = $doc->createElement("gmd:dataQualityInfo");
		$dqDataQuality = $doc->createElement("gmd:DQ_DataQuality");

		// <gmd:lineage>
		$lineage = $doc->createElement("gmd:lineage");
		$liLineage = $doc->createElement("gmd:LI_Lineage");
		$statement = $doc->createElement("gmd:statement");
		$statementText = "This metadata records was created using informaton from DEIMS-SDR (deims.org) and is based on user input via forms.";
		$charString = $doc->createElement("gco:CharacterString");
		$charString->appendChild($doc->createTextNode($statementText));
		$statement->appendChild($charString);
		$liLineage->appendChild($statement);
		$lineage->appendChild($liLineage);
		$dqDataQuality->appendChild($lineage);
		$dataQualityInfo->appendChild($dqDataQuality);
		$root->appendChild($dataQualityInfo);

        // contact
        if (!empty($record_information["attributes"]["contact"]["siteManager"])) {
            foreach ($record_information["attributes"]["contact"]["siteManager"] as $person) {
				$contact = $doc->createElement("gmd:contact");
                $rp = $doc->createElement("gmd:CI_ResponsibleParty");
                $name = $doc->createElement("gmd:individualName");
                $nameText = $doc->createTextNode($person["name"] ?? "");
                $name->appendChild($nameText);
                $rp->appendChild($name);
                // role is always mandatory for CI_ResponsibleParty
                $role = $doc->createElement("gmd:role");
                $roleCode = $doc->createElement("gmd:CI_RoleCode","pointOfContact");
                $roleCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#CI_RoleCode");
                $roleCode->setAttribute("codeListValue", "pointOfContact");
                $role->appendChild($roleCode);
                $rp->appendChild($role);
                $contact->appendChild($rp);
				$root->appendChild($contact);
            }
        }

        if (!empty($record_information["attributes"]["contact"]["operatingOrganisation"])) {
            foreach ($record_information["attributes"]["contact"]["operatingOrganisation"] as $organisation) {
                $contact = $doc->createElement("gmd:contact");
				$rp = $doc->createElement("gmd:CI_ResponsibleParty");
                $name = $doc->createElement("gmd:organisationName");
                $nameText = $doc->createTextNode($organisation["name"] ?? "");
                $name->appendChild($nameText);
                $rp->appendChild($name);
                // role is always mandatory for CI_ResponsibleParty
                $role = $doc->createElement("gmd:role");
                $roleCode = $doc->createElement("gmd:CI_RoleCode", "pointOfContact");
                $roleCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#CI_RoleCode");
                $roleCode->setAttribute("codeListValue", "pointOfContact");
                $role->appendChild($roleCode);
                $rp->appendChild($role);
                $contact->appendChild($rp);
				$root->appendChild($contact);
            }
        }

        // dateStamp
        $dateStamp = $doc->createElement("gmd:dateStamp");
		$dateStamp->appendChild($doc->createElement("gco:Date", date("Y-m-d")));
        $root->appendChild($dateStamp);

        // metadataStandard
        $stdName = $doc->createElement("gmd:metadataStandardName");
        $stdName->appendChild($doc->createElement("gco:CharacterString", "ISO 19115:2003/19139"));
        $root->appendChild($stdName);

        $stdVersion = $doc->createElement("gmd:metadataStandardVersion");
        $stdVersion->appendChild($doc->createElement("gco:CharacterString", "1.0"));
        $root->appendChild($stdVersion);

        // identificationInfo
        $ident = $doc->createElement("gmd:identificationInfo");
        $dataId = $doc->createElement("gmd:MD_DataIdentification");

        // title
        $citation = $doc->createElement("gmd:citation");
        $ciCitation = $doc->createElement("gmd:CI_Citation");
        $title = $doc->createElement("gmd:title");
        $title->appendChild($doc->createElement("gco:CharacterString",$record_information["title"]));
        $ciCitation->appendChild($title);

        // citation > date
        $ciDate = $doc->createElement("gmd:CI_Date");

        $date = $doc->createElement("gmd:date");
        $dateVal = $doc->createElement("gco:Date",$record_information["changed"]);
        $date->appendChild($dateVal);
        $ciDate->appendChild($date);

        $dateType = $doc->createElement("gmd:dateType");
        $dtCode = $doc->createElement("gmd:CI_DateTypeCode", "creation");
        $dtCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#CI_DateTypeCode");
        $dtCode->setAttribute("codeListValue", "creation");
        $dateType->appendChild($dtCode);
        $ciDate->appendChild($dateType);
        $ciCitation->appendChild($doc->createElement("gmd:date"))->appendChild($ciDate);
        $citation->appendChild($ciCitation);
        $dataId->appendChild($citation);

        // Create <gmd:topicCategory>
        $topicCategory = $doc->createElement("gmd:topicCategory");
        $topicCode = $doc->createElement("gmd:MD_TopicCategoryCode", "structure");
        $topicCategory->appendChild($topicCode);
        $dataId->appendChild($topicCategory);
        
        // abstract
        $abstract = $doc->createElement("gmd:abstract");
        $abstractText = $doc->createElement("gco:CharacterString");
        $abstractText->appendChild($doc->createTextNode($record_information["attributes"]["general"]["abstract"] ?? "No abstract provided."));
        $abstract->appendChild($abstractText);
        $dataId->appendChild($abstract);

        // resourceConstraints - CC BY 4.0 license
        $constraints = $doc->createElement("gmd:resourceConstraints");
        $legalConstraints = $doc->createElement("gmd:MD_LegalConstraints");

        // useLimitation with license URL and name
        $useLimitation = $doc->createElement("gmd:useLimitation");
        $licenseText = "This dataset is licenced under the Creative Commons Attribution 4.0 International (CC BY 4.0). See https://creativecommons.org/licenses/by/4.0/";
        $useLimitation->appendChild($doc->createElement("gco:CharacterString", $licenseText));
        $legalConstraints->appendChild($useLimitation);

        // accessConstraints = license
        $accessConstraints = $doc->createElement("gmd:accessConstraints");
        $accessCode = $doc->createElement("gmd:MD_RestrictionCode", "license");
        $accessCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#MD_RestrictionCode");
        $accessCode->setAttribute("codeListValue", "license");
        $accessConstraints->appendChild($accessCode);
        $legalConstraints->appendChild($accessConstraints);

        // useConstraints = license
        $useConstraints = $doc->createElement("gmd:useConstraints");
        $useCode = $doc->createElement("gmd:MD_RestrictionCode", "license");
        $useCode->setAttribute("codeList","http://www.isotc211.org/2005/resources/codeList.xml#MD_RestrictionCode");
        $useCode->setAttribute("codeListValue", "license");
        $useConstraints->appendChild($useCode);
        $legalConstraints->appendChild($useConstraints);

        $constraints->appendChild($legalConstraints);
        $dataId->appendChild($constraints);

        // language
        $lang = $doc->createElement("gmd:language");
        $langCode = $doc->createElement("gmd:LanguageCode", "eng");
        $langCode->setAttribute("codeList","http://www.loc.gov/standards/iso639-2/");
        $langCode->setAttribute("codeListValue", "eng");
        $lang->appendChild($langCode);
        $dataId->appendChild($lang);

        // image
        if ($record_information["attributes"]["general"]["images"]) {
            $graphicOverview = $doc->createElement("gmd:graphicOverview");
            $browseGraphic = $doc->createElement("gmd:MD_BrowseGraphic");

            $image = $record_information["attributes"]["general"]["images"][0];
            // File name (URL or file path)
            $fileName = $doc->createElement("gmd:fileName");
            $fileName->appendChild($doc->createElement("gco:CharacterString", $image["url"]));
            $browseGraphic->appendChild($fileName);

            // Optional description
            $fileDesc = $doc->createElement("gmd:fileDescription");
            $fileDesc->appendChild($doc->createElement("gco:CharacterString", $image["alt"]));
            $browseGraphic->appendChild($fileDesc);

            // Nest and append
            $graphicOverview->appendChild($browseGraphic);
            $dataId->appendChild($graphicOverview);
        }

        // geographic extent
        if ($record_information["attributes"]["geographic"]["boundaries"]) {
            $bbox_geom = $this->getBoundingBoxFromWKT($record_information["attributes"]["geographic"]["boundaries"]);
            $extent = $doc->createElement("gmd:extent");
            $exExtent = $doc->createElement("gmd:EX_Extent");
            $geo = $doc->createElement("gmd:geographicElement");
            $bbox = $doc->createElement("gmd:EX_GeographicBoundingBox");

            $west = $doc->createElement("gmd:westBoundLongitude");
            $west->appendChild($doc->createElement("gco:Decimal", $bbox_geom["west"]));
            $bbox->appendChild($west);

            $east = $doc->createElement("gmd:eastBoundLongitude");
            $east->appendChild($doc->createElement("gco:Decimal", $bbox_geom["east"]));
            $bbox->appendChild($east);

            $south = $doc->createElement("gmd:southBoundLatitude");
            $south->appendChild($doc->createElement("gco:Decimal", $bbox_geom["south"]));
            $bbox->appendChild($south);

            $north = $doc->createElement("gmd:northBoundLatitude");
            $north->appendChild($doc->createElement("gco:Decimal", $bbox_geom["north"]));
            $bbox->appendChild($north);

            $geo->appendChild($bbox);
            $exExtent->appendChild($geo);
            $extent->appendChild($exExtent);
            $dataId->appendChild($extent);
            $ident->appendChild($dataId);
            $root->appendChild($ident);
        } 
		else {
            // Extract coordinates from WKT string (assumed to be valid "POINT(lon lat)")
            $wkt = $record_information["attributes"]["geographic"]["coordinates"];
            preg_match("/POINT\s*\(\s*([\d\.\-]+)\s+([\d\.\-]+)\s*\)/i", $wkt, $matches);
            $lon = $matches[1];
            $lat = $matches[2];

            // Create extent structure using gml:Point
            $extent = $doc->createElement("gmd:extent");
            $exExtent = $doc->createElement("gmd:EX_Extent");
            $geoElement = $doc->createElement("gmd:geographicElement");
            $boundingPolygon = $doc->createElement("gmd:EX_BoundingPolygon");

            $polygon = $doc->createElement("gmd:polygon");
            $gmlPoint = $doc->createElementNS("http://www.opengis.net/gml","gml:Point");
            $gmlPoint->setAttribute("gml:id","centroid_or_representative_coordinates_of_site");
            $gmlPoint->setAttribute("srsName","http://www.opengis.net/def/crs/EPSG/0/4326");

            // Add the coordinates (lat lon)
            $pos = $doc->createElementNS("http://www.opengis.net/gml","gml:pos","$lat $lon");
            $gmlPoint->appendChild($pos);

            // Nest everything into the correct hierarchy
            $polygon->appendChild($gmlPoint);
            $boundingPolygon->appendChild($polygon);
            $geoElement->appendChild($boundingPolygon);
            $exExtent->appendChild($geoElement);
            $extent->appendChild($exExtent);
            $dataId->appendChild($extent);
            $ident->appendChild($dataId);
            $root->appendChild($ident);
        }

        // Return response
        $response = new Response($doc->saveXML());
        $response->headers->set("Content-Type", "application/xml");
        return $response;
    }

    public function getBoundingBoxFromWKT(string $wkt): ?array {
        
		$wkt = trim($wkt);
        $minLon = $maxLon = $minLat = $maxLat = null;

        // Match all coordinate groups inside parentheses (supports multiple polygons)
        preg_match_all("/\(\s*\(([^()]+)\)\s*\)/", $wkt, $matches);

        if (empty($matches[1])) { return null;} // Invalid or unsupported WKT 

        foreach ($matches[1] as $coords_str) {
            $points = preg_split("/,\s*/", trim($coords_str));

            foreach ($points as $point) {
                // Expect "lon lat" format
                $parts = preg_split("/\s+/", trim($point));
                if (count($parts) < 2) {
                    continue;
                }

                $lon = floatval($parts[0]);
                $lat = floatval($parts[1]);

                $minLon = is_null($minLon) ? $lon : min($minLon, $lon);
                $maxLon = is_null($maxLon) ? $lon : max($maxLon, $lon);
                $minLat = is_null($minLat) ? $lat : min($minLat, $lat);
                $maxLat = is_null($maxLat) ? $lat : max($maxLat, $lat);
            }
        }

        return [
            "west" => $minLon,
            "east" => $maxLon,
            "south" => $minLat,
            "north" => $maxLat,
        ];
    }
}
