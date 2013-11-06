<?php
require("../engineInclude.php");
require("../header.php");
define('UPLOAD_PATH', files::getBaseUploadPath());
define('PERMISSONS', 0777);

// Include the uploader class
recurseInsert("includes/class.fineUploader.php","php");

$uploader = new qqFileUploader();

// Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
$uploader->allowedExtensions = array();

// Specify the input name set in the javascript.
$uploader->inputName = 'qqfile';

// Preserve the file's extention for Mime-Type stuff
$filename = $uploader->getName();
$fileExt  = ".".pathinfo($filename, PATHINFO_EXTENSION);

// To save the upload with a specified name, set the second parameter.
$uploadPath = UPLOAD_PATH.DIRECTORY_SEPARATOR.$engine->cleanPost['MYSQL']['uploadID'];

// Make sure the upload temp dir exits
if(!is_dir($uploadPath)) mkdir($uploadPath, PERMISSONS, TRUE);
else if (!$engine->cleanPost['MYSQL']['multiple']) {
	$files = glob($uploadPath.DIRECTORY_SEPARATOR.'*'); // get all existing file names
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}
}

// Save the upload! (the ltrim() ensures that uploaded hidden files become un-hidden)
$result = $uploader->handleUpload($uploadPath, ltrim($uploader->getName(),'.'));

// To return a name used for uploaded file you can use the following line.
$result['uploadName'] = $uploader->getUploadName();

header("Content-Type: text/plain");
echo json_encode($result);
?>