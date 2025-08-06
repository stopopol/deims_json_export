<?php

namespace Drupal\deims_json_export\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

class DEIMSIso19139Controller extends ControllerBase {

  public function Iso19139Response($record_information) {
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    // Root element
    $root = $doc->createElementNS("http://www.isotc211.org/2005/gmd", "gmd:MD_Metadata");
    $doc->appendChild($root);

    // Namespaces
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:gco", "http://www.isotc211.org/2005/gco");
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
    $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:gml", "http://www.opengis.net/gml");
    $root->setAttribute("xsi:schemaLocation", "http://www.isotc211.org/2005/gmd http://schemas.opengis.net/iso/19139/20070417/gmd/gmd.xsd");

    // fileIdentifier
    $fileIdentifier = $doc->createElement("gmd:fileIdentifier");
    $charStr = $doc->createElement("gco:CharacterString", $record_information['id']['suffix'] ?? 'default-id');
    $fileIdentifier->appendChild($charStr);
    $root->appendChild($fileIdentifier);

    // language
    $language = $doc->createElement("gmd:language");
    $langCode = $doc->createElement("gmd:LanguageCode", "eng");
    $langCode->setAttribute("codeList", "http://www.loc.gov/standards/iso639-2/");
    $langCode->setAttribute("codeListValue", "eng");
    $language->appendChild($langCode);
    $root->appendChild($language);

    // characterSet
    $charSet = $doc->createElement("gmd:characterSet");
    $charCode = $doc->createElement("gmd:MD_CharacterSetCode", "utf8");
    $charCode->setAttribute("codeList", "http://www.isotc211.org/2005/resources/codeList.xml#MD_CharacterSetCode");
    $charCode->setAttribute("codeListValue", "utf8");
    $charSet->appendChild($charCode);
    $root->appendChild($charSet);

    // hierarchyLevel
    $hierarchy = $doc->createElement("gmd:hierarchyLevel");
    $scopeCode = $doc->createElement("gmd:MD_ScopeCode", "dataset");
    $scopeCode->setAttribute("codeList", "http://www.isotc211.org/2005/resources/codeList.xml#MD_ScopeCode");
    $scopeCode->setAttribute("codeListValue", "dataset");
    $hierarchy->appendChild($scopeCode);
    $root->appendChild($hierarchy);

    // contact
    $contact = $doc->createElement("gmd:contact");
    $rp = $doc->createElement("gmd:CI_ResponsibleParty");

    if (!empty($record_information["contact"]["siteManager"])) {
      foreach ($record_information["contact"]["siteManager"] as $contact_item) {
        $name = $doc->createElement("gmd:individualName");
        $name->appendChild($doc->createElement("gco:CharacterString", $contact_item["name"] ?? ''));
        $rp->appendChild($name);
      }
    }

    if (!empty($record_information["contact"]["operatingOrganisation"])) {
      foreach ($record_information["contact"]["operatingOrganisation"] as $org_item) {
        $org = $doc->createElement("gmd:organisationName");
        $org->appendChild($doc->createElement("gco:CharacterString", $org_item["name"] ?? ''));
        $rp->appendChild($org);
      }
    }

    $role = $doc->createElement("gmd:role");
    $roleCode = $doc->createElement("gmd:CI_RoleCode", "pointOfContact");
    $roleCode->setAttribute("codeList", "http://www.isotc211.org/2005/resources/codeList.xml#CI_RoleCode");
    $roleCode->setAttribute("codeListValue", "pointOfContact");
    $role->appendChild($roleCode);
    $rp->appendChild($role);

    $contact->appendChild($rp);
    $root->appendChild($contact);

    // dateStamp
    $dateStamp = $doc->createElement("gmd:dateStamp");
    $dateStamp->appendChild($doc->createElement("gco:Date", date('Y-m-d')));
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
    $title->appendChild($doc->createElement("gco:CharacterString", $record_information['title'] ?? 'Untitled'));
    $ciCitation->appendChild($title);

    // citation > date
    $ciDate = $doc->createElement("gmd:CI_Date");

    $date = $doc->createElement("gmd:date");
    $dateVal = $doc->createElement("gco:Date", date('Y-m-d'));
    $date->appendChild($dateVal);
    $ciDate->appendChild($date);

    $dateType = $doc->createElement("gmd:dateType");
    $dtCode = $doc->createElement("gmd:CI_DateTypeCode", "creation");
    $dtCode->setAttribute("codeList", "http://www.isotc211.org/2005/resources/codeList.xml#CI_DateTypeCode");
    $dtCode->setAttribute("codeListValue", "creation");
    $dateType->appendChild($dtCode);
    $ciDate->appendChild($dateType);

    $ciCitation->appendChild($doc->createElement("gmd:date"))->appendChild($ciDate);
    $citation->appendChild($ciCitation);
    $dataId->appendChild($citation);

    // abstract
    $abstract = $doc->createElement("gmd:abstract");
    $abstract->appendChild($doc->createElement("gco:CharacterString", $record_information["general"]["abstract"] ?? 'No abstract provided.'));
    $dataId->appendChild($abstract);

    // language
    $lang = $doc->createElement("gmd:language");
    $langCode = $doc->createElement("gmd:LanguageCode", "eng");
    $langCode->setAttribute("codeList", "http://www.loc.gov/standards/iso639-2/");
    $langCode->setAttribute("codeListValue", "eng");
    $lang->appendChild($langCode);
    $dataId->appendChild($lang);

    // extent
    $extent = $doc->createElement("gmd:extent");
    $exExtent = $doc->createElement("gmd:EX_Extent");
    $geo = $doc->createElement("gmd:geographicElement");
    $bbox = $doc->createElement("gmd:EX_GeographicBoundingBox");

    $west = $doc->createElement("gmd:westBoundLongitude");
    $west->appendChild($doc->createElement("gco:Decimal", -10.0));
    $bbox->appendChild($west);

    $east = $doc->createElement("gmd:eastBoundLongitude");
    $east->appendChild($doc->createElement("gco:Decimal", 10.0));
    $bbox->appendChild($east);

    $south = $doc->createElement("gmd:southBoundLatitude");
    $south->appendChild($doc->createElement("gco:Decimal", -5.0));
    $bbox->appendChild($south);

    $north = $doc->createElement("gmd:northBoundLatitude");
    $north->appendChild($doc->createElement("gco:Decimal", 5.0));
    $bbox->appendChild($north);

    $geo->appendChild($bbox);
    $exExtent->appendChild($geo);
    $extent->appendChild($exExtent);
    $dataId->appendChild($extent);

    $ident->appendChild($dataId);
    $root->appendChild($ident);

    // Return response
    $response = new Response($doc->saveXML());
    $response->headers->set('Content-Type', 'application/xml');
    return $response;
  }

}
