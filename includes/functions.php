<?php

function displayMessages() {
	$engine = EngineAPI::singleton();
	if (is_empty($engine->errorStack)) {
		return FALSE;
	}
	return '<section><header><h1>Results</h1></header>'.errorHandle::prettyPrint().'</section>';
}

function encodeFields($fields) {

	return base64_encode(serialize($fields));

}

function decodeFields($fields) {

	return unserialize(base64_decode($fields));

}

// Deprecated
function checkProjectPermissions($id) {
	return projects::checkPermissions($id);
}

// Deprecated
function getProject($projectID) {
	return projects::get($projectID);
}

// Deprecated
function getForm($formID) {
	return forms::get($formID);
}

// Deprecated
function getObject($objectID) {
	return objects::get($objectID);
}

// Deprecated
function getAllObjectsForForm($formID) {
	return objects::getAllObjectsForForm($formID);
}

// Deprecated
function checkObjectInForm($formID,$objectID) {
	return objects::checkObjectInForm($formID,$objectID);
}

// Deprecated
function checkFormInProject($projectID,$formID) {
	return forms::checkFormInProject($projectID,$formID);
}

function sortFieldsByPosition($a,$b) {
	return strnatcmp($a['position'], $b['position']);
}

function buildProjectNavigation($projectID) {
	$project = getProject($projectID);

	if ($project === FALSE) {
		return(FALSE);
	}

	$nav = decodeFields($project['groupings']);

	// print "<pre>";
	// var_dump($nav);
	// print "</pre>";

	$output = "";

	$currentGroup = "";

	foreach ($nav as $item) {

		// deal with field sets
		if ($item['grouping'] != $currentGroup) {
			if ($currentGroup != "") {
				$output .= "</ul></li>";
			}
			if (!isempty($item['grouping'])) {
				$output .= sprintf('<li><strong>%s</strong><ul>',
					$item['grouping']
					);
			}
			$currentGroup = $item['grouping'];
		}

		$output .= "<li>";
		if ($item['type'] == "logout") {
			$output .= sprintf('<a href="%s">%s</a>',
				htmlSanitize($item['url']),
				htmlSanitize($item['label'])
				);
		}
		else if ($item['type'] == "link") {
			$output .= sprintf('<a href="%s">%s</a>',
				htmlSanitize($item['url']),
				htmlSanitize($item['label'])
				);
		}
		else if ($item['type'] == "objectForm" || $item['type'] == "metadataForm") {
			$output .= sprintf('<a href="form.php?id=%s&amp;formID=%s">%s</a>',
				htmlSanitize($projectID),
				htmlSanitize($item['formID']),
				htmlSanitize($item['label'])
				);
		}
		else {
			$output .= sprintf('%s',
				htmlSanitize($item['label'])
				);
		}
		$output .= "</li>";

	}


	return $output;

}

function buildNumberAttributes($field) {

	$output = "";
	$output .= (!isempty($field["min"])) ?' min="'.$field['min'].'"'  :"";
	$output .= (!isempty($field["max"])) ?' max="'.$field['max'].'"'  :"";
	$output .= (!isempty($field["step"]))?' step="'.$field['step'].'"':"";

	return $output;

}

// Deprecated
function buildForm($formID,$objectID = NULL) {
	return forms::build($formID,$objectID);
}

function buildListTable($objects,$form,$projectID) {
    $revisionControl = new revisionControlSystem('objects','revisions','ID','modifiedTime');
	$form['fields'] = decodeFields($form['fields']);

	$header  = '<tr><th>Delete</th><th>Edit</th><th>Revisions</th><th>ID No</th>';
	$headers = array();
	foreach ($form['fields'] as $field) {

		if (strtolower($field['type']) == "idno") continue;

		if ($field['displayTable'] == "true") {
			$header .= sprintf('<th>%s</th>',
				$field['label']
				);
			$headers[$field['name']] = $field['label'];
		}
	}
	$header .= '</tr>';

	$output = sprintf('<form action="%s?id=%s&amp;formID=%s" method="%s">',
		$_SERVER['PHP_SELF'],
		htmlSanitize($projectID),
		htmlSanitize($form['ID']),
		"post"
		);
	$output .= sessionInsertCSRF();

	$output .= '<table>';
	$output .= $header;

	foreach($objects as $object) {
		$output .= "<tr>";
		$output .= sprintf('<td><input type="checkbox" name="delete_%s" /></td>',
			$object['ID']
			);
		$output .= sprintf('<td><a href="form.php?id=%s&amp;formID=%s&amp;objectID=%s">Edit</a></td>',
			htmlSanitize($projectID),
			htmlSanitize($form['ID']),
			htmlSanitize($object['ID'])
			);
        $output .= $revisionControl->hasRevisions($object['ID'])
            ? sprintf('<td><a href="revisions.php?id=%s&amp;formID=%s&amp;objectID=%s">View</a></td>',
                htmlSanitize($projectID),
                htmlSanitize($form['ID']),
                htmlSanitize($object['ID']))
            : '<td style="font-style: italic; color: #666;">None</td>';
		$output .= sprintf('<td>%s</td>',
			htmlSanitize(($form['metadata'] == "1")?$object['ID']:$object['idno'])
			);
		foreach ($headers as $headerName => $headerLabel) {
			$output .= sprintf('<td>%s</td>',
				htmlSanitize($object['data'][$headerName])
				);
		}
		$output .= "</tr>";

	}


	$output .= '</table>';

	$output .= "</form>";

	return $output;

}

