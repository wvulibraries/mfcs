<?php
require("../engineInclude.php");
require("../header.php");
localVars::add("basePath",getBaseUploadPath());

// Include the uploader class
recurseInsert("includes/class.fineUploader.php","php");

$uploader = new qqFileUploader();

// Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
$uploader->allowedExtensions = array();

// Specify the input name set in the javascript.
$uploader->inputName = 'qqfile';

// Ensure directories exist and permissions set properly
prepareUploadDirs($engine->cleanPost['MYSQL']['uploadID']);

// To save the upload with a specified name, set the second parameter.
$result = $uploader->handleUpload(getUploadDir('originals',$engine->cleanPost['MYSQL']['uploadID']), $uploader->getName());

// To return a name used for uploaded file you can use the following line.
$result['uploadName'] = $uploader->getUploadName();

header("Content-Type: text/plain");
echo json_encode($result);
?>