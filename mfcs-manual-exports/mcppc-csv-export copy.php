<?php

session_save_path('/tmp');

include "../public_html/header.php";
include('./helper_functions.php');

# set project variables
$project_name = "mcppc";
$form_id = "151";
$timestamp = time();

// BUILD ARRAY OF OBJECTS FOR CSV
// ====================================================
$objects = objects::getAllObjectsForForm($form_id);

// ====================================================
// SETUP DIRECTORIES
// ====================================================

// Directory where we will export the files
$currentTime = time();
$filesExportBaseDir = $currentTime;

// create the directory structure.
if (!mkdir($filesExportBaseDir)) {
	die("Couldn't Make Directory : Base : $filesExportBaseDir");
}

// Output File 
$outFileName = "${project_name}_".(time()).".csv";
$outFile = $filesExportBaseDir . "/" . $outFileName; 
localvars::add("outFile",$outFile);

if (!$file = fopen($outFile,"a")) {
	errorHandle::newError(__METHOD__."() - Error creating file", errorHandle::DEBUG);
	print "error opening file.";
	exit;
}

// ====================================================
// BUILD ARRAY OF OBJECTS FOR CSV
// ====================================================
$objects = objects::getAllObjectsForForm($form_id);

$csv = array(); 
$headers =  array("contributing_institution",
                  "title",
                  "date",
                  "edtf",
                  "creator",
                  "rights",
                  "language",
                  "congress",
                  "collection_title",
                  "physical_location", 
                  "collection_finding_aid",
                  "identifier",
                  "record_type",
                  "dc_type",
                  "policy_area",
                  "subject_topical",
                  "subject_personal_name",
                  "subject_corp_name",
                  "geographic_location",
                  "extent",
                  "publisher",
                  "description", 
                  "uri_pdf",
                  "uri_thumb"
                 );

# set headers
if ((fputcsv($file, $headers)) === FALSE) {
	print "Failed to set headers";
	die;
}

$count = 0;
foreach ($objects as $object) {
  $count++;
  // insure $identifier is set
  $identifier = strtolower(trim($object['data']['Identifier']));
  if (empty($identifier)) {
    $identifier = strtolower($object['idno']);
  }

	$tmp   = array(); 
  $tmp[] = $object['data']['contributingInstitution'];
  $tmp[] = $object['data']['title'];
  $tmp[] = $object['data']['date'];
  $tmp[] = $object['data']['EDTF'];
  $tmp[] = array_to_string($object['data']['creator']);
  $tmp[] = getHeadingByID($object['data']['rights'], 'rights');
  $tmp[] = array_to_string($object['data']['language'], 'language');
  $tmp[] = getHeadingByID($object['data']['congress'], 'subjectTemporal');
  $tmp[] = getHeadingByID($object['data']['collectionTitle'], 'collection');
  $tmp[] = $object['data']['physicalLocation'];
  $tmp[] = getHeadingByID($object['data']['findingAid'], 'findingAidURL');
  $tmp[] = $identifier;
  $tmp[] = array_to_string($object['data']['recordType'], 'recordType');
  $tmp[] = getHeadingByID($object['data']['type'], 'type');
  $tmp[] = array_to_string($object['data']['policyArea'], 'policyArea');
  $tmp[] = array_to_string($object['data']['subjectTopical'], 'title');  
  $tmp[] = array_to_string($object['data']['subjectPersonalName'], 'title');
  $tmp[] = array_to_string($object['data']['subjectCorpName'], 'title');
  $tmp[] = array_to_string($object['data']['location'], 'geoLocation');
  $tmp[] = $object['data']['extent'];
  $tmp[] = getHeadingByID($object['data']['Publisher'], 'names');
  $tmp[] = clean_tags_spaces($object['data']['description']);
  $tmp[] = "https://mcppc.lib.wvu.edu/pdf/{$identifier}.pdf";
  $tmp[] = "https://mcppc.lib.wvu.edu/thumbs/{$identifier}.jpg";

	if ((fputcsv($file, $tmp)) === FALSE) {
		print "error writing to file";
		die;
	}

  // break after one record for testing
  // break;
}

// ====================================================
// WRITE TO FILE & CLOSE IT
// ====================================================

fclose($file);

// var_dump($outFile);
// die();


print "Exported " . $count . " Objects.\n";

?>


