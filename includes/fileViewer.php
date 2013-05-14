<?php
include("../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

try{
	// Check for simple (stupid developer errors)
	if(!isset($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID Provided!');
	if(!isset($engine->cleanGet['MYSQL']['field']))    throw new Exception('No Object Field Provided!');

	// Get the passed info
	$objectID  = $engine->cleanGet['MYSQL']['objectID'];
	$fieldName = $engine->cleanGet['MYSQL']['field'];
	$basePath  = files::getBaseUploadPath();

	// Are we getting a file from a current object, or from a revision?
	if(isset($engine->cleanGet['MYSQL']['revisionID'])){
		// Lookup the passed revision
		$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');
		$object = $revisions->getRevision($objectID, $engine->cleanGet['MYSQL']['revisionID']);
		if($object === FALSE) throw new Exception('Invalid Revision ID!');
		$object['data'] = decodeFields($object['data']);
	}else{
		// Lookup the passed object
		$object = objects::get($objectID);
		if($object === FALSE) throw new Exception('Invalid Object ID!');
	}

	// Get the passed field's data
	if(isset($object['data'][$fieldName])){
		$fieldData = $object['data'][$fieldName];
	}else{
		throw new Exception('Invalid Object Field!');
	}

	// Get the object's files array and grab the correct file we are showing
	$files = $object['data'][$fieldName];
	if(!sizeof($files)) throw new Exception("No files uploaded for this field!");

	// $files has the UUID in it at this point

	$file = isset($engine->cleanGet['MYSQL']['fileKey'])? $files[$engine->cleanGet['MYSQL']['fileKey']] : array_pop($files);

	// Build the full path to the object we're showing
	$fullPath = files::getSaveDir('originals',$file['filepath']).DIRECTORY_SEPARATOR.basename($file['filepath']);

	// Get the object's contents
	$fileContents = file_get_contents($fullPath);

	// Get the MIME Type
	if(isPHP('5.3')){
		$fi = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fi->buffer($fileContents);
	}else{
		$fi = new finfo(FILEINFO_MIME);
		list($mimeType,$mimeEncoding) = explode(';', $fi->buffer($fileContents));
	}

	// Set the correct MIME-Type headers, and output the file's content
	if(isset($engine->cleanGet['MYSQL']['download']) and str2bool($engine->cleanGet['MYSQL']['download'])){
		header(sprintf("Content-Disposition: attachment; filename='%s'", $file['filename']));
		header("Content-Type: application/octet-stream");
		die($fileContents); // die so nothing else will be displayed
	}else{
		if($mimeType == 'application/x-empty'){
			errorHandle::newError("Failed to locate file to display! (objectID: {$engine->cleanGet['MYSQL']['objectID']}, field: {$engine->cleanGet['MYSQL']['field']})", errorHandle::HIGH);
			header("Content-type: text/plain");
			die("Failed to locate requested file!"); // die so nothing else will be displayed
		}else{
			files::generateFilePreview($fullPath, $mimeType, $fileContents);
			exit();
		}
	}
}catch(Exception $e){
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	die($e->getMessage());
}

?>