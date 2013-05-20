<?php
include("../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

try{
	// Check for simple (stupid developer errors)
	if(!isset($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID provided!');
	if(!isset($engine->cleanGet['MYSQL']['field']))    throw new Exception('No field provided!');

	// Get some vars
	$object    = objects::get($engine->cleanGet['MYSQL']['objectID']);
	$fieldName = $engine->cleanGet['MYSQL']['field'];
	$field     = forms::getField($object['formID'], $engine->cleanGet['MYSQL']['field']);
	$fileArray = $object['data'][ $fieldName ];
	$fileUUID  = $fileArray['uuid'];
	$fileType  = $engine->cleanGet['MYSQL']['type'];
	$fileID    = $engine->cleanGet['MYSQL']['fileID'];
	$file      = $fileArray['files'][ $fileType ][ $fileID ];
	$filepath  = $file['path'].$file['name'];

	// Make sure the file exists
	if(!file_exists($filepath)) throw new Exception('File not found!');

	// Get the MIME Type
	if(isPHP('5.3')){
		$fi = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fi->file($filepath);
	}else{
		$fi = new finfo(FILEINFO_MIME);
		list($mimeType,$mimeEncoding) = explode(';', $fi->file($filepath));
	}

	// Set the correct MIME-Type headers, and output the file's content
	if(isset($engine->cleanGet['MYSQL']['download']) and str2bool($engine->cleanGet['MYSQL']['download'])){
		header(sprintf("Content-Disposition: attachment; filename='%s'",
				isset($downloadFilename) ? $downloadFilename : basename($filepath))
		);
		header("Content-Type: application/octet-stream");
		ini_set('memory_limit',-1);
		die(file_get_contents($filepath)); // die so nothing else will be displayed
	}else{
		if($mimeType == 'application/x-empty'){
			errorHandle::newError("Failed to locate file to display!", errorHandle::HIGH);
			header("Content-type: text/plain");
			die("Failed to locate requested file!"); // die so nothing else will be displayed
		}else{
			files::generateFilePreview($filepath, $mimeType);
			exit();
		}
	}
}catch(Exception $e){
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	die($e->getMessage());
}

?>