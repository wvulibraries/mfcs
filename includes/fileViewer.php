<?php
include("../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

try{
	// Check for simple (stupid developer errors)
	if(!isset($engine->cleanGet['MYSQL']['assetsID'])) throw new Exception('No Assets ID Provided!');

	$fileType = isset($engine->cleanGet['MYSQL']['type'])
		? $engine->cleanGet['MYSQL']['type']
		: NULL;
	$savePath = files::getSaveDir($engine->cleanGet['MYSQL']['assetsID'], $fileType);

	// Figure out what file to display (either the provided one, or the 1st one in the dir)
	if(isset($engine->cleanGet['MYSQL']['file'])){
		// Use provided filename
		$filepath = $savePath.$engine->cleanGet['MYSQL']['file'];
		errorHandle::newError("fileViewer.php - No file found at '$filepath'", errorHandle::HIGH);
		if(!file_exists($filepath)) throw new Exception('No file found!');
	}else{
		// Use 1st file in directory
		$files = scandir($savePath);
		while(!isset($filepath) and ($file = array_pop($files)) !== FALSE){
			if($file{0} == '.') continue;
			$filepath = $savePath.$file;
			if(file_exists($filepath)) break;
		}
		if(!isset($filepath)){
			errorHandle::newError("fileViewer.php - No file found under '$savePath'", errorHandle::HIGH);
			throw new Exception('No file found!');
		}
	}

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