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

	// search has to be raw, so that things like apostrophe's aren't escaped
	$search   = isset($engine->cleanGet['RAW']['q'])          ? $engine->cleanGet['RAW']['q']        : NULL;
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
		if(!is_empty($search)) {
			$search = strtolower($search);
			$val    = strtolower($option['value']);

			if(strpos($val, $search) === false) {
				continue;
			}
		}

		$optionValues = array(
			'text' => $option['value'],
			'id'   => $option['objectID'],
		);

		array_push($options,$optionValues);
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
