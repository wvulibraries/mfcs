<?php

function get_select_by($id,$field) {
  $object = objects::get($id);
  return($object['data'][$field]);
}

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
	return errorHandle::prettyPrint();
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

function get_heading_by_id($id,$title) {
	$object = objects::get($id);
	return($object['data'][$title]);
}

function link_select_to_objects($object) {

	$form = forms::get($object['formID']);

	foreach ($form['fields'] as $field) {

		if (($field['type'] == "select" || $field['type'] == "multiselect") && isset($field['choicesForm'])) {

			$temp = array();
			if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {
				foreach ($object['data'][$field['name']] as $heading_id) {
					$temp[] = get_heading_by_id($heading_id,$field['choicesField']);
				}
			}

			$object['data'][$field['name']] = $temp;

		}

	}

	return $object;
}

function process_objects($object) {

	if ($object['data']['publicRelease'] == "No") return;

	return link_select_to_objects($object);

}

// From https://github.com/GerHobbelt/nicejson-php
// original code: http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
// adapted to allow native functionality in php version >= 5.4.0

/**
* Format a flat JSON string to make it more human-readable
*
* @param string $json The original JSON string to process
*        When the input is not a string it is assumed the input is RAW
*        and should be converted to JSON first of all.
* @return string Indented version of the original JSON string
*/
function json_format($json) {
  if (!is_string($json)) {
    if (phpversion() && phpversion() >= 5.4) {
      return json_encode($json, JSON_PRETTY_PRINT);
    }
    $json = json_encode($json);
  }
  $result      = '';
  $pos         = 0;               // indentation level
  $strLen      = strlen($json);
  $indentStr   = "&nbsp;&nbsp;&nbsp;&nbsp;";
  $newLine     = "<br />";
  $prevChar    = '';
  $outOfQuotes = true;

  for ($i = 0; $i < $strLen; $i++) {
    // Speedup: copy blocks of input which don't matter re string detection and formatting.
    $copyLen = strcspn($json, $outOfQuotes ? " \t\r\n\",:[{}]" : "\\\"", $i);
    if ($copyLen >= 1) {
      $copyStr = substr($json, $i, $copyLen);
      // Also reset the tracker for escapes: we won't be hitting any right now
      // and the next round is the first time an 'escape' character can be seen again at the input.
      $prevChar = '';
      $result .= $copyStr;
      $i += $copyLen - 1;      // correct for the for(;;) loop
      continue;
    }
    
    // Grab the next character in the string
    $char = substr($json, $i, 1);
    
    // Are we inside a quoted string encountering an escape sequence?
    if (!$outOfQuotes && $prevChar === '\\') {
      // Add the escaped character to the result string and ignore it for the string enter/exit detection:
      $result .= $char;
      $prevChar = '';
      continue;
    }
    // Are we entering/exiting a quoted string?
    if ($char === '"' && $prevChar !== '\\') {
      $outOfQuotes = !$outOfQuotes;
    }
    // If this character is the end of an element,
    // output a new line and indent the next line
    else if ($outOfQuotes && ($char === '}' || $char === ']')) {
      $result .= $newLine;
      $pos--;
      for ($j = 0; $j < $pos; $j++) {
        $result .= $indentStr;
      }
    }
    // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
    else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
      continue;
    }

    // Add the character to the result string
    $result .= $char;
    // always add a space after a field colon:
    if ($outOfQuotes && $char === ':') {
      $result .= ' ';
    }

    // If the last character was the beginning of an element,
    // output a new line and indent the next line
    else if ($outOfQuotes && ($char === ',' || $char === '{' || $char === '[')) {
      $result .= $newLine;
      if ($char === '{' || $char === '[') {
        $pos++;
      }
      for ($j = 0; $j < $pos; $j++) {
        $result .= $indentStr;
      }
    }
    $prevChar = $char;
  }

  return $result;
}


?>
