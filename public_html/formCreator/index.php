<?php

// @TODO there is way too much logic in this file. It needs to be refactored out.
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

// for determining if the page has had an error
$formCreationError = FALSE;

$formID = isset($engine->cleanPost['HTML']['id']) ? $engine->cleanPost['HTML']['id'] : (isset($engine->cleanGet['HTML']['id']) ? $engine->cleanGet['HTML']['id'] : NULL);
if (is_empty($formID)) {
	$formID = NULL;
}

log::insert("Form Creator: Edit Forms",0,$formID);

if(isset($engine->cleanPost['MYSQL']['deleteForm'])){
	forms::delete($engine->cleanGet['HTML']['id']);
	http::redirect(localvars::get('siteRoot').'formCreator/list.php',301);
}

if (isset($engine->cleanPost['MYSQL']['submitNavigation'])) {
	try{
		if (navigation::updateFormNav($engine->cleanPost['RAW']['groupings']) === FALSE) {
			throw new Exception("Error saving navigation");
		}

		errorHandle::successMsg("Successfully updated Form Navigation.");
	}
	catch (Exception $e) {
		errorHandle::newError("{$e->getFile()}:{$e->getLine()} {$e->getMessage()}", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Updating Navigation");
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
	}
}

if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
	$engine->openDB->transBegin();

	$form   = json_decode($engine->cleanPost['RAW']['form'], TRUE);
	$fields = json_decode($engine->cleanPost['RAW']['fields'], TRUE);
	$idno   = NULL;

	// Ensure all fields have an ID for the label. Assign it the value of name if needed.
	if (!is_empty($fields)) {
		$count = NULL;
		foreach ($fields as $I => $field) {
			$positions[$I] = $field['position'];

			if (is_empty($field['id'])) {
				$fields[$I]['id'] = $field['name'];
			}

			if ($field['type'] == 'idno') {
				$idno        = $field;
				$idnoConfirm = $field['idnoConfirm'];

				$count = (isnull($count)) ? $field['startIncrement']-1 : $count;
				if ($count < 0) {
					$count = 0;
				}
			}
			else if (isset($field['choicesType']) && $field['choicesType'] == 'manual') {
				unset($fields[$I]['choicesForm']);
				unset($fields[$I]['choicesField']);
			}
			else if (isset($field['choicesType']) && $field['choicesType'] == 'form') {
				unset($fields[$I]['choicesDefault']);
				unset($fields[$I]['choicesOptions']);
			}
		}

		array_multisort($positions, SORT_ASC, $fields);

		$query['form']   = $form;
		$query['fields'] = $fields;
		$query['idno']   = $idno;
		$query['count']  = $count;
	}

	if (!isnull($formID)) {
		// Only add count if confirmation is checked
		if (str2bool($idnoConfirm)) {
			$countSql = sprintf("`count`='%s',",
				$engine->openDB->escape($count)
			);
		} else {
			$countSql = "";
		}

		if ($form['formMetadata'] == '1' && !is_empty($form['linkTitle'])) {
			// Test linkTable uniqueness
			$sql = sprintf("SELECT `linkTitle` FROM `forms` WHERE `linkTitle`='%s' AND `ID`!='%s'",
				$engine->openDB->escape($form['linkTitle']),
				$engine->openDB->escape($formID)
				);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				// SQL error
				errorHandle::newError(__METHOD__."() - detecting linkTitle uniqueness: ".$sqlResult['error'], errorHandle::DEBUG);
			}
			else if ($sqlResult['numRows'] > 0) {
				// Not unique
				errorHandle::errorMsg("Link Title must be unique.");
				$form['linkTitle'] = '';
			}
		}
		else {
			// Blank linkTable on object forms
			$form['linkTitle'] = '';
		}

		// Update forms table
		$sql = sprintf("UPDATE `forms`
						SET `title`='%s',
							`description`=%s,
							`fields`='%s',
							`idno`='%s',
							`submitButton`='%s',
							`updateButton`='%s',
							`container`='%s',
							`production`='%s',
							`metadata`='%s',
							`exportPublic`='%s',
							`exportOAI`='%s',
							`objPublicReleaseShow`='%s',
							`objPublicReleaseDefaultTrue`='%s',
							%s
							`displayTitle`='%s',
							`objectTitleField`='%s',
							`linkTitle`=%s
						WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($form['formTitle']),          // title=
			!is_empty($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL", // description=
			encodeFields($fields),                                // fields=
			encodeFields($idno),                                  // idno=
			$engine->openDB->escape($form['submitButton']),       // submitButton=
			$engine->openDB->escape($form['updateButton']),       // updateButton=
			$engine->openDB->escape($form['formContainer']),      // container=
			$engine->openDB->escape($form['formProduction']),     // production=
			$engine->openDB->escape($form['formMetadata']),       // metadata=
			$engine->openDB->escape($form['exportPublic']),
			$engine->openDB->escape($form['exportOAI']),
			$engine->openDB->escape($form['objPublicReleaseShow']),
			$engine->openDB->escape($form['objPublicReleaseDefaultTrue']),
			$countSql,                                            // count=
			(is_empty($engine->openDB->escape($form['objectDisplayTitle'])) ? $engine->openDB->escape($form['formTitle']) : $engine->openDB->escape($form['objectDisplayTitle'])),
			// displayTitle
			$engine->openDB->escape($form['objectTitleField']),   // objectTitleField=
			!is_empty($form['linkTitle']) ? "'".$engine->openDB->escape($form['linkTitle'])."'" : "NULL", // linkTitle=
			$engine->openDB->escape($formID)                      // ID=
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - updating form: ".$sqlResult['error'], errorHandle::DEBUG);
			errorHandle::errorMsg("Failed to update form.");
		}
	}
	else {
		// Insert into forms table
		$sql = sprintf("INSERT INTO `forms` (title, description, fields, idno, submitButton, updateButton, container, production, metadata, exportPublic, exportOAI, objPublicReleaseShow, objPublicReleaseDefaultTrue, count, displayTitle, objectTitleField, linkTitle) VALUES ('%s',%s,'%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s', '%s','%s','%s','%s',%s)",
			$engine->openDB->escape($form['formTitle']),
			isset($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL",
			encodeFields($fields),
			encodeFields($idno),
			$engine->openDB->escape($form['submitButton']),
			$engine->openDB->escape($form['updateButton']),
			$engine->openDB->escape($form['formContainer']),
			$engine->openDB->escape($form['formProduction']),
			$engine->openDB->escape($form['formMetadata']),
			$engine->openDB->escape($form['exportPublic']),
			$engine->openDB->escape($form['exportOAI']),
			$engine->openDB->escape($form['objPublicReleaseShow']),
			$engine->openDB->escape($form['objPublicReleaseDefaultTrue']),
			$engine->openDB->escape($count),
			(is_empty($engine->openDB->escape($form['objectDisplayTitle'])) ? $engine->openDB->escape($form['formTitle']) : $engine->openDB->escape($form['objectDisplayTitle'])),
			$engine->openDB->escape($form['objectTitleField']),
			!is_empty($form['linkTitle']) ? "'".$engine->openDB->escape($form['linkTitle'])."'" : "NULL" // linkTitle=
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - Inserting new form: ".$sqlResult['error']." == ".$sql, errorHandle::DEBUG);
			errorHandle::errorMsg("Failed to create form. Inserting new form caused the following errors: ".$sqlResult['error']);

			$formCreationError  = TRUE;
		}
		else {
			$formID = $sqlResult['id'];
			$formCreationError  = FALSE;
		}
	}

	if (!is_empty($engine->errorStack) || !$sqlResult['result']) {
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
	}
	else {
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
		errorHandle::successMsg("Successfully submitted form.");
		header("refresh:.5;url=/formCreator/index.php?id=$formID");
	}
}

if (isset($engine->cleanPost['MYSQL']['submitPermissions'])) {
	try{
		// trans: begin transaction
		$engine->openDB->transBegin();

		// update permissions
		if (mfcsPerms::delete($formID) === FALSE) {
			throw new Exception("MySQL Error - Wipe Permissions ({$sqlResult['error']} -- $sql)");
		}

		$tmp = array("selectedViewUsers"    => mfcs::AUTH_VIEW,
			         "selectedEntryUsers"   => mfcs::AUTH_ENTRY,
			         "selectedUsersAdmins"  => mfcs::AUTH_ADMIN,
			         "selectedContactUsers" => mfcs::AUTH_CONTACT
			         );

		foreach ($tmp as $I => $K) {

			if (!isset($engine->cleanPost['MYSQL'][$I]) || !is_array($engine->cleanPost['MYSQL'][$I])) continue;

			foreach ($engine->cleanPost['MYSQL'][$I] as $userID) {
				if (mfcsPerms::add($userID,$formID,$K) === FALSE) {
					throw new Exception("Error adding Permissions");
				}
			}
		}

		// If we get here then the permissions successfully updated!
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
		errorHandle::successMsg("Successfully updated Permissions");
	}
	catch (Exception $e) {
		errorHandle::newError("{$e->getFile()}:{$e->getLine()} {$e->getMessage()}", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Updating Project");
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
	}
}

if (isset($engine->cleanPost['MYSQL']['projectForm']) && forms::isMetadataForm($formID) === FALSE) {
	$engine->openDB->transBegin();

	if (!isset($engine->cleanPost['MYSQL']['projects'])) {
		// If no projects are set, we are deleting all the projects
		if (forms::deleteAllProjects($engine->cleanGet['MYSQL']['id']) === FALSE) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception("Error removing all projects from Object.");
		}
	}
	else {
		// There are changes.
		// Delete all the old ones
		if (forms::deleteAllProjects($engine->cleanGet['MYSQL']['id']) === FALSE) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception("Error removing all projects from Object.");
		}

		// Add All the new ones
		if (forms::addProjects($engine->cleanGet['MYSQL']['id'],$engine->cleanPost['MYSQL']['projects']) === FALSE) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception("Error adding projects to Object.");
		}

		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
	}
}

