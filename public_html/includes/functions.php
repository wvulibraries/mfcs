<?php

function renderToOptions($option){
	$returnValue = "";
	foreach($option as $key => $value){
		$returnValue .= sprintf('<option value="%s">%s</option>',
			$key,
			$value
		);
	}
	return $returnValue;
}

function displayMessages() {
	$engine = EngineAPI::singleton();
	if (is_empty($engine->errorStack)) {
		return FALSE;
	}
	return '<header class="page-header"><h1>Results</h1></header>'.errorHandle::prettyPrint();
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

// This function handles some translation errors that commonly occure during cutting and pasting.
function convertString($string) {

	// Formatting
	$string = preg_replace('/%Oitalic%/',   '<em>',      $string);
	$string = preg_replace('/%Citalic%/',   '</em>',     $string);
	$string = preg_replace('/%Obold%/',     '<strong>',  $string);
	$string = preg_replace('/%Cbold%/',     '</strong>', $string);
	$string = preg_replace('/%underline%/', '<u>',       $string);
	$string = preg_replace('/%underline%/', '</u>',      $string);
	$string = preg_replace('/\|\|\|/',      '<br />',    $string);

	// Links
	$string = preg_replace('/%link url="(.+?)"%(.+?)%\/link%/', '<a href="$1"><u>$2</u></a>', $string);

	// Fonts
	$string = preg_replace('/&#x2026;/', "…", $string);
	$string = preg_replace('/&iexcl;/', "¡", $string);
	$string = preg_replace('/&pound;/', "£", $string);
	$string = preg_replace('/&yen;/', "¥", $string);
	$string = preg_replace('/&iquest;/', "¿", $string);
	$string = preg_replace('/&frac34;/', "¾", $string);
	$string = preg_replace('/&frac12;/', "½", $string);
	$string = preg_replace('/&frac14;/', "¼", $string);
	$string = preg_replace('/&#x2018;/', "‘", $string);
	$string = preg_replace('/&#x2019;/', "’", $string);

	// Punctuation
	$string = preg_replace('/&amp;/',"&",$string);
	$string = preg_replace('/&gt;/',">",$string);
	$string = preg_replace('/&lt;/',"<",$string);
	$string = preg_replace('/&quot;/','"',$string);

	return $string;
}

?>
