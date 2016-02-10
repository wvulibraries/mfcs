<?php
include("../header.php");

// Function needed for u sort
function compareStrings($a, $b){
	return strcmp(strtolower($a['text']), strtolower($b['text']));
}

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

try {

	$formID    = isset($engine->cleanGet['MYSQL']['formID'])    ? $engine->cleanGet['MYSQL']['formID']    : NULL;
	$fieldName = isset($engine->cleanGet['MYSQL']['fieldName']) ? $engine->cleanGet['MYSQL']['fieldName'] : NULL;
	$value     = isset($engine->cleanGet['MYSQL']['value'])     ? $engine->cleanGet['MYSQL']['value']     : NULL;
	$output    = array(
		'options'  => array(),
		'pageSize' => 0,
		'total'    => 0,
	);

	if (isnull($formID)) {
		throw new Exception("Missing Form ID.");
	}

	if (isnull($fieldName)) {
		throw new Exception("Missing Field Name.");
	}

	$search   = isset($engine->cleanGet['MYSQL']['q'])        ? $engine->cleanGet['MYSQL']['q']        : NULL;
	$page     = isset($engine->cleanGet['MYSQL']['page'])     ? $engine->cleanGet['MYSQL']['page']     : 1;
	$pageSize = isset($engine->cleanGet['MYSQL']['pageSize']) ? $engine->cleanGet['MYSQL']['pageSize'] : 2000;
	$options  = array();

	// limit by search and re-order by value
	foreach (forms::retrieveData($formID, $fieldName) as $option) {
		// If value exists and doesn't match, skip it
		if (!is_empty($value) && $value != $option['objectID']) {
			continue;
		}

		// If a search term was entered
		if (!is_empty($search)) {
			$search = strtolower($search);

			// Check if search string exists in value
			if (FALSE === strpos(strtolower($option['value']), $search)) {
				// Try again by removing diacritics
				$value = str_replace(array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), $option['value']);
				$value = iconv('UTF-8', 'US-ASCII//TRANSLIT', $value);

				// Still no match found, skip
				if (FALSE === strpos(strtolower($value), $search)) {
					continue;
				}
			}
		}

		$options[] = array(
			'text' => $option['value'], // first element controls sorting
			'id'   => $option['objectID'],
		);
	}

	usort($options, 'compareStrings');

	for ($i = $page*$pageSize-$pageSize; $i < $page*$pageSize; $i++) {
		if (!isset($options[$i])) {
			break;
		}
		$output['options'][] = $options[$i];
	}

	$output['pageSize'] = $pageSize;
	$output['total']    = sizeof($options);

}
catch(Exception $e) {
	$output['error'] = $e->getMessage();
}

die(json_encode($output));