try {
	$tmp = NULL;
	$imagick = @Imagick::queryformats();
	foreach ($imagick as $format) {
		$tmp .= '<option value="'.$format.'">'.$format.'</option>';
	}
	localVars::add("conversionFormats",$tmp);
	unset($tmp);

	$tmp = NULL;
	foreach (array("top","middle","bottom") as $h) {
		foreach (array("left","center","right") as $w) {
			$tmp .= '<option value="'.$h.'|'.$w.'">'.ucfirst($h).' '.ucfirst($w).'</option>';
		}
	}
	localVars::add("imageLocations",$tmp);
	unset($tmp);

	$tmp = '<option value="">None</option>';
	foreach (validate::validationMethods() as $val => $text) {
		$tmp .= '<option value="'.$val.'">'.$text.'</option>';
	}
	localVars::add("validationTypes",$tmp);
	unset($tmp);

	// Get list of forms for choices dropdown
	if (($metadataForms = forms::getMetadataForms()) === FALSE) {
		throw new Exception("Errer retreiving metadata forms");
	}

	$tmp = array();
	if (is_array($metadataForms)) {
		$tmp[]  = '<option value="null"> Select a Form </option>';
		foreach ($metadataForms as $form) {
			$tmp[] = sprintf('<option value="%s">%s</option>',
				$form['ID'],
				$form['title']
				);
		}
	}
	localVars::add("formsOptions",implode(",",$tmp));
	unset($tmp);

	// Get list of watermarks for dropdown
	$sql = sprintf("SELECT `ID`, `name` FROM `watermarks`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		throw new Exception("Error retreiving watermarks");
	}

	$tmp = array();
	while ($row = mysqli_fetch_array($sqlResult['result'], MYSQLI_ASSOC)) {
		$tmp[] = sprintf('<option value="%s">%s</option>',
			$row['ID'],
			$row['name']
			);
	}
	localVars::add("watermarkList",implode("",$tmp));
	unset($tmp);
}
catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("thisSubmitButton","Add Form");

