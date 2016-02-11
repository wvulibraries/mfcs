<?php
include("../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;
// $engine->eTemplate("include","header");


// List of Video Mime types
	$videoMimeTypes = array( 'application/mp4', 'application/ogg', 'video/3gpp', 'video/3gpp2', 'video/flv', 'video/h264', 'video/mp4', 'video/mpeg', 'video/mpeg-2', 'video/mpeg4', 'video/ogg', 'video/ogm', 'video/quicktime', 'video/avi');
	$audioMimeTypes = array('audio/acc', 'audio/mp4', 'audio/mp3', 'audio/mp2', 'audio/mpeg', 'audio/oog', 'audio/midi', 'audio/wav', 'audio/x-ms-wma','audio/webm');
	$imageMimeTypes = array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon');
	$pdfMimeTypes   = array('application/pdf');
	$webMimeTypes   = array('audio/mp4', 'audio/mp3', 'video/mp4', 'video/mpeg', 'video/ogg');

try{
	// Check for simple (stupid developer errors)
	if(!isset($engine->cleanGet['MYSQL']['objectID'])) throw new Exception('No Object ID provided!');
	if(!isset($engine->cleanGet['MYSQL']['field']))    throw new Exception('No field provided!');
	if(!isset($engine->cleanGet['MYSQL']['type']))     throw new Exception('No file type provided!');

	// Get some vars
	$object    = objects::get($engine->cleanGet['MYSQL']['objectID']);
	$fieldName = $engine->cleanGet['MYSQL']['field'];
	$field     = forms::getField($object['formID'], $engine->cleanGet['MYSQL']['field']);
	$fileType  = trim($engine->cleanGet['MYSQL']['type']);
	$fileArray = $object['data'][ $fieldName ];
	$fileUUID  = $fileArray['uuid'];


	if (FALSE === strpos($fileType,'combined')){
		// Non-combined file
		$fileID    = $engine->cleanGet['MYSQL']['fileID'];
		$file      = $fileArray['files'][ $fileType ][ $fileID ];

		if($fileType == 'video'){
			$file = $fileArray['files']['video'][0];
		}

		$filepath  = ($fileType == 'archive' ?  files::getSaveDir($fileUUID,$fileType).DIRECTORY_SEPARATOR.$file['name'] : files::getSaveDir($fileUUID,$fileType).$file['name']);
	}
	else {
		// Combined file
		if ($fileType == 'combinedPDF') {
			// Show the combined PDF
			// Find the file that has an application mime type (like a PDF)
			foreach ($fileArray['files']['combine'] as $file) {
				if (FALSE !== strpos($file['type'], 'application/')) {
					$filepath = files::getSaveDir($fileUUID,'combine').$file['name'];
					break;
				}
			}
		}
		else {
			// Show the combined PDF's thumbnail
			// Find the file that has an image mime type
			foreach ($fileArray['files']['combine'] as $file) {
				if (FALSE !== strpos($file['type'], 'image/')) {
					$filepath = files::getSaveDir($fileUUID,'combine').$file['name'];
					break;
				}
			}
		}
	}

	// Make sure the file exists
	if (!file_exists($filepath)) throw new Exception('File not found! "'.$filepath.'"');

	// for video thumbnails set a name of the file
	if(isset($engine->cleanGet['MYSQL']['name']) && ( !isnull($engine->cleanGet['MYSQL']['name']) || !is_empty($engine->cleanGet['MYSQL']['name']) )){
		$videoThumbName = $engine->cleanGet['MYSQL']['name'];
		$videoThumbExt  = $field['videoFormatThumb'];
		$fullFileName   = $videoThumbName.".".$videoThumbExt;
		$filepath       = files::getSaveDir($fileUUID,$fileType).$fullFileName;
	}

	// Get the MIME Type
	if (isPHP('5.3')) {
		$fi = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fi->file($filepath);
	}
	else {
		$fi = new finfo(FILEINFO_MIME);
		list($mimeType,$mimeEncoding) = explode(';', $fi->file($filepath));
	}



	//Set the correct MIME-Type headers, and output the file's content
	if (isset($engine->cleanGet['MYSQL']['download']) and str2bool($engine->cleanGet['MYSQL']['download'])) {
		header(sprintf("Content-Disposition: attachment; filename='%s'",
				isset($downloadFilename) ? $downloadFilename : basename($filepath))
		);
		header("Content-Type: application/octet-stream");
		ini_set('memory_limit',-1);
		die(file_get_contents($filepath)); // die so nothing else will be displayed
	}
	else {
		if ($mimeType == 'application/x-empty') {
			errorHandle::newError("Failed to locate file to display!", errorHandle::HIGH);
			header("Content-type: text/plain");
			die("Failed to locate requested file!"); // die so nothing else will be displayed
		}
		else{
			if(in_array($mimeType, $webMimeTypes)){
    			$stream = new VideoStream($filepath, $mimeType);
				$stream->start();
			}
			else{
				files::generateFilePreview($filepath, $mimeType);
				exit();
			}
		}
	}
}
catch (Exception $e) {
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	die($e->getMessage());
}

?>