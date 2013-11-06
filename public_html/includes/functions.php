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


function buildNumberAttributes($field) {

	$output = "";
	if (!isempty($field["format"]) && $field['format'] == 'value') {
		$output .= (!isempty($field["min"])) ?' min="'.$field['min'].'"'  :"";
		$output .= (!isempty($field["max"])) ?' max="'.$field['max'].'"'  :"";
		$output .= (!isempty($field["step"]))?' step="'.$field['step'].'"':"";
	}
	return $output;
}

// if $increment is true it returns the NEXT number. if it is false it returns the current
function getIDNO($formID,$projectID,$increment=TRUE) {
	return mfcs::getIDNO($formID,$increment);
}

?>
