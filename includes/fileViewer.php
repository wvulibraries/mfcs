<?php
include("../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

try{
	// Check for simple (stupid developer errors)
	if(!isset($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID provided!');
	if(!isset($engine->cleanGet['MYSQL']['field']))    throw new Exception('No field provided!');

	// Get some vars
	$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
	$field = forms::getField($object['formID'], $engine->cleanGet['MYSQL']['field']);
	$fileType = isset($engine->cleanGet['MYSQL']['type'])
		? $engine->cleanGet['MYSQL']['type']
		: NULL;
	$savePath = isset($engine->cleanGet['MYSQL']['assetsID'])
		? files::getSaveDir($engine->cleanGet['MYSQL']['assetsID'], $fileType)
		: files::getSaveDir($object['data'][ $engine->cleanGet['MYSQL']['field'] ], $fileType);

	/*
	 * Figure out what file to display
	 * This is where we need to do some selective munging of the vars (depending on the type
	 */
	switch($fileType){
		case 'originals':
			if(!isset($engine->cleanGet['MYSQL']['file'])) throw new Exception('No filename provided!');
			$filepath = $savePath.$engine->cleanGet['MYSQL']['file'];
			break;

		case 'processed':
			$filepath = $savePath.$engine->cleanGet['MYSQL']['file'].'.'.strtolower($field['convertFormat']);
			break;

		case 'thumbs':
			$filepath = $savePath.$engine->cleanGet['MYSQL']['file'].'.'.strtolower($field['thumbnailFormat']);
			break;

		case 'ocr':
			$filepath = $savePath.$engine->cleanGet['MYSQL']['file'].'.txt';
			break;

		case 'combine':
			// @todo I think this is a bug in the file processor, but will need to check
			$filepath = $savePath.'../combined.pdf';
			break;
	}

	// Make sure the file exists
	if(!file_exists($filepath)) throw new Exception('File now found!');

	// Get the object's contents
	$fileContents = file_get_contents($filepath);

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
		header(sprintf("Content-Disposition: attachment; filename='%s'", basename($filepath)));
		header("Content-Type: application/octet-stream");
		die($fileContents); // die so nothing else will be displayed
	}else{
		if($mimeType == 'application/x-empty'){
			errorHandle::newError("Failed to locate file to display!", errorHandle::HIGH);
			header("Content-type: text/plain");
			die("Failed to locate requested file!"); // die so nothing else will be displayed
		}else{
			files::generateFilePreview($filepath, $mimeType, $fileContents);
			exit();
		}
	}
}catch(Exception $e){
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	die($e->getMessage());
}

?>