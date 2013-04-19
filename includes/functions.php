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

function sortFieldsByPosition($a,$b) {
	return strnatcmp($a['position'], $b['position']);
}

function buildProjectNavigation($projectID) {
	$project = projects::get($projectID);

	if ($project === FALSE) {
		return(FALSE);
	}

	$nav = $project['groupings'];

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
			$output .= sprintf('<a href="object.php?id=%s&amp;formID=%s">%s</a>',
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

// if $increment is true it returns the NEXT number. if it is false it returns the current
function getIDNO($formID,$projectID,$increment=TRUE) {
	return mfcs::getIDNO($formID,$increment);
}


/**
 * Creates the directory structure for a given upload id.
 *
 * @param string $uploadID
 * @return bool
 * @author Scott Blake
 **/
/*
function prepareUploadDirs($uploadID) {
	$permissions = 0777;

	if (!is_dir(files::getBaseUploadPath())) {
		if (!mkdir(files::getBaseUploadPath(), $permissions, TRUE)) {
			errorHandle::newError("Failed to create directory: ".files::getBaseUploadPath(),errorHandle::DEBUG);
			return FALSE;
		}
	}
	if (!is_writable(files::getBaseUploadPath())) {
		errorHandle::newError('Not writable: '.files::getBaseUploadPath(),errorHandle::DEBUG);
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
*/
?>