if (!isnull($formID) && $formCreationError === FALSE) {
	localVars::add("thisSubmitButton","Update Form");
	$form = forms::get($formID);
	if ($form !== FALSE) {
		$formPreview = NULL;

		localVars::add("formID",          htmlSanitize($form['ID']));
		localVars::add("formTitle",       htmlSanitize($form['title']));
		localVars::add("displayTitle",    htmlSanitize($form['displayTitle']));
		localVars::add("linkTitle",       htmlSanitize($form['linkTitle']));
		localVars::add("formDescription", htmlSanitize($form['description']));
		localVars::add("submitButton",    htmlSanitize($form['submitButton']));
		localVars::add("updateButton",    htmlSanitize($form['updateButton']));
		localVars::add("formContainer",   ($form['container'] == '1')  ? "checked" : "");
		localVars::add("formProduction",  ($form['production'] == '1') ? "checked" : "");
		localVars::add("formMetadata",    ($form['metadata'] == '1')   ? "checked" : "");
		localVars::add("exportPublic",    ($form['exportPublic'] == '1')   ? "checked" : "");
		localVars::add("exportOAI",       ($form['exportOAI'] == '1')   ? "checked" : "");
		localVars::add("objPublicReleaseShow",        ($form['objPublicReleaseShow'] == '1')       ? "checked" : "");
		localVars::add("objPublicReleaseDefaultTrue", ($form['objPublicReleaseDefaultTrue'] == '1')? "checked" : "");

		if (is_empty($form['fields'])) {
			$form['fields'] = array();
		}

		// Get all fieldsets needed
		foreach ($form['fields'] as $I => $field) {
			if (!is_empty($field['fieldset'])) {
				$fieldsets[$field['fieldset']] = array(
					"type"     => "fieldset",
					"fieldset" => $field['fieldset'],
					);
			}
		}

		$positionOffset = 0;
		foreach($form['fields'] as $I => $field) {
			if (isset($field['choicesOptions']) && is_array($field['choicesOptions'])) {
				$field['choicesOptions'] = implode("%,%",$field['choicesOptions']);
			}
			else if (isset($field['allowedExtensions']) && is_array($field['allowedExtensions'])) {
				$field['allowedExtensions'] = implode("%,%",$field['allowedExtensions']);
			}

			if ($field['type'] == 'text') {
				localVars::add("objectTitleFieldOptions", sprintf('%s<option value="%s"%s>%s</option>',
					localVars::get("objectTitleFieldOptions"),
					$field['name'],
					($field['name'] == $form['objectTitleField']) ? " selected" : NULL,
					$field['label']
					));
			}

			$values = json_encode($field);

			if (!is_empty($field['fieldset']) && isset($fieldsets[$field['fieldset']])) {
				$formPreview .= sprintf('
					<li id="formPreview_%s" data-id="%s">
						<div class="fieldPreview">
							<script type="text/javascript">
								$("#formPreview_%s .fieldPreview").html(newFieldPreview("%s","%s", %s));
							</script>
						</div>
						<div class="fieldValues">
							<script type="text/javascript">
								$("#formPreview_%s .fieldValues").html(newFieldValues("%s","%s",%s));
							</script>
						</div>
					</li>',
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($fieldsets[$field['fieldset']]['type']),
					json_encode($fieldsets[$field['fieldset']]),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($fieldsets[$field['fieldset']]['type']),
					json_encode($fieldsets[$field['fieldset']])
					);

				$positionOffset++;
				unset($fieldsets[$field['fieldset']]);
			}

			$formPreview .= sprintf('
				<li id="formPreview_%s" data-id="%s">
					<div class="fieldPreview">
						<script type="text/javascript">
							$("#formPreview_%s .fieldPreview").html(newFieldPreview("%s","%s", %s));
						</script>
					</div>
					<div class="fieldValues">
						<script type="text/javascript">
							$("#formPreview_%s .fieldValues").html(newFieldValues("%s","%s",%s));
						</script>
					</div>
				</li>',
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['type']),
				$values,
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['type']),
				$values
				);
		}

		localVars::add("formPreview",$formPreview);
	}
}
else if($formCreationError === TRUE){
	$form = $query['form'];
	$form['fields'] = $query['fields'];

	if ($form !== FALSE) {
		$formPreview = NULL;

		localVars::add("formTitle",       htmlSanitize($form['formTitle']));
		localVars::add("displayTitle",    htmlSanitize($form['objectDisplayTitle']));
		localVars::add("linkTitle",       htmlSanitize($form['linkTitle']));
		localVars::add("formDescription", htmlSanitize($form['formDescription']));
		localVars::add("submitButton",    htmlSanitize($form['submitButton']));
		localVars::add("updateButton",    htmlSanitize($form['updateButton']));
		localVars::add("formContainer",   ($form['formContainer'] == '1')  ? "checked" : "");
		localVars::add("formProduction",  ($form['formProduction'] == '1') ? "checked" : "");
		localVars::add("formMetadata",    ($form['formMetadata'] == '1')   ? "checked" : "");
		localVars::add("exportPublic",    ($form['exportPublic'] == '1')   ? "checked" : "");
		localVars::add("exportOAI",       ($form['exportOAI'] == '1')   ? "checked" : "");
		localVars::add("objPublicReleaseShow",        ($form['objPublicReleaseShow'] == '1')       ? "checked" : "");
		localVars::add("objPublicReleaseDefaultTrue", ($form['objPublicReleaseDefaultTrue'] == '1')? "checked" : "");

		if (is_empty($form['fields'])) {
			$form['fields'] = array();
		}

		// Get all fieldsets needed
		foreach ($form['fields'] as $I => $field) {
			if (!is_empty($field['fieldset'])) {
				$fieldsets[$field['fieldset']] = array(
					"type"     => "fieldset",
					"fieldset" => $field['fieldset'],
					);
			}
		}

		$positionOffset = 0;
		foreach($form['fields'] as $I => $field) {
			if (isset($field['choicesOptions']) && is_array($field['choicesOptions'])) {
				$field['choicesOptions'] = implode("%,%",$field['choicesOptions']);
			}
			else if (isset($field['allowedExtensions']) && is_array($field['allowedExtensions'])) {
				$field['allowedExtensions'] = implode("%,%",$field['allowedExtensions']);
			}

			if ($field['type'] == 'text') {
				localVars::add("objectTitleFieldOptions", sprintf('%s<option value="%s"%s>%s</option>',
					localVars::get("objectTitleFieldOptions"),
					$field['name'],
					($field['name'] == $form['objectTitleField']) ? " selected" : NULL,
					$field['label']
					));
			}

			$values = json_encode($field);

			if (!is_empty($field['fieldset']) && isset($fieldsets[$field['fieldset']])) {
				$formPreview .= sprintf('
					<li id="formPreview_%s" data-id="%s">
						<div class="fieldPreview">
							<script type="text/javascript">
								$("#formPreview_%s .fieldPreview").html(newFieldPreview("%s","%s"));
							</script>
						</div>
						<div class="fieldValues">
							<script type="text/javascript">
								$("#formPreview_%s .fieldValues").html(newFieldValues("%s","%s",%s));
							</script>
						</div>
					</li>',
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($fieldsets[$field['fieldset']]['type']),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($field['position'] + $positionOffset),
					htmlSanitize($fieldsets[$field['fieldset']]['type']),
					json_encode($fieldsets[$field['fieldset']])
					);

				$positionOffset++;
				unset($fieldsets[$field['fieldset']]);
			}

			$formPreview .= sprintf('
				<li id="formPreview_%s" data-id="%s">
					<div class="fieldPreview">
						<script type="text/javascript">
							$("#formPreview_%s .fieldPreview").html(newFieldPreview("%s","%s"));
						</script>
					</div>
					<div class="fieldValues">
						<script type="text/javascript">
							$("#formPreview_%s .fieldValues").html(newFieldValues("%s","%s",%s));
						</script>
					</div>
				</li>',
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['type']),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['position'] + $positionOffset),
				htmlSanitize($field['type']),
				$values
			);
		}

		localVars::add("formPreview",$formPreview);
	}
}
else {
	localVars::add("displayModal","<script> $('#formTypeSelector').modal('show'); </script> ");
}

