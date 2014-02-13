<?php

include("../../header.php");

function convertCharacters($string) {
	$string = preg_replace('/…/', '&#x2026;', $string);
	$string = preg_replace('/\*\*\!\*\*/', ';', $string);

	$string = preg_replace('/<em>/', '%Oitalic%', $string);
	$string = preg_replace('/<\/em>/', '%Citalic%', $string);
	$string = preg_replace('/<strong>/', '%Obold%', $string);
	$string = preg_replace('/<\/strong>/', '%Cbold%', $string);
	$string = preg_replace('/<a href="(.+?)">(<u>)?(.+?)(<\/u>)?<\/a>/','%link url="$1"%$3%/link%',$string);

	$string = preg_replace('/&auml;/', "ä", $string);

	return $string;
}

// Output File:
$outFileName = "pec-data_".(time()).".xml";
$outFile     = "./dlxsXmlImageClass/".$outFileName;

localvars::add("outFile",$outFile);
localvars::add("outFileName",$outFileName);

function getHeadingByID($id) {
	$object = objects::get($id);
	return($object['data']['name']);
}

$objects = objects::getAllObjectsForForm("2");

$xml = '<?xml version="1.0" encoding="UTF-8" ?><!-- This grammar has been deprecated - use FMPXMLRESULT instead --><FMPDSORESULT xmlns="http://www.filemaker.com/fmpdsoresult"><ERRORCODE>0</ERRORCODE><DATABASE>iai_data.fp7</DATABASE><LAYOUT></LAYOUT>';

$count = 0;
foreach ($objects as $object) {

// 	if (!is_array($object['data']['creatorCorpName'])) {

// 	print "<pre>";
// 	var_dump($object);
// 	print "</pre>";
// exit;
// }
	$mergedCreators = array_merge((array)$object['data']['creatorPersName'], (array)$object['data']['creatorCorpName'], (array)$object['data']['creatorMeetName'], (array)$object['data']['creatorUniformTitle']);
	$mergedSubjects = array_merge((array)$object['data']['subjectPersName'], (array)$object['data']['subjectCorpName'], (array)$object['data']['subjectMeetingName'], (array)$object['data']['subjectUniformTitle'], (array)$object['data']['subjectTopical'], (array)$object['data']['subjectGeoName']);
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

	// array_filter($creators);
	// array_filter($subjects);

	$creator = implode("|||", $creators);
	$subject = implode("|||", $subjects);

	$object['data']['title'] = convertCharacters($object['data']['title']);

	$object['data']['description'] = preg_replace('/<p>/', '', $object['data']['description']);
	$object['data']['description'] = preg_replace('/<\/p>/', '', $object['data']['description']);
	$object['data']['description'] = preg_replace('/&nbsp;/', '', $object['data']['description']);

	$object['data']['description'] = convertCharacters($object['data']['description']);

	$object['data']['description'] = preg_replace('/</', '&lt;', $object['data']['description']);
	$object['data']['description'] = preg_replace('/>/', '&gt;', $object['data']['description']);
	$object['data']['description'] = preg_replace('/&lt;br \/&gt;/', '||||||', $object['data']['description']);


	if (preg_match('/^\|+$/',$creator)) {
		$creator = "";
	}
	if (preg_match('/^\|+$/',$subject)) {
		$subject = "";
	}

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

if (!$file = fopen($outFile,"w")) {
	errorHandle::newError(__METHOD__."() - Error creating file", errorHandle::DEBUG);
	print "error opening file.";
	exit;
}
fwrite($file, $xml);
fclose($file);

?>

Download: 
<a href="{local var="outFile"}">{local var="outFileName"}</a>