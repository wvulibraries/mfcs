<?php
include("../header.php");

try {

	$formID    = isset($engine->cleanGet['MYSQL']['formID'])    ? $engine->cleanGet['MYSQL']['formID']    : NULL;
	$fieldName = isset($engine->cleanGet['MYSQL']['fieldName']) ? $engine->cleanGet['MYSQL']['fieldName'] : NULL;
	$output    = array(
		'options'  => array(),
		'pageSize' => $pageSize,
		'total'    => 0,
	);

	if (isnull($formID) || isnull($fieldName)) {
		throw new Exception();
	}

	$search   = isset($engine->cleanGet['MYSQL']['q'])        ? $engine->cleanGet['MYSQL']['q']        : NULL;
	$page     = isset($engine->cleanGet['MYSQL']['page'])     ? $engine->cleanGet['MYSQL']['page']     : NULL;
	$pageSize = isset($engine->cleanGet['MYSQL']['pageSize']) ? $engine->cleanGet['MYSQL']['pageSize'] : NULL;
	$options  = array();

	// limit by search and re-order by value
	foreach (forms::retrieveData($formID, $fieldName) as $option) {
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

	sort($options);

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
}

die(json_encode($output));
