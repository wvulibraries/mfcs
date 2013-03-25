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
		return FALSE;
	}

	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	if ((int)$row['COUNT(permissions.ID)'] > 0) {
		return TRUE;
	}

	return FALSE;

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
		return FALSE;
	}

	return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

}

function getForm($formID) {

	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `forms` WHERE `ID`='%s'",
		$engine->openDB->escape($formID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return FALSE;
	}

	return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
}

function getObject($objectID) {
	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `objects` WHERE `ID`='%s'",
		$engine->openDB->escape($objectID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
		return FALSE;
	}

	return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
}

function getAllObjectsForForm($formID) {

	$engine = EngineAPI::singleton();

	$sql       = sprintf("SELECT * FROM `objects` WHERE `formID`='%s'",
		$engine->openDB->escape($formID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - getting all objects: ".$sqlResult['error'], errorHandle::DEBUG);
		return FALSE;
	}

	$objects = array();
	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		$row['data'] = decodeFields($row['data']);
		$objects[] = $row;

	}

	return $objects;

}

function checkObjectInForm($formID,$objectID) {

	$object = getObject($objectID);

	if ($object['formID'] == $formID) {
		return TRUE;
	}

	return FALSE;

}

function checkFormInProject($projectID,$formID) {

	$project = getProject($projectID);

	if (!is_empty($project['forms'])) {

		$currentForms = decodeFields($project['forms']);

		foreach ($currentForms['metadata'] as $I=>$V) {
			if ($V == $formID) {
				return TRUE;
			}
		}
		foreach ($currentForms['objects'] as $I=>$V) {
			if ($V == $formID) {
				return TRUE;
			}
		}
	}

	return FALSE;

}

function sortFieldsByPosition($a,$b) {
    return strnatcmp($a['position'], $b['position']);
}