// NOTE: data is being saved as RAW from the array.
function submitForm($project,$formID,$objectID=NULL) {
	return forms::submit($project,$formID,$objectID);
}

// $value must be RAW
function isDupe($formID,$projectID=NULL,$field,$value) {

	$engine = EngineAPI::singleton();

	if (isnull($projectID)) {
		$sql = sprintf("SELECT COUNT(*) FROM dupeMatching WHERE `formID`='%s' AND `field`='%s' AND `value`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($field),
			$engine->openDB->escape($value)
			);
	}
	else {
		$sql = sprintf("SELECT COUNT(*) FROM dupeMatching WHERE `formID`='%s' AND `projectID`='%s' AND `field`='%s' AND `value`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($projectID),
			$engine->openDB->escape($field),
			$engine->openDB->escape($value)
			);
	}

	$sqlResult = $engine->openDB->sqlResult($sql);

	// we return TRUE on Error, because if a dupe is encountered we want it to fail out.
	if ($sqlResult['result'] === FALSE) {
		return TRUE;
	}
	else if ((INT)$sqlResult['result']['COUNT(*)'] > 0) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

function getFormIDInfo($formID) {
	$form = getForm($formID);

	return decodeFields($form['idno']);
}

// if $increment is true it returns the NEXT number. if it is false it returns the current
function getIDNO($formID,$projectID,$increment=TRUE) {

	$engine         = EngineAPI::singleton();
	$idno           = getFormIDInfo($formID);

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT `count` FROM `projects` WHERE `ID`='%s'",
			$engine->openDB->escape($projectID)
			)
		);

	if (!$sqlResult['result']) {
		return FALSE;
	}

	$idno                         = $idno['idnoFormat'];
	$len                          = strrpos($idno,"#") - strpos($idno,"#") + 1;
	$sqlResult['result']['count'] = str_pad($sqlResult['result']['count'],$len,"0",STR_PAD_LEFT);
	$idno                         = preg_replace("/#+/", $sqlResult['result']['count'], $idno);

	return $idno;
}


