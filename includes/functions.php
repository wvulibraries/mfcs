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

function getObject($objectID) {
	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `objects` WHERE `ID`='%s'",
		$engine->openDB->escape($objectID)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
		return(FALSE);
	}
	
	return(mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC));
}

function checkObjectInForm($formID,$objectID) {

	$object = getObject($objectID);

	if ($object['formID'] == $formID) {
		return(TRUE);
	}

	return(FALSE);

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

function buildForm($formID,$projectID,$objectID = NULL) {

	$engine = EngineAPI::singleton();

	// Get the current Form
	$form   = getForm($formID);

	if ($form === FALSE) {
		return(FALSE);
	}

	$fields = decodeFields($form['fields']);

	if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
		errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		return(FALSE);
	}

	if (!isnull($objectID)) {
		$object = getObject($objectID);
		if ($object === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return(FALSE);
		}
		$object['data'] = decodeFields($object['data']);
		if ($object['data'] === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return(FALSE);
		}
	}

	// print "<pre>";
	// var_dump($form);
	// print "</pre>";

	// print "<pre>";
	// var_dump($fields);
	// print "</pre>";

	// print "<pre>";
	// var_dump($object);
	// print "</pre>";
    

	$output = sprintf('<form action="%s?id=%s&amp;formID=%s" method="%s">',
		$_SERVER['PHP_SELF'],
		htmlSanitize($projectID),
		htmlSanitize($formID),
		"post"
		);

	$output .= sessionInsertCSRF();

	$output .= sprintf('<header><h1>%s</h1><h2>%s</h2></header>',
		htmlSanitize($form['title']),
		htmlSanitize($form['description']));

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
			$currentFieldset = $field['fieldset'];
		}	


		// build the actual input box
		
		$output .= '<div class="">';


		$output .= sprintf('<label for="%s">%s</label>',
			htmlSanitize($field['id']),
			htmlSanitize($field['label'])
			);

		if ($field['type']      == "textarea") {
			$output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s" %s %s %s %s>%s</textarea>',
				htmlSanitize($field['name']),
				htmlSanitize($field['placeholder']),
				htmlSanitize($field['id']),
				htmlSanitize($field['class']),
				(!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
				//true/false type attributes
				(uc($field['required']) == "TRUE")?"required":"",
				(uc($field['readonly']) == "TRUE")?"readonly":"", 
				(uc($field['disabled']) == "TRUE")?"disabled":"",
				(isset($object['data'][$field['name']]))?htmlSanitize($object['data'][$field['name']]):htmlSanitize($field['value'])
				);

		}
		else if ($field['type'] == "checkbox" || $field['type'] == "radio") {

		// }
		// else if ($field['type'] == "radio") {
			// Manually selected
			if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
				if (isempty($field['choicesOptions'])) {
					errorHandle::errorMsg("No options provided for radio buttons, '".$field['label']."'");
					return(FALSE);
				}

				foreach ($field['choicesOptions'] as $I=>$option) {
					$output .= sprintf('<input type="%s" name="%s" id="%s_%s" value="%s" %s/><label for="%s_%s">%s</label>',
						htmlSanitize($field['type']),
						htmlSanitize($field['name']),
						htmlSanitize($field['name']),
						htmlSanitize($I),
						htmlSanitize($option),
						(!isempty($field['choicesDefault']) && $field['choicesDefault'] == $option)?'checked="checked"':"",
						htmlSanitize($field['name']),
						htmlSanitize($I),
						htmlSanitize($option)
						);
				}

			}
			else {
				$sql       = sprintf("SELECT * FROM `objects` WHERE formID='%s' and metadata='1'",
					$engine->openDB->escape($field['choicesForm'])
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
					return(FALSE);
				}

				$count = 0;
				while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
					$row['data'] = decodeFields($row['data']);

					$output .= sprintf('<input type="checkbox" name="%s" id="%s_%s" value="%s"/><label for="%s_%s">%s</label>',
						htmlSanitize($field['type']),
						htmlSanitize($field['name']),
						htmlSanitize($field['name']),
						htmlSanitize(++$count),
						htmlSanitize($row['ID']),
						htmlSanitize($field['name']),
						htmlSanitize($count),
						htmlSanitize($row['data'][$field['choicesField']])
						);
				}

			}
		}
		else if ($field['type'] == "select") {
			$output .= sprintf('<select name="%s" id="%s">',
				htmlSanitize($field['name']),
				htmlSanitize($field['name'])
				);

			// Manually selected
			if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
				if (isempty($field['choicesOptions'])) {
					errorHandle::errorMsg("No options provided for radio buttons, '".$field['label']."'");
					return(FALSE);
				}

				foreach ($field['choicesOptions'] as $I=>$option) {
					$output .= sprintf('<option value="%s" %s/>%s</option>',
						htmlSanitize($option),
						(!isempty($field['choicesDefault']) && $field['choicesDefault'] == $option)?'checked="checked"':"",
						htmlSanitize($option)
						);
				}

			}
			// Pull from another Form
			else {

				$sql       = sprintf("SELECT * FROM `objects` WHERE formID='%s' and metadata='1'",
					$engine->openDB->escape($field['choicesForm'])
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
					return(FALSE);
				}

				while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

					$row['data'] = decodeFields($row['data']);

					$output .= sprintf('<option value="%s" />%s</option>',
						htmlSanitize($row['ID']),
						htmlSanitize($row['data'][$field['choicesField']])
						);
				}

			}

			$output .= "</select>";

		}
		else {
			$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s %s />',
				htmlSanitize($field['type']),
				htmlSanitize($field['name']),
				(isset($object['data'][$field['name']]))?htmlSanitize($object['data'][$field['name']]):htmlSanitize($field['value']),
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
		htmlSanitize($form["submitButton"]),
		"submitForm"
		);

	$output .= "</form>";

	return($output);

}