function buildProjectNavigation($projectID) {
	$project = getProject($projectID);

	if ($project === FALSE) {
		return(FALSE);
	}

	$nav = decodeFields($project['groupings']);

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
			$output .= sprintf('<a href="form.php?id=%s&amp;formID=%s">%s</a>',
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

function buildForm($formID,$projectID,$objectID = NULL) {

	$engine = EngineAPI::singleton();

	// Get the current Form
	$form   = getForm($formID);

	if ($form === FALSE) {
		return FALSE;
	}

	$fields = decodeFields($form['fields']);

	if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
		errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		return FALSE;
	}

	if (!isnull($objectID)) {
		$object = getObject($objectID);
		if ($object === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return FALSE;
		}
		$object['data'] = decodeFields($object['data']);
		if ($object['data'] === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return FALSE;
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


		if ($field['type'] != "idno" && (isset($field['managedBy']) && strtolower($field['managedBy']) != "system")) {
			$output .= sprintf('<label for="%s">%s</label>',
				htmlSanitize($field['id']),
				htmlSanitize($field['label'])
				);
		}

		if ($field['type']      == "textarea" || $field['type']      == "wysiwyg") {
			$output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s" %s %s %s %s>%s</textarea>',
				htmlSanitize($field['name']),
				htmlSanitize($field['placeholder']),
				htmlSanitize($field['id']),
				htmlSanitize($field['class']),
				(!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
				//true/false type attributes
				(strtoupper($field['required']) == "TRUE")?"required":"",
				(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
				(strtoupper($field['disabled']) == "TRUE")?"disabled":"",
				(isset($object['data'][$field['name']]))?htmlSanitize($object['data'][$field['name']]):htmlSanitize($field['value'])
				);

			if ($field['type'] == "wysiwyg") {
				$output .= sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%s/includes/js/CKEditor/"</script>',
					localvars::get("siteRoot")
					);
				$output .= sprintf('<script type="text/javascript" src="%s/includes/js/CKEditor/ckeditor.js"></script>',
					localvars::get("siteRoot")
					);
				$output .= '<script type="text/javascript">';
				$output .= sprintf('if (CKEDITOR.instances["%s"]) { CKEDITOR.remove(CKEDITOR.instances["%s"]); }',
					htmlSanitize($field['id']),
					htmlSanitize($field['id'])
					);
				$output .= sprintf('CKEDITOR.replace("%s");',
					htmlSanitize($field['id'])
					);

				$output .= 'htmlParser = "";';
				$output .= 'if (CKEDITOR.instances["'.$I['field'].'_insert"].dataProcessor) {';
				$output .= sprintf('    htmlParser = CKEDITOR.instances["%s"].dataProcessor.htmlFilter;',
					htmlSanitize($field['id'])
					);
				$output .= '}';

				$output .= '</script>';
			}

		}
		else if ($field['type'] == "checkbox" || $field['type'] == "radio") {

		// }
		// else if ($field['type'] == "radio") {
			// Manually selected
			if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
				if (isempty($field['choicesOptions'])) {
					errorHandle::errorMsg("No options provided for radio buttons, '".$field['label']."'");
					return FALSE;
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
					return FALSE;
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
					return FALSE;
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
					return FALSE;
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
		else if ($field['type'] == 'file') {

			$output .= sprintf('<div id="fineUploader_%s"></div>',
				htmlSanitize($field['name'])
				);
			$output .= sprintf('
				<script type="text/javascript">
					$("#fineUploader_%s")
						.fineUploader({
							request: {
								endpoint: "{local var="siteRoot"}includes/uploader.php",
								params: {
									engineCSRFCheck: "{engine name="csrf" insert="false"}",
								}
							},
							multiple: %s,
							validation: {
								allowedExtensions: ["%s"],
							},
							text: {
							  uploadButton: \'<i class="icon-plus icon-white"></i> Select Files\'
							},
							showMessage: function(message) {
								$("#fineUploader_%s .qq-upload-list").append(\'<li class="alert alert-error">\' + message + \'</li >\');
							},
							classes: {
								success: "alert alert-success",
								fail: "alert alert-error"
							},
						})
						.on("complete", function(event,id,fileName,responseJSON) {
							$("#fineUploader_%s").after(\'<input type="hidden" name="%s[]" value="\'+responseJSON.uploadName+\'">\');
						});
				</script>',
				htmlSanitize($field['name']),
				(strtoupper($field['multipleFiles']) == "TRUE") ? "true" : "false",
				implode('", "',$field['allowedExtensions']),
				htmlSanitize($field['name']),
				htmlSanitize($field['name']),
				htmlSanitize($field['name'])
				);

		}
		else {
			if ($field['type'] == "idno") {
				if (strtolower($field['managedBy']) == "system") continue;
				$field['type'] = "text";
			}

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
				(strtoupper($field['required']) == "TRUE")?"required":"",
				(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
				(strtoupper($field['disabled']) == "TRUE")?"disabled":""
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

	return $output;

}

function buildListTable($objects,$form,$projectID) {

	$form['fields'] = decodeFields($form['fields']);

	$header  = '<tr><th>Delete</th><th>Edit</th><th>ID No</th>';
	$headers = array();
	foreach ($form['fields'] as $field) {

		if (strtolower($field['type']) == "idno") continue;

		if ($field['displayTable'] == "true") {
			$header .= sprintf('<th>%s</th>',
				$field['label']
				);
			$headers[$field['name']] = $field['label'];
		}
	}
	$header .= '</tr>';

	$output = sprintf('<form action="%s?id=%s&amp;formID=%s" method="%s">',
		$_SERVER['PHP_SELF'],
		htmlSanitize($projectID),
		htmlSanitize($form['ID']),
		"post"
		);
	$output .= sessionInsertCSRF();

	$output .= '<table>';
	$output .= $header;

	foreach($objects as $object) {
		$output .= "<tr>";
		$output .= sprintf('<td><input type="checkbox" name="delete_%s" /></td>',
			$object['ID']
			);
		$output .= sprintf('<td><a href="form.php?id=%s&amp;formID=%s&amp;objectID=%s">Edit</a></td>',
			htmlSanitize($projectID),
			htmlSanitize($form['ID']),
			htmlSanitize($object['ID'])
			);
		$output .= sprintf('<td>%s</td>',
			htmlSanitize(($form['metadata'] == "1")?$object['ID']:$object['idno'])
			);
		foreach ($headers as $headerName => $headerLabel) {
			$output .= sprintf('<td>%s</td>',
				htmlSanitize($object['data'][$headerName])
				);
		}
		$output .= "</tr>";

	}


	$output .= '</table>';

	$output .= "</form>";

	return $output;

}

// NOTE: data is being saved as RAW from the array.
function submitForm($project,$formID,$objectID=NULL) {

	$engine = EngineAPI::singleton();

	if (isnull($objectID)) {
		$newObject = TRUE;
	}
	else {
		$newObject = FALSE;
	}

	// Get the current Form
	$form   = getForm($formID);

	if ($form === FALSE) {
		return FALSE;
	}

	// the form is an object form, make sure that it has an ID field defined.
	if ($form['metadata'] == "0") {
		$idnoInfo = getFormIDInfo($formID);
		if ($idnoInfo === FALSE) {
			errorHandle::newError(__METHOD__."() - no IDNO field for object form.", errorHandle::DEBUG);
			return(FALSE);
		}
	}

	$fields = decodeFields($form['fields']);

	if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
		errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving form.");
		return FALSE;
	}

	$values = array();

	// go through all the fields, get their values
	foreach ($fields as $field) {

		if ($field['type'] == "fieldset" || $field['type'] == "idno" || $field['disabled'] == "true") continue;

		if (strtolower($field['required']) == "true"           &&
			(!isset($engine->cleanPost['RAW'][$field['name']]) ||
			 isempty($engine->cleanPost['RAW'][$field['name']]))
			) {

			errorHandle::errorMsg("Missing data for required field '".$field['label']."'.");
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

		// Duplicate Checking (Project)
		if (strtolower($field['duplicates']) == "true") {
			if (isDupe($formID,$project['ID'],$field['name'],$engine->cleanPost['RAW'][$field['name']])) {
				errorHandle::errorMsg("Duplicate data (in Project) provided in field '".$field['label']."'.");
				continue;
			}
		}
		// Duplicate Checking (Form)
		if (strtolower($field['duplicatesForm']) == "true") {
			if (isDupe($formID,NULL,$field['name'],$engine->cleanPost['RAW'][$field['name']])) {
				errorHandle::errorMsg("Duplicate data (in form) provided in field '".$field['label']."'.");
				continue;
			}
		}

		if (strtolower($field['readonly']) == "true") {
			// need to pull the data that loaded with the form
			if ($newObject === TRUE) {
				// grab it from the database
				$oldObject              = getObject($objectID);
				$object['data']         = decodeFields($object['data']);
				$values[$field['name']] = $object['data'][$field['name']];
			}
			else {
				// grab the default value from the form.
				$values[$field['name']] = $field['value'];
			}
		}

		$values[$field['name']] = $engine->cleanPost['RAW'][$field['name']];

	}

	if (!is_empty($engine->errorStack)) {
		return FALSE;
	}

	// start transactions
	$result = $engine->openDB->transBegin("objects");
	if ($result !== TRUE) {
		errorHandle::errorMsg("Database transactions could not begin.");
		errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
		return FALSE;
		}

	if ($newObject === TRUE) {
		$sql       = sprintf("INSERT INTO `objects` (parentID,formID,defaultProject,data,metadata,modifiedTime) VALUES('%s','%s','%s','%s','%s','%s')",
			isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($project['ID']),
			encodeFields($values),
			$engine->openDB->escape($form['metadata']),
			time()
			);
	}
	else {

		// place old version into revision control
		$return = $rcs->insertRevision($objectID);

		if ($return !== TRUE) {

			$engine->openDB->transRollback();
			$engine->openDB->transEnd();

			errorHandle::errorMsg("Error inserting revision.");
			errorHandle::newError(__METHOD__."() - unable to insert revisions", errorHandle::DEBUG);
			return FALSE;
		}

		// insert new version
		$sql = sprintf("UPDATE `objects` SET `parentID`='%s', `formID`='%s', `defaultProject`='%s', `data`='%s', `metadata`='%s', `modifiedTime`='%s') WHERE `ID`='%s'",
			isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($project['ID']),
			encodeFields($values),
			$engine->openDB->escape($form['metadata']),
			time(),
			$engine->openDB->escape($objectID)
			);


	}

	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();

		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return FALSE;
	}

	if ($newObject === TRUE) {
		$objectID = $sqlResult['id'];
	}

	// Check to see if this object already exists in the objectProjects table. If not, add it.
	$sql       = sprintf("SELECT COUNT(*) FROM `objectProjects` WHERE `objectID`='%s' AND `projectID`='%s'",
		$engine->openDB->escape($objectID),
		$engine->openDB->escape($project['ID'])
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();

		errorHandle::newError(__METHOD__."() - error getting count: ".$sqlResult['error'], errorHandle::DEBUG);
		return FALSE;
	}

	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	if ($row['COUNT(*)'] == 0) {
		$sql       = sprintf("INSERT INTO `objectProjects` (objectID,projectID) VALUES('%s','%s')",
			$engine->openDB->escape($objectID),
			$engine->openDB->escape($project['ID'])
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
			return FALSE;
		}

	}



	// if it is an object form (not a metadata form)
	// do the IDNO stuff
	if ($form['metadata'] == "0") {
			// increment the project counter
		$sql       = sprintf("UPDATE `projects` SET `count`=`count`+'1' WHERE `ID`='%s'",
			$engine->openDB->escape($project['ID'])
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - Error incrementing project counter: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

			// if the idno is managed by the system get a new idno
		if ($idnoInfo['managedBy'] == "system") {
			$idno = $engine->openDB->escape(getIDNO($formID,$project['ID']));
		}
			// the idno is managed manually
		else {
			$idno = $engine->cleanPost['MYSQL']['idno'];
		}

			// update the object with the new idno
		$sql       = sprintf("UPDATE `objects` SET `idno`='%s' WHERE `ID`='%s'",
				$idno, // Cleaned above when assigned
				$engine->openDB->escape($objectID)
				);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - updating the IDNO: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

	}

	if ($newObject === FALSE) {
			// update the object with a new idno if it is managed manually
			// update all the fields in the dupeMatching Table

			// delete all matching fields
		$sql       = sprintf("DELETE FROM `dupeMatching` WHERE `formID`='%s' AND `objectID`='%s' AND `projectID`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($objectID),
			$engine->openDB->escape($project['ID'])
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			errorHandle::newError(__METHOD__."() - removing from duplicate table: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

	}

		// insert all the fields into the dupeMatching table
	foreach ($values as $name=>$raw) {
		$sql       = sprintf("INSERT INTO `dupeMatching` (`formID`,`projectID`,`objectID`,`field`,`value`) VALUES('%s','%s','%s','%s','%s')",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($project['ID']),
			$engine->openDB->escape($objectID),
			$engine->openDB->escape($name),
			$engine->cleanPost['MYSQL'][$name]
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
	}




	// end transactions
	$engine->openDB->transCommit();
	$engine->openDB->transEnd();

	return TRUE;
}

// $value must be RAW
function isDupe($formID,$projectID=NULL,$field,$value) {

	$engine = EngineAPI::singleton();

	if (isnull($projectID)) {
		$sql = sprintf("SELECT COUNT(*) FROM dupeMatching WHERE `formID`='%s' AND `field`='%s' AND `value`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($field),
			$engine->openDB->escape($value)
			);
	}
	else {
		$sql = sprintf("SELECT COUNT(*) FROM dupeMatching WHERE `formID`='%s' AND `projectID`='%s' AND `field`='%s' AND `value`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($projectID),
			$engine->openDB->escape($field),
			$engine->openDB->escape($value)
			);
	}

	$sqlResult = $engine->openDB->sqlResult($sql);

	// we return TRUE on Error, because if a dupe is encountered we want it to fail out.
	if ($sqlResult['result'] === FALSE) {
		return TRUE;
	}
	else if ((INT)$sqlResult['result']['COUNT(*)'] > 0) {
		return TRUE;
	}
	else {
		return FALSE;
	}

}

function getFormIDInfo($formID) {
	$form = getForm($formID);

	return decodeFields($form['idno']);
}

// if $increment is true it returns the NEXT number. if it is false it returns the current
function getIDNO($formID,$projectID,$increment=TRUE) {

	$engine         = EngineAPI::singleton();
	$idno           = getFormIDInfo($formID);

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT `count` FROM `projects` WHERE `ID`='%s'",
			$engine->openDB->escape($projectID)
			)
		);

	if (!$sqlResult['result']) {
		return FALSE;
	}

	$idno                         = $idno['idnoFormat'];
	$len                          = strrpos($idno,"#") - strpos($idno,"#") + 1;
	$sqlResult['result']['count'] = str_pad($sqlResult['result']['count'],$len,"0",STR_PAD_LEFT);
	$idno                         = preg_replace("/#+/", $sqlResult['result']['count'], $idno);

	return $idno;
}


// if $increment is true it returns the NEXT number. if it is false it returns the current
function dumpStuff($formID,$projectID,$increment=TRUE) {

	$engine         = EngineAPI::singleton();

	$form           = getForm($formID);
	$form['fields'] = decodeFields($form['fields']);
	$idno           = getFormIDInfo($formID);

	print "<pre>";
	var_dump($form['fields']);
	print "</pre>";

	print "<pre>";
	var_dump($idno);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT `count` FROM `projects` WHERE `ID`='%s'",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("SELECT * FROM `projects`",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("INSERT INTO containers (containerName) VALUES('foo')",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	$sqlResult = $engine->openDB->sqlResult(
		sprintf("UPDATE containers SET containerName='bar' WHERE ID='1'",
			$engine->openDB->escape($projectID)
			)
		);

	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";

	return TRUE;
}


/**
 * Checks if the path exists and attempts to correct problems.
 *
 * @param string $path
 * @return bool
 * @author Scott Blake
 **/
function prepareUploadDir($path) {
	$permissions = 0755;

	if (!is_dir($path)) {
		mkdir($path.'/originals', $permissions, TRUE);
		mkdir($path.'/chunks',    $permissions, TRUE);
		mkdir($path.'/thumbs',    $permissions, TRUE);
		mkdir($path.'/converted', $permissions, TRUE);
	}
	else if (!is_writable($path)) {
		if (chmod($path, $permissions)) {
			chmod($path.'/originals', $permissions);
			chmod($path.'/chunks',    $permissions);
			chmod($path.'/thumbs',    $permissions);
			chmod($path.'/converted', $permissions);
		}
		else {
			return FALSE;
		}
	}

	return TRUE;
}

?>