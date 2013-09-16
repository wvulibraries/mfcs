<?php

include("../../header.php");

function getHeadingByID($id) {
	$object = objects::get($id);
	return($object['data']['name']);
}

$objects = objects::getAllObjectsForForm("2");

$xml = '<?xml version="1.0" encoding="UTF-8" ?><!-- This grammar has been deprecated - use FMPXMLRESULT instead --><FMPDSORESULT xmlns="http://www.filemaker.com/fmpdsoresult"><ERRORCODE>0</ERRORCODE><DATABASE>iai_data.fp7</DATABASE><LAYOUT></LAYOUT>';

$count = 0;
foreach ($objects as $object) {

	$mergedCreators = array_merge($object['data']['creatorPersName'], $object['data']['creatorCorpName'], $object['data']['creatorMeetName'], $object['data']['creatorUniformTitle']);
	$mergedSubjects = array_merge($object['data']['subjectPersName'], $object['data']['subjectCorpName'], $object['data']['subjectMeetingName'], $object['data']['subjectUniformTitle'], $object['data']['subjectTopical'], $object['data']['subjectGeoName']);
	$creators       = array();
	$subjects       = array();

	foreach ($mergedCreators as $headingID) {
		$creators[] = getHeadingByID($headingID);
	}

	foreach ($mergedSubjects as $headingID) {
		$subjects[] = getHeadingByID($headingID);
	}

	sort($creators);
	sort($subjects);

	$creator = implode("|||", $creators);
	$subject = implode("|||", $subjects);

	$xml .= sprintf('<ROW MODID="0" RECORDID="%s">',
		++$count
		);
	$xml .= sprintf("<itemtitle>%s</itemtitle>",
		$object['data']['title']
		);
	$xml .= sprintf("<date>%s</date>",
		$object['data']['date']
		);
	$xml .= sprintf("<itemdescription>%s</itemdescription>",
		$object['data']['description']
		);
	$xml .= sprintf("<subject>%s</subject>",
		$subject
		);
	$xml .= sprintf("<format>%s</format>",
		$object['data']['format']
		);
	$xml .= sprintf("<imagefn>%s.pdf</imagefn>",
		$object['idno']
		);
	$xml .= sprintf("<identifier>%s</identifier>",
		$object['idno']
		);
	$xml .= sprintf("<creator>%s</creator>",
		$creator
		);
	$xml .= sprintf("<type>%s</type>",
		getHeadingByID($object['data']['type'])
		);
	$xml .= '</ROW>';

}

$xml .= "</FMPDSORESULT>";

print $xml;

?>