// NOTE: data is being saved as RAW from the array. 
function submitForm($project,$formID,$objectID=NULL) {

	$engine = EngineAPI::singleton();

	// Get the current Form
	$form   = getForm($formID);

	if ($form === FALSE) {
		return(FALSE);
	}

	$fields = decodeFields($form['fields']);
	print "<pre>";
	var_dump($fields);
	print "</pre>";

	if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
		errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		return(FALSE);
	}

	$values = array();

	// go through all the fields, get their values
	foreach ($fields as $field) {

		if ($field['type'] == "fieldset") {
			continue;
		}

		// perform validations here
		if (isempty($field['validation']) || $field['validation'] == "none") {
			$valid = TRUE;
		}
		else {
			$return = validate::isValidMethod($field['validation']);
			$valid  = FALSE;
			if ($return === TRUE) {
				if ($field['validation'] == "regexp") {
					$valid = validate::$field['validation']($field['validationRegex'],$field['value']);
				}
				else {
					$valid = validate::$field['validation']($engine->cleanPost['RAW'][$field['name']]);
				}
			}
		}

		if ($valid === FALSE) {
			errorHandle::errorMsg("Invalid data provided in field '".$field['label']."'.");
			continue;
		}

		$values[$field['name']] = $engine->cleanPost['RAW'][$field['name']];

	}

	if (!is_empty($engine->errorStack)) {
		return(FALSE);
	}

	if (isnull($objectID)) {
		$sql       = sprintf("INSERT INTO `objects` (parentID,formID,defaultProject,data,metadata) VALUES('%s','%s','%s','%s','%s')",
			isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($project['ID']),
			encodeFields($values),
			$engine->openDB->escape($form['metadata'])
			);
	}
	else {
		// start transactions
		
		// place old version into revision control
		
		// insert new version
		
		// end transactions
	}
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return(FALSE);
	}
	

	return(TRUE);
}

?>