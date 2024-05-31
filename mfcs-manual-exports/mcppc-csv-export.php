<?php
session_save_path('/tmp');
include "../public_html/header.php";
include './helper_functions.php';

// Set project variables
$project_name = "mcppc";
$form_id = "151";
$currentTime = date('Y-m-d_H-i-s');

// Build array of objects for CSV
$objects = objects::getAllObjectsForForm($form_id);

// Setup export directory
$filesExportBaseDir = "./$currentTime";
if (!mkdir($filesExportBaseDir)) {
    die("Couldn't Make Directory : Base : $filesExportBaseDir");
}

$outFileName = "${project_name}_${currentTime}.csv";
$outFile = $filesExportBaseDir . "/" . $outFileName;

if (!$file = fopen($outFile, "a")) {
    errorHandle::newError(__METHOD__ . "() - Error creating file", errorHandle::DEBUG);
    exit("Error opening file.");
}

$headers = [
    "contributing_institution", "title", "date", "edtf", "creator", "rights", "language", "congress",
    "collection_title", "physical_location", "collection_finding_aid", "identifier", "record_type", "dc_type",
    "policy_area", "subject_topical", "subject_personal_name", "subject_corp_name", "geographic_location",
    "extent", "publisher", "description", "uri_pdf", "uri_thumb"
];

if (fputcsv($file, $headers) === false) {
    fclose($file);
    die("Failed to set headers");
}

$count = 0;
foreach ($objects as $object) {
    // inspect the object to determine the structure of the data
    // var_dump($object);

    // // try to get the heading by ID
    // $heading = array_to_string($object['data']['language'], 'language');
    // var_dump($heading);
    // die();

    $count++;
    $identifier = isset($object['data']['Identifier']) ? strtolower(trim($object['data']['Identifier'])) : strtolower($object['idno']);
    $publisher = isset($object['data']['Publisher']) ? getHeadingByID($object['data']['Publisher'], 'names') : '';

    $tmp = [
        isset($object['data']['contributingInstitution']) ? $object['data']['contributingInstitution'] : '',
        isset($object['data']['title']) ? $object['data']['title'] : '',
        isset($object['data']['date']) ? $object['data']['date'] : '',
        isset($object['data']['EDTF']) ? $object['data']['EDTF'] : '',
        isset($object['data']['creator']) ? array_to_string($object['data']['creator'], 'title') : '',
        isset($object['data']['rights']) ? getHeadingByID($object['data']['rights'], 'rights') : '',
        isset($object['data']['language']) ? array_to_string($object['data']['language'], 'language') : '',
        isset($object['data']['congress']) ? getHeadingByID($object['data']['congress'], 'subjectTemporal') : '',
        isset($object['data']['collectionTitle']) ? getHeadingByID($object['data']['collectionTitle'], 'collection') : '',
        isset($object['data']['physicalLocation']) ? $object['data']['physicalLocation'] : '',
        isset($object['data']['findingAid']) ? getHeadingByID($object['data']['findingAid'], 'findingAidURL') : '',
        $identifier,
        isset($object['data']['recordType']) ? array_to_string($object['data']['recordType'], 'recordType') : '',
        isset($object['data']['type']) ? getHeadingByID($object['data']['type'], 'type') : '',
        isset($object['data']['policyArea']) ? array_to_string($object['data']['policyArea'], 'policyArea') : '',
        isset($object['data']['subjectTopical']) ? array_to_string($object['data']['subjectTopical'], 'title') : '',
        isset($object['data']['subjectPersonalName']) ? array_to_string($object['data']['subjectPersonalName'], 'title') : '',
        isset($object['data']['subjectCorpName']) ? array_to_string($object['data']['subjectCorpName'], 'title') : '',
        isset($object['data']['location']) ? array_to_string($object['data']['location'], 'geoLocation') : '',
        isset($object['data']['extent']) ? $object['data']['extent'] : '',
        isset($publisher) ? $publisher : '',
        isset($object['data']['description']) ? clean_tags_spaces($object['data']['description']) : '',
        "https://mcppc.lib.wvu.edu/pdf/{$identifier}.pdf",
        "https://mcppc.lib.wvu.edu/thumbs/{$identifier}.jpg"
    ];

    // inspect $tmp to determine the structure of the data
    // var_dump($tmp);
    // die();

    if (fputcsv($file, $tmp) === false) {
        fclose($file);
        die("Error writing to file");
    }

    // Uncomment the following line for testing with one record
    // break;
}

fclose($file);

echo "Exported $count Objects.\n";
?>
