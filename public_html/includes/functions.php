<?php

// TODO : Move to Object class
function get_select_by($id,$field) {
  $object = objects::get($id);
  return($object['data'][$field]);
}

// TODO : move to object class
function get_multiselect_by($ids,$field) {
  $return = array();

  foreach ((array)$ids as $id) {
      $return[] = get_select_by($id,$field);
  }

  return $return;
}

// TODO : this should be moved to the web_form class
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

// TODO : I think this function can be safely removed.
// if $increment is true it returns the NEXT number. if it is false it returns the current
function getIDNO($formID,$projectID,$increment=TRUE) {
return mfcs::getIDNO($formID,$increment);
}

// TODO remove this function. Be sure to check exports and migration scripts for usage
// This function handles some translation errors that commonly occure during cutting and pasting.
function convertString($string) {
	return string_utils::convert_string($string);
}

// TODO get_heading_by_id and link_select_to_objects need moved to the export (or perhaps a new OAI) class
// They also need to be made to work better in maps
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
	if ($object['publicRelease'] == "No") return;
	return link_select_to_objects($object);
}

// TODO : this function should be removed
function json_format($json) {
  return string_utils::json_format($json);
}


?>
