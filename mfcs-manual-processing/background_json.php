<?php
// background_json.php

// Retrieve project name, timestamp, and directories from command line arguments
// $project_name = $argv[1];
// $timestamp = $argv[2];
// $directories = json_decode($argv[3], true);
// $form_id = $argv[4];

// Include necessary files and initialize variables
include('/home/mfcs.lib.wvu.edu/public_html/header.php');
include('./helper_functions.php');

$project_name = "mcppc";
$form_id = "151";
$timestamp = time();

// Create Directories 
// if (!($directories = Exporting::createExportDirectories($project_name, mfcs::config('nfsexport'), $timestamp, array("data","pdf","thumbs")))) {
//     die("Couldn't create export directories");
// }

// Load the objects from MFCS
$objects = objects::getAllObjectsForForm($form_id);

// Process metadata and initiate background task for file copying
processExportDataInBackground($objects, $project_name);

// Functions definition
function processExportDataInBackground($objects, $project_name) {
    // Background task for processing export data
    $tmp = array();
    foreach ($objects as $object) {
        // Extract metadata
        $tmp[] = extractMetadata($object);
    }

    # set output file for metadata
    $metadata_output = "{$project_name}-data.json";

    // Write metadata to file
    if (!$file = fopen($metadata_output,"w")) {
        errorHandle::newError(__METHOD__."() - Error creating file", errorHandle::DEBUG);
        print "error opening file.";
        exit;
    }

    fwrite($file, json_encode($tmp));
    fclose($file);
}

// Function to safely get an array from the object
function get_array_safe($array, $key) {
    return isset($array[$key]) && is_array($array[$key]) ? $array[$key] : array();
}

function extractMetadata($object) {
    // Define $identifier if needed
    $identifier = isset($object['identifier']) ? $object['identifier'] : 'undefined';

    // Extract metadata from object
    $metadata = array(
        'identifier'               => $identifier,
        'title'                    => isset($object['data']['title']) ? $object['data']['title'] : '',
        'date'                     => isset($object['data']['date']) ? $object['data']['date'] : '',
        'edtf'                     => isset($object['data']['EDTF']) ? $object['data']['EDTF'] : '',
        'creator'                  => array_to_string(get_array_safe($object['data'], 'creator'), 'title'),
        'rights'                   => getHeadingByID(isset($object['data']['rights']) ? $object['data']['rights'] : '', 'rights'),
        'language'                 => array_to_string(get_array_safe($object['data'], 'language'), 'language'),
        'record_type'              => array_to_string(get_array_safe($object['data'], 'recordType'), 'recordType'),
        'collection_title'         => getHeadingByID(isset($object['data']['collectionTitle']) ? $object['data']['collectionTitle'] : '', 'collection'),
        'collection_finding_aid'   => getHeadingByID(isset($object['data']['findingAid']) ? $object['data']['findingAid'] : '', 'findingAidURL'),
        'description'              => isset($object['data']['description']) ? clean_tags_spaces($object['data']['description']) : '',
        'policy_area'              => array_to_string(get_array_safe($object['data'], 'policyArea'), 'policyArea'),
        'subject_personal_name'    => array_to_string(get_array_safe($object['data'], 'subjectPersonalName'), 'title'),
        'subject_corp_name'        => array_to_string(get_array_safe($object['data'], 'subjectCorpName'), 'title'),
        'subject_topical'          => array_to_string(get_array_safe($object['data'], 'subjectTopical'), 'title'),
        'congress'                 => getHeadingByID(isset($object['data']['congress']) ? $object['data']['congress'] : '', 'subjectTemporal'),
        'geographic_location'      => array_to_string(get_array_safe($object['data'], 'location'), 'geoLocation'),
        'dc_type'                  => getHeadingByID(isset($object['data']['type']) ? $object['data']['type'] : '', 'type'),
        'extent'                   => isset($object['data']['extent']) ? $object['data']['extent'] : ''
    );

    return $metadata;
}

?>