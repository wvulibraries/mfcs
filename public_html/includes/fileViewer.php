<?php
include "../header.php";

// Turn off EngineAPI template engine
$engine->obCallback = false;

// List of Mime types
$videoMimeTypes = array('application/mp4', 'application/ogg', 'video/3gpp', 'video/3gpp2', 'video/flv', 'video/h264', 'video/mp4', 'video/mpeg', 'video/mpeg-2', 'video/mpeg4', 'video/ogg', 'video/ogm', 'video/quicktime', 'video/avi');
$audioMimeTypes = array('audio/acc', 'audio/mp4', 'audio/mp3', 'audio/mp2', 'audio/mpeg', 'audio/oog', 'audio/midi', 'audio/wav', 'audio/x-ms-wma','audio/webm');
$imageMimeTypes = array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon');
$pdfMimeTypes   = array('application/pdf');
$webMimeTypes   = array('audio/mp4', 'audio/mp3', 'video/mp4', 'video/mpeg', 'video/ogg');

try {
    // Check for required parameters
    $requiredParams = array('objectID', 'field', 'type');
    foreach ($requiredParams as $param) {
        if (!isset($engine->cleanGet['MYSQL'][$param])) {
            throw new Exception('Missing required parameter: ' . $param);
        }
    }

    // Get necessary variables
    $objectID = $engine->cleanGet['MYSQL']['objectID'];
    $field = $engine->cleanGet['MYSQL']['field'];
    $fileType = trim($engine->cleanGet['MYSQL']['type']);

    // Get file information
    $object = objects::get($objectID);
    $fileArray = $object['data'][$field];
    $fileUUID = $fileArray['uuid'];

    // Handle combined and non-combined files
    if (strpos($fileType, 'combined') !== false) {
        // Combined file
        $combineFiles = $fileArray['files']['combine'];
    
        // Check if it's a combined PDF
        if ($fileType == 'combinedPDF') {
            $filterType = 'application/';
        } else {
            $filterType = 'image/';
        }
    
        // Find the file that matches the filter type
        foreach ($combineFiles as $file) {
            if (strpos($file['type'], $filterType) !== false) {
                $filepath = files::getSaveDir($fileUUID, 'combine') . $file['name'];
                break;
            }
        }
    } else {
        // Non-combined file
        $fileID = $engine->cleanGet['MYSQL']['fileID'];
    
        // Check if it's a video file
        if ($fileType == 'video') {
            $file = $fileArray['files']['video'][0];
        } else {
            $file = $fileArray['files'][$fileType][$fileID];
        }
    
        // Set filepath based on file type
        if ($fileType == 'archive') {
            $filepath = files::getSaveDir($fileUUID, $fileType) . DIRECTORY_SEPARATOR . $file['name'];
        } else {
            $filepath = files::getSaveDir($fileUUID, $fileType) . $file['name'];
        }
    }

    // Check if file is OCR type and handle it
    if ($fileType == 'ocr') {
        $fileID = $engine->cleanGet['MYSQL']['fileID'];
        $ocrFile = $fileArray['files']['ocr'][$fileID]["ocr"][0]['name'];

        if (!empty($ocrFile)) {
            $filepath = ($fileType == 'archive') ? files::getSaveDir($fileUUID, $fileType) . DIRECTORY_SEPARATOR . $file['name'] : files::getSaveDir($fileUUID, $fileType) . $ocrFile;;

            if (file_exists($filepath)) {
                downloadFile($filepath);
            } else {
                throw new Exception('OCR File not found: ' . $filepath);
            }
        } else {
            throw new Exception('No OCR files found for this object.');
        }
    }

    // Get MIME Type
    $mimeType = mime_content_type($filepath);

    // Set headers and output file content
    if (isset($engine->cleanGet['MYSQL']['download']) && str2bool($engine->cleanGet['MYSQL']['download'])) {
        downloadFile($filepath);
    } else {
        if ($mimeType == 'application/x-empty') {
            throw new Exception('Failed to locate file to display: ' . $filepath);
        } else {
            if (in_array($mimeType, $webMimeTypes)) {
                $stream = new VideoStream($filepath, $mimeType);
                $stream->start();
            } else {
                files::generateFilePreview($filepath, $mimeType);
                exit();
            }
        }
    }
} catch (Exception $e) {
    errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
    die($e->getMessage());
}

function downloadFile($filepath) {
    header(sprintf("Content-Disposition: attachment; filename=%s", basename($filepath)));
    header("Content-Type: application/octet-stream");
    ini_set('memory_limit', '-1');
    die(file_get_contents($filepath));
}
?>