// if $increment is true it returns the NEXT number. if it is false it returns the current
function dumpStuff($formID,$projectID,$increment=TRUE) {

	$engine         = EngineAPI::singleton();

	$form           = getForm($formID);
	$form['fields'] = decodeFields($form['fields']);
	$idno           = getFormIDInfo($formID);

	print "<pre>";
	var_dump($form['fields']);
	print "</pre>";

	print "<pre>";
	var_dump($idno);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT `count` FROM `projects` WHERE `ID`='%s'",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT * FROM `projects`",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("INSERT INTO containers (containerName) VALUES('foo')",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("UPDATE containers SET containerName='bar' WHERE ID='1'",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	return TRUE;
}


/**
 * Returns the base path to be used when uploading files
 *
 * @return string
 * @author Scott Blake
 **/
function getBaseUploadPath() {
	return mfcs::config('uploadPath', sys_get_temp_dir().DIRECTORY_SEPARATOR.'mfcs');
}

/**
 * Returns the path of an upload directory given an upload id.
 *
 * @param string $type
 * @return string
 * @author Scott Blake
 **/
function getUploadDir($type, $uploadID) {
	return getBaseUploadPath().DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$uploadID;
}

/**
 * Creates the directory structure for a given upload id.
 *
 * @param string $uploadID
 * @return bool
 * @author Scott Blake
 **/
function prepareUploadDirs($uploadID) {
	$permissions = 0777;

	if (!is_dir(getBaseUploadPath())) {
		if (!mkdir(getBaseUploadPath(), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getBaseUploadPath(),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_writable(getBaseUploadPath())) {
		errorHandle::newError('Not writable: '.getBaseUploadPath(),errorHandle::DEBUG);
		return FALSE;
	}

	if (!is_dir(getUploadDir('originals',$uploadID))) {
		if (!mkdir(getUploadDir('originals',$uploadID), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getUploadDir('originals',$uploadID),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_dir(getUploadDir('converted',$uploadID))) {
		if (!mkdir(getUploadDir('converted',$uploadID), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getUploadDir('converted',$uploadID),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_dir(getUploadDir('combined',$uploadID))) {
		if (!mkdir(getUploadDir('combined',$uploadID), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getUploadDir('combined',$uploadID),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_dir(getUploadDir('thumbs',$uploadID))) {
		if (!mkdir(getUploadDir('thumbs',$uploadID), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getUploadDir('thumbs',$uploadID),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_dir(getUploadDir('ocr',$uploadID))) {
		if (!mkdir(getUploadDir('ocr',$uploadID), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".getUploadDir('ocr',$uploadID),errorHandle::DEBUG);
			return FALSE;
		}
	}

	return TRUE;
}

/**
 * Performs necessary conversions, thumbnails, etc.
 *
 * @param array $field
 * @param string $uploadID
 * @return bool
 * @author Scott Blake
 **/
function processUploads($field,$uploadID) {
	$engine = EngineAPI::singleton();

	$files = scandir(getUploadDir('originals',$uploadID));
	foreach ($files as $filename) {
		// Skip these
		if (in_array($filename, array(".",".."))) {
			continue;
		}

		// Preserve original extension
		$origPath = getUploadDir("originals",$uploadID).DIRECTORY_SEPARATOR.$filename;
		$origExt  = ".".pathinfo($origPath, PATHINFO_EXTENSION);

		// Ensure this file is an image before image specific processing
		if (getimagesize($origPath) !== FALSE) {
			// If combine files is checked, read this image and add it to the combined object
			if (isset($field['combine']) && str2bool($field['combine'])) {
				// Create the hocr file
				$output = file_put_contents(
					getBaseUploadPath().DIRECTORY_SEPARATOR."hocr",
					"tessedit_create_hocr 1"
					);

				if ($output === FALSE) {
					errorHandle::newError("Failed to create hocr file.",errorHandle::HIGH);
					return FALSE;
				}

				// perform hOCR on the original uploaded file which gets stored in combined as an HTML file
				$output = shell_exec(sprintf('tesseract %s %s -l eng %s 2>&1',
					escapeshellarg($origPath),
					escapeshellarg(getUploadDir("combined",$uploadID).DIRECTORY_SEPARATOR.basename($filename,$origExt)),
					escapeshellarg(getBaseUploadPath().DIRECTORY_SEPARATOR."hocr")
					));

				if (trim($output) !== 'Tesseract Open Source OCR Engine with Leptonica') {
					errorHandle::newError("Tesseract Output: ".$output,errorHandle::HIGH);
					return FALSE;
				}

				// Convert original uploaded file to jpg in preparation of final combine
				$output = shell_exec(sprintf('convert %s %s 2>&1',
					escapeshellarg($origPath),
					escapeshellarg(getUploadDir("combined",$uploadID).DIRECTORY_SEPARATOR.basename($filename,$origExt).".jpg")
					));

				if (!is_empty($output)) {
					errorHandle::newError("Convert Output: ".$output,errorHandle::HIGH);
					return FALSE;
				}
			}

			// Convert uploaded files into some ofhter size/format/etc
			if (isset($field['convert']) && str2bool($field['convert'])) {
				$image = new Imagick();
				$image->readImage($origPath);

				// Convert format
				$image->setImageFormat($field['convertFormat']);

				// Resize image
				$image->scaleImage($field['convertWidth'], $field['convertHeight'], TRUE);

				// Add a border
				if (isset($field['border']) && str2bool($field['border'])) {
					$image->borderImage(
						$field['borderColor'],
						$field['borderWidth'],
						$field['borderHeight']
						);
				}

				// Create a thumbnail
				if (isset($field['thumbnail']) && str2bool($field['thumbnail'])) {
					// Make a copy of the original
					$thumb = $image->clone();

					// Change the format
					$thumb->setImageFormat($field['thumbnailFormat']);

					// Scale to thumbnail size, constraining proportions
					$thumb->thumbnailImage(
						$field['thumbnailWidth'],
						$field['thumbnailHeight'],
						TRUE
						);

					// Store thumbnail
					if ($thumb->writeImage(getUploadDir("thumbs",$uploadID).DIRECTORY_SEPARATOR.basename($filename,$origExt).".".strtolower($thumb->getImageFormat())) === FALSE) {
						errorHandle::errorMsg("Failed to create thumbnail: ".$filename);
					}
				}

				// Add a watermark
				if (isset($field['watermark']) && str2bool($field['watermark'])) {
					$fh = fopen($field['watermarkImage'], "rb");

					$watermark = new Imagick();
					$watermark->readImageFile($fh); // Full URL
					// $watermark->readImage("/path/to/file.png"); // Uses path relative to current file

					// Resize the watermark
					$watermark->scaleImage($image->getImageWidth()/1.5, $image->getImageHeight()/1.5, TRUE);

					list($positionHeight,$positionWidth) = explode("|",$field['watermarkLocation']);

					// calculate the position
					switch ($positionHeight) {
						case 'top':
							$y = 0;
							break;

						case 'bottom':
							$y = $image->getImageHeight() - $watermark->getImageHeight();
							break;

						case 'middle':
						default:
							$y = ($image->getImageHeight() - $watermark->getImageHeight()) / 2;
							break;
					}

					switch ($positionWidth) {
						case 'left':
							$x = 0;
							break;

						case 'right':
							$x = $image->getImageWidth() - $watermark->getImageWidth();
							break;

						case 'center':
						default:
							$x = ($image->getImageWidth() - $watermark->getImageWidth()) / 2;
							break;
					}

					// Add watermark to image
					if ($image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y) === FALSE) {
						errorHandle::errorMsg("Failed to create watermark: ".$filename);
					}
				}

				// Store image
				if ($image->writeImages(getUploadDir('converted',$uploadID).DIRECTORY_SEPARATOR.basename($filename,$origExt).".".strtolower($image->getImageFormat()), TRUE) === FALSE) {
					errorHandle::errorMsg("Failed to create image: ".$filename);
				}
			}

			// Create an OCR text file
			if (isset($field['ocr']) && str2bool($field['ocr'])) {
				// Include TesseractOCR class
				require_once 'class.tesseract_ocr.php';

				$text = TesseractOCR::recognize(getUploadDir('originals',$uploadID).DIRECTORY_SEPARATOR.$filename);

				if (file_put_contents(getUploadDir('ocr',$uploadID).DIRECTORY_SEPARATOR.basename($filename,$origExt).".txt", $text) === FALSE) {
					errorHandle::errorMsg("Failed to create OCR text file: ".$filename);
					errorHandle::newError("Failed to create OCR file for ".getUploadDir('originals',$uploadID).DIRECTORY_SEPARATOR.$filename,errorHandle::DEBUG);
				}
			}
		}

		// Ensure this file is an audio file before audio specific processing
		if (strpos(finfo::file($origPath, FILEINFO_MIME_TYPE), 'audio/') !== FALSE) {
			// Perform audio processing here
		}
	}

	// Write the combined PDF to disk
	if (isset($field['combine']) && str2bool($field['combine'])) {
		$combinedDir = getUploadDir("combined",$uploadID).DIRECTORY_SEPARATOR;

		// Combine HTML and JPG files into individual PDF files
		foreach (glob($combinedDir."*.jpg") as $file) {
			$output = shell_exec(sprintf('hocr2pdf -i %s -s -o %s < %s 2>&1',
				escapeshellarg($file),
				escapeshellarg($combinedDir.basename($file,"jpg")."pdf"),
				escapeshellarg($combinedDir.basename($file,"jpg")."html")
				));

			if (trim($output) !== 'Writing unmodified DCT buffer.') {
				errorHandle::errorMsg("Failed to Create PDF: ".basename($file,"jpg")."pdf");
				errorHandle::newError("hocr2pdf Output: ".$output,errorHandle::HIGH);
			}
		}

		// Combine all PDF files in directory
		$output = shell_exec(sprintf('gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s -f %s 2>&1',
			$combinedDir."combined.pdf",
			$combinedDir."*.pdf"
			));

		if (!is_empty($output)) {
			errorHandle::errorMsg("Failed to combine PDFs into single PDF.");
			errorHandle::newError("GhostScript Output: ".$output,errorHandle::HIGH);
		}

		// Delete all the files except "combined.pdf"
		foreach(glob($combinedDir."*") AS $file) {
			if ($file !== $combinedDir."combined.pdf") {
				unlink($file);
			}
		}
	}
}
?>
