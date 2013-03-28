<?php
include("../header.php");
recurseInsert("acl.php","php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

try{
    // Check for simple (stupid developer errors)
    if(!isset($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID Provided!');
    if(!isset($engine->cleanGet['MYSQL']['field']))    throw new Exception('No Object Field Provided!');

    // Get the passed info
    $objectID  = $engine->cleanGet['MYSQL']['objectID'];
    $fieldName = $engine->cleanGet['MYSQL']['field'];
    $basePath  = mfcs::config('uploadPath', '/tmp/mfcs');

    // Lookup the passed objectID
    $object = getObject($objectID);
    if($object === FALSE) throw new Exception('Invalid Object ID!');

    // Extract the object's data
    $object['data'] = decodeFields($object['data']);
    if($object['data'] === FALSE) throw new Exception('Error retrieving object!');

    // Get the passed field's data
    if(isset($object['data'][$fieldName])){
        $fieldData = $object['data'][$fieldName];
    }else{
        throw new Exception('Invalid Object Field!');
    }

    // Build the full path to the object we're showing
    $fullPath = "$basePath/originals/{$fieldData[0]}";

    // Get the object's contents
    $fileContents = file_get_contents($fullPath);

    // Determine the object's MIME type
    $fi = new finfo(FILEINFO_MIME);
    $mimeType = $fi->buffer(file_get_contents($fullPath));

    // Lastly, set the MIME Type header, and display the object
    header("Content-type: $mimeType");
    die($fileContents); // Die so nothing else will be displayed
}catch(Exception $e){
    errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
    die($e->getMessage());
}

// Get the needed IDs
$projectID = $engine->cleanGet['MYSQL']['projectID'];
$formID    = $engine->cleanGet['MYSQL']['formID'];
$objectID  = $engine->cleanGet['MYSQL']['objectID'];
$fieldName = $engine->cleanGet['MYSQL']['field'];




?>