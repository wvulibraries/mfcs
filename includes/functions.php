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

function checkProjectPermissions($id) {

	$engine = EngineAPI::singleton();

	$username = sessionGet("username");

	$sql       = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.projectID='%s' AND users.username='%s'",
		$engine->openDB->escape($id),
		$engine->openDB->escape($username)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return(FALSE);
	}
	
	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	if ((int)$row['COUNT(permissions.ID)'] > 0) {
		return(TRUE);
	}

	return(FALSE);

}

// returns the database object for the project ID
// we need to add caching to this, once caching is moved from EngineCMS to EngineAPI
function getProject($projectID) {

	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s'",
		$engine->openDB->escape($projectID)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return(FALSE);
	}
	
	return(mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC));

}

function getForm($formID) {

	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `forms` WHERE `ID`='%s'",
		$engine->openDB->escape($formID)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return(FALSE);
	}
	
	return(mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC));
}

function checkFormInProject($projectID,$formID) {

	$project = getProject($projectID);

	if (!is_empty($project['forms'])) {

		$currentForms = decodeFields($project['forms']);

		foreach ($currentForms['metadata'] as $I=>$V) {
			if ($V == $formID) {
				return(TRUE);
			}
		}
		foreach ($currentForms['objects'] as $I=>$V) {
			if ($V == $formID) {
				return(TRUE);
			}
		}
	}

	return(FALSE);

}

function sortFieldsByPosition($a,$b) {
    return strnatcmp($a['position'], $b['position']);
}

function buildNumberAttributes($field) {

	$output = "";
	$output .= (!isempty($field["min"])) ?' min="'.$field['min'].'"'  :"";
	$output .= (!isempty($field["max"])) ?' max="'.$field['max'].'"'  :"";
	$output .= (!isempty($field["step"]))?' step="'.$field['step'].'"':"";

	return($output);

}

function buildForm($formID) {

	$engine = EngineAPI::singleton();

	// Get the current Form
	$sql       = sprintf("SELECT * FROM `forms` WHERE `ID`='%s'",
		$engine->openDB->escape($formID)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		return(FALSE);
	}
	
	$form   = getForm($formID);

	if ($form === FALSE) {
		return(FALSE);
	}

	$fields = decodeFields($form['fields']);


	if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
		errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		throw new Exception('Error');
	}

	print "<pre>";
	var_dump($form);
	print "</pre>";

	print "<pre>";
	var_dump($fields);
	print "</pre>";


	$output = sprintf('<form action="%s" method="%s">',
		$_SERVER['PHP_SELF'],
		"post"
		);

	$currentFieldset = "";

	foreach ($fields as $field) {

		if ($field['type'] == "fieldset") {
			continue;
		}

		// deal with field sets
		if ($field['fieldset'] != $currentFieldset) {
			if ($currentFieldset != "") {
				$output .= "</fieldset>";
			}
			if (!isempty($field['fieldset'])) {
				$output .= sprintf('<fieldset><legend>%s</legend>',
					$field['fieldset']
					);
			}
		}	


		// build the actual input box
		
		$output .= '<div class="">';

		$output .= sprintf('<label for="%s">%s</label>',
			htmlSanitize($field['id']),
			htmlSanitize($field['label'])
			);

		if ($field['type']      == "textarea") {

		}
		else if ($field['type'] == "checkbox") {

		}
		else if ($field['type'] == "radio") {

		}
		else if ($field['type'] == "select") {

		}
		else {
			$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s %s />',
				htmlSanitize($field['type']),
				htmlSanitize($field['name']),
				htmlSanitize($field['value']),
				htmlSanitize($field['placeholder']),
				//for numbers
				($field['type'] == "number")?(buildNumberAttributes($field)):"",
				htmlSanitize($field['id']),
				htmlSanitize($field['class']),
				(!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
				//true/false type attributes
				(uc($field['required']) == "TRUE")?"required":"",
				(uc($field['readonly']) == "TRUE")?"readonly":"", 
				(uc($field['disabled']) == "TRUE")?"disabled":""
				);
		}

		$output .= "</div>";
	}

	if (!isempty($currentFieldset)) {
		$output .= "</fieldset>";
	}
	
	$output .= sprintf('<input type="submit" value="%s" name="%s" />',
		"Submit",
		"submitForm"
		);

	$output .= "</form>";

	return($output);

}

?>