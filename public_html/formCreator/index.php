<?php

// @TODO there is way too much logic in this file. It needs to be refactored out.
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

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
			$countSql,                                            // count=
			$engine->openDB->escape($form['objectDisplayTitle']), // displayTitle=
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
		$sql = sprintf("INSERT INTO `forms` (title, description, fields, idno, submitButton, updateButton, container, production, metadata, count, displayTitle, objectTitleField, linkTitle) VALUES ('%s',%s,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%s)",
			$engine->openDB->escape($form['formTitle']),
			isset($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL",
			encodeFields($fields),
			encodeFields($idno),
			$engine->openDB->escape($form['submitButton']),
			$engine->openDB->escape($form['updateButton']),
			$engine->openDB->escape($form['formContainer']),
			$engine->openDB->escape($form['formProduction']),
			$engine->openDB->escape($form['formMetadata']),
			$engine->openDB->escape($count),
			$engine->openDB->escape($form['objectDisplayTitle']),
			$engine->openDB->escape($form['objectTitleField']),
			!is_empty($form['linkTitle']) ? "'".$engine->openDB->escape($form['linkTitle'])."'" : "NULL" // linkTitle=
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - Inserting new form: ".$sqlResult['error']." == ".$sql, errorHandle::DEBUG);
			errorHandle::errorMsg("Failed to create form.");
		}
		else {
			$formID = $sqlResult['id'];
		}
	}

	if (!is_empty($engine->errorStack)) {
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
	}
	else {
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
		errorHandle::successMsg("Successfully submitted form.");
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

		$tmp = array("selectedViewUsers"   => mfcs::AUTH_VIEW,
			         "selectedEntryUsers"  => mfcs::AUTH_ENTRY,
			         "selectedUsersAdmins" => mfcs::AUTH_ADMIN
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
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
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

if (!isnull($formID)) {
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

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

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
									$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s"));
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
								$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s"));
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
$selectedEntryUsers  = "";
$selectedViewUsers   = "";
$selectedUsersAdmins = "";

if (isset($engine->cleanGet['MYSQL']['id']) && !isempty($engine->cleanGet['MYSQL']['id'])) {

	$sql = sprintf("SELECT permissions.type, users.status, users.firstname, users.lastname, users.username, users.ID as userID FROM permissions LEFT JOIN users ON permissions.userID=users.ID WHERE permissions.formID='%s' ORDER BY `users`.`lastname`",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);
	if(!$sqlResult['result']) throw new Exception("MySQL Error - getting permissions ({$sqlResult['error']})");

	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		$optionHTML = sprintf('<option value="%s">%s, %s (%s)</option>',
			htmlSanitize($row['userID']),
			htmlSanitize($row['lastname']),
			htmlSanitize($row['firstname']),
			htmlSanitize($row['username']));
		switch($row['type']){
			case mfcs::AUTH_VIEW:
				$selectedViewUsers .= $optionHTML;
				break;
			case mfcs::AUTH_ENTRY:
				$selectedEntryUsers .= $optionHTML;
				break;
			case mfcs::AUTH_ADMIN:
				$selectedUsersAdmins .= $optionHTML;
				break;
		}
	}
}

// Audio Video Specific Information
// =============================================================
$audioFileTypes = array();

// more options can be added later, its not a great idea to go above or below 128 though
$bitrates    = array(
	'128'        => '128kbs - Low',
	'160'		 => '160kbs - Average ',
	'192'		 => '192kbs - Above Average',
	'256'        => '256kbs - Recommended',
	'320'		 => '320kbs - High',
);

$audioTypes  = array(
	'aac'        => 'Advanced Audio Coding',
	'mp3'        => 'MP3 - Audio Layer 3',
	'oga'        => 'Open Container Audio',
	'wav'        => 'Wav Sound File'
);

$videoTypes  = array(
	'3gp'        => 'Mobile Video Format',
	'h264'       => 'H264 Raw Format',
	'mp4'        => 'MP4 Video Format',
	'oog'        => 'Open Container Format',
	'wmv'        => 'Windows Media Video',
);

$videoThumbs = array(
	'bmp'        => 'Bitmap',
	'gif'        => 'Gif',
	'jpeg'       => 'Jpeg',
	'png'        => 'Png'
);

$metadataChoices = array(
	'dublin'  => 'dublin core',
	'test'    => 'test option',
	'fake'    => 'fake option'
);


// Render Stuff
localvars::add('bitRates', renderToOptions($bitrates));
localvars::add('audioOptions', renderToOptions($audioTypes));
localvars::add('videoTypes', renderToOptions($videoTypes));
localvars::add('videoThumbs', renderToOptions($videoThumbs));
localvars::add('metadataSchema', renderToOptions($metadataChoices));

localvars::add("selectedEntryUsers",$selectedEntryUsers);
localvars::add("selectedViewUsers",$selectedViewUsers);
localvars::add("selectedUsersAdmins",$selectedUsersAdmins);
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
				<li><a href="#navigation" data-toggle="tab">Navigation Creator</a></li>
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