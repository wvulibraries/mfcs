<?php
require("../engineInclude.php");

localVars::add("basePath","/tmp/mfcs");

// Include functions
recurseInsert("includes/functions.php","php");

// Include the uploader class
recurseInsert("includes/class.fineUploader.php","php");

$uploader = new qqFileUploader();

// Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
$uploader->allowedExtensions = array();

// Specify max file size in bytes.
$uploader->sizeLimit = 10 * 1024 * 1024;

// Specify the input name set in the javascript.
$uploader->inputName = 'qqfile';

// If you want to use resume feature for uploader, specify the folder to save parts.
$uploader->chunksFolder = localVars::get("basePath").'/chunks';

// Ensure directories exist and permissions set properly
prepareUploadDir(localVars::get("basePath"));

// To save the upload with a specified name, set the second parameter.
$result = $uploader->handleUpload(localVars::get("basePath").'/originals', md5(mt_rand()).'_'.$uploader->getName());

// To return a name used for uploaded file you can use the following line.
$result['uploadName'] = $uploader->getUploadName();

header("Content-Type: text/plain");
echo json_encode($result);
?>