if (is_empty(localVars::get("submitButton"))) {
	localVars::add("submitButton","Submit");
}
if (is_empty(localVars::get("updateButton"))) {
	localVars::add("updateButton","Update");
}

if (!isnull($formID)) {
	try {
	// Setup permissions stuff.
		if (($users = users::getUsers()) === FALSE) {
			throw new Exception("Error getting users");
		}

		if (($availableUsersList = listGenerator::availableUsersList($users)) === FALSE) {
			throw new Exception("Error getting users list.");
		}

		localvars::add("availableUsersList",$availableUsersList);

	// Setup Navigation stuff.
		$metadataForms         = array();
		$metadataFormsEven     = NULL;
		$metadataFormsOdd      = NULL;
		$selectedMetadataForms = "";

		if (($metadataForms = forms::getObjectFormMetaForms($formID)) === FALSE) {
			throw new Exception("Error getting linked metadata forms.");
		}

	// Now loop through all the metadata forms building their HTML and putting it in the right place
		foreach ($metadataForms as $i => $form) {
			$targetVar   = ($i % 2) ? 'metadataFormsOdd' : 'metadataFormsEven';
			$$targetVar .= sprintf('<li data-type="metadataForm" data-formid="%s"><a href="#">%s</a></li>',
				htmlSanitize($form['formID']),
				htmlSanitize($form['title'])
				);
		}

		localvars::add("selectedMetadataForms",$selectedMetadataForms);
		if (!empty($metadataFormsEven) || !empty($metadataFormsOdd)) {
			localvars::add("metadataForms", sprintf('
				<h3 class="mdTitle"> Metadata Forms</h3>
				<div class="row-fluid">
					<ul class="unstyled draggable metadataLinks">%s %s</ul>
				</div>',
				$metadataFormsEven,
				$metadataFormsOdd
				));
		}

		// Get existing groupings
		$sql = sprintf("SELECT * FROM `forms` WHERE `ID`='%s' LIMIT 1",
			$engine->openDB->escape($formID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError("MySQL Error - Error getting project ({$sqlResult['error']})",errorHandle::DEBUG);
			throw new Exception("Error getting navigation");
		}

		$row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC);

		if (!is_empty($row['navigation'])) {
			$tmp       = decodeFields($row['navigation']);
			$groupings = array();
			$preview   = NULL;

			// Get all groupings needed
			foreach ($tmp as $I => $V) {
				if (!is_empty($V['grouping'])) {
					$groupings[$V['grouping']] = array(
						"type"     => "grouping",
						"grouping" => $V['grouping'],
					);
				}
			}

			$positionOffset = 0;
			foreach ($tmp as $I => $V) {
				$values = json_encode($V);

				if (!is_empty($V['grouping']) && isset($groupings[$V['grouping']])) {
					$preview .= sprintf('
						<li id="GroupingsPreview_%s" class="%s">
							<div class="groupingPreview">
								<script type="text/javascript">
									$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s",%s));
								</script>
							</div>
							<div class="groupingValues">
								<script type="text/javascript">
									$("#GroupingsPreview_%s .groupingValues").html(newGroupingValues("%s","%s",%s));
								</script>
							</div>
						</li>',
						htmlSanitize($V['position'] + $positionOffset),
						htmlSanitize($groupings[$V['grouping']]['type']),
						htmlSanitize($V['position'] + $positionOffset),
						htmlSanitize($groupings[$V['grouping']]['type']),
						json_encode($groupings[$V['grouping']]),
						htmlSanitize($V['position'] + $positionOffset),
						htmlSanitize($V['position'] + $positionOffset),
						htmlSanitize($groupings[$V['grouping']]['type']),
						json_encode($groupings[$V['grouping']])
					);

					$positionOffset++;
					unset($groupings[$V['grouping']]);
				}

				$preview .= sprintf('
					<li id="GroupingsPreview_%s" class="%s">
						<div class="groupingPreview">
							<script type="text/javascript">
								$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s",%s));
							</script>
						</div>
						<div class="groupingValues">
							<script type="text/javascript">
								$("#GroupingsPreview_%s .groupingValues").html(newGroupingValues("%s","%s",%s));
							</script>
						</div>
					</li>',
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['type']),
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['type']),
					$values,
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['type']),
					$values
				);
			}
			localvars::add("existingGroupings",$preview);
		}
	}
	catch (Exception $e) {
		errorHandle::errorMsg($e->getMessage());
	}
}

// Build the list of users for the form permissions select boxes
$selectedEntryUsers   = "";
$selectedViewUsers    = "";
$selectedUsersAdmins  = "";
$selectedUsersContact = "";

if (isset($engine->cleanGet['MYSQL']['id']) && !isempty($engine->cleanGet['MYSQL']['id'])) {

	$sql = sprintf("SELECT permissions.type, users.status, users.firstname, users.lastname, users.username, users.ID as userID FROM permissions LEFT JOIN users ON permissions.userID=users.ID WHERE permissions.formID='%s' ORDER BY `users`.`lastname`",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);
	if(!$sqlResult['result']) throw new Exception("MySQL Error - getting permissions ({$sqlResult['error']})");

	while($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
		$optionHTML = sprintf('<option value="%s">%s, %s (%s)</option>',
			htmlSanitize($row['userID']),
			htmlSanitize($row['lastname']),
			htmlSanitize($row['firstname']),
			htmlSanitize($row['username']));
		switch($row['type']){
			case mfcs::AUTH_VIEW:
				$selectedViewUsers    .= $optionHTML;
				break;
			case mfcs::AUTH_ENTRY:
				$selectedEntryUsers   .= $optionHTML;
				break;
			case mfcs::AUTH_ADMIN:
				$selectedUsersAdmins  .= $optionHTML;
				break;
			case mfcs::AUTH_CONTACT:
				$selectedUsersContact .= $optionHTML;
				break;
		}
	}
}

// Audio Video Specific Information
// =============================================================
$audioFileTypes = array();

// more options can be added later, its not a great idea to go above or below 128 though
$bitrates    = array(
	'32' => '32kbs - Low (speeches)',
	'64' => '64kbs - Low (fast-streaming)',
	'128' => '128kbs - Recommended (most usable range)',
	'192' => '192kbs - High Quality',
	'256' => '256kbs - Best Quality',
);

$videoBitrates    = array(
	'700' => '700kbs - Low Quality',
	'1200' => '1200kbs',
	'2400' => '2400kbs - Youtube Quality',
	'5000' => '5000kbs - Streaming HD Quality',
	'12000' => '12000kbs - HDTV Quality',
);

$audioTypes  = array(
	'mp3' => 'MP3',
	'ogg' => 'OGG',
	'wav' => 'WAV'
);

$videoTypes  = array(
	'avi'  => 'AVI',
	'mov'  => 'MOV',
	'mp4'  => 'MP4',
	'3gp'  => '3GP',
	'webm' => 'WEBM',
	'ogv'  => 'OGV'
);

$videoThumbs = array(
	'gif' => 'Gif',
	'jpg' => 'Jpg',
	'png' => 'Png'
);

try {
	$metadataChoices = listGenerator::getMetadataStandards();
	if(!$metadataChoices){
		throw new Exception("Error -- MetadataStandards have not been found.");
	}
	localvars::add('metadataSchema', renderToOptions($metadataChoices));
} catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}


// Render Stuff
localvars::add('bitRates',      renderToOptions($bitrates));
localvars::add('videoBitrates', renderToOptions($videoBitrates));
localvars::add('audioOptions',  renderToOptions($audioTypes));
localvars::add('videoTypes',    renderToOptions($videoTypes));
localvars::add('videoThumbs',   renderToOptions($videoThumbs));


localvars::add("selectedEntryUsers",  $selectedEntryUsers);
localvars::add("selectedViewUsers",   $selectedViewUsers);
localvars::add("selectedUsersAdmins", $selectedUsersAdmins);
localvars::add("selectedUsersContact",$selectedUsersContact);
localVars::add("results",displayMessages());

$selectedProjects = forms::getProjects(isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : 0);
localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));

$engine->eTemplate("include","header");

?>

<section>
			<header class="page-header">
				<h1>Form Creator</h1>
			</header>

			<div class="tab-content">

			<ul class="nav nav-tabs">
				<li class="active"><a href="#formCreator" data-toggle="tab">Form Creator</a></li>
				<?php if (!isnull($formID)) { ?>
				<?php if (!forms::isMetadataForm($formID)) { ?>
				<li><a href="#projects" data-toggle="tab">Assigned Projects</a></li>
				<?php } ?>
				<li><a href="#navigation" class="navigationCreator" data-toggle="tab">Navigation Creator</a></li>
				<li><a href="#permissions" data-toggle="tab">Form Permissions</a></li>
				<li><a href="#deleteForm" data-toggle="tab" style="color: red;">Delete Form</a></li>
				<?php } ?>
			</ul>

			<div class="tab-pane active" id="formCreator">
				<?php recurseInsert("templates/formPreview.php","php"); ?>
			</div>

			<?php if (!isnull($formID)) { ?>
			<?php if (!forms::isMetadataForm($formID)) { ?>
				<div class="tab-pane" id="projects">
					<?php recurseInsert("templates/changeProjectMembership.php","php"); ?>
				</div>
				<?php } ?>

				<div class="tab-pane" id="navigation">
					<?php recurseInsert("templates/navCreator.php","php"); ?>
				</div>

				<div class="tab-pane" id="permissions">
					<?php recurseInsert("templates/formPermissions.php","php"); ?>
				</div>

				<div class="tab-pane" id="deleteForm">
					<?php recurseInsert("templates/deleteForm.php","php"); ?>
				</div>
			<?php } ?>
	</div>
</section>


<!-- Modals ===================================================================  -->

 <div id="defaultValueVariables" class="modal" rel="modal" data-show="false">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Available variables for default value</h3>
    </div>
    <div class="modal-body">
    	<div class="content">
	        <b>User</b>
	        <ul style="list-style: none;">
	            <li><b>%userid%</b><br>The user id for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%userid%') ?></i>)</li>
	            <li><b>%username%</b><br>The username for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%username%') ?></i>)</li>
	            <li><b>%firstname%</b><br>The first name for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%firstname%') ?></i>)</li>
	            <li><b>%lastname%</b><br>The last name for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%lastname%') ?></i>)</li>
	        </ul>
	        <hr>
	        <b>Static Date/Time</b>
	        <ul style="list-style: none;">
	            <li><b>%date%</b><br>The current date as MM/DD/YYYY. (<i>Example: <?php echo forms::applyFieldVariables('%date%') ?></i>)</li>
	            <li><b>%time%</b><br>The current time as HH:MM:SS. (<i>Example: <?php echo forms::applyFieldVariables('%time%') ?></i>)</li>
	            <li><b>%time12%</b><br>The current 12-hr time. (<i>Example: <?php echo forms::applyFieldVariables('%time12%') ?></i>)</li>
	            <li><b>%time24%</b><br>The current 24-hr time. (<i>Example: <?php echo forms::applyFieldVariables('%time24%') ?></i>)</li>
	            <li><b>%timestamp%</b><br>The current UNIX system timestamp. (<i>Example: <?php echo forms::applyFieldVariables('%timestamp%') ?></i>)</li>
	        </ul>
	        <hr>
	        <b>Custom Date/Time</b>
	        <ul style="list-style: none;">
	            <li>
	                <b>%date(FORMAT)%</b><br>
	                You can specify a custom format when creating dates and times where FORMAT is a PHP <a href="http://us2.php.net/manual/en/function.date.php" target="_blank">date()</a> format string.
	                <br>
	                <b><i>Example:</i></b> %date(l, m j Y)% becomes <?php echo forms::applyFieldVariables('%date(l, F j Y)%') ?>
	            </li>
	        </ul>
	    </div>
    </div>
</div>

<div class="modal fade formType" id="formTypeSelector">
	<div class="modal-header">
		<h3>What type of form will this be?</h3>
	</div>
	<div class="modal-body text-center">
		<button class="btn btn-large">Metadata</button>
		<button class="btn btn-large">Object</button>
	</div>
</div>
{local var="displayModal"}


<div class="formAlert noSpacesAlert alert alert-danger alert-dismissible" role="alert" style="display:none;">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<strong> Space Warning! </strong>
	<p> There are no spaces allowed your field name, please replace the spaces with an underscore, or remove them. </p>
</div>

<!-- Needed Inline to get the variable to JS
     Local vars will not work within js for dynamic creation -->
<script type="text/javascript">
	var metadataSchema = '{local var="metadataSchema"}';
</script>

<?php
 $engine->eTemplate("include","footer");
?>
