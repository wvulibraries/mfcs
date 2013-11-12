<?php
include("../header.php");

$formID = isset($engine->cleanPost['HTML']['id']) ? $engine->cleanPost['HTML']['id'] : (isset($engine->cleanGet['HTML']['id']) ? $engine->cleanGet['HTML']['id'] : NULL);
if (is_empty($formID)) {
	$formID = NULL;
}

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
		foreach ($fields as $I => $field) {
			$positions[$I] = $field['position'];

			if (is_empty($field['id'])) {
				$fields[$I]['id'] = $field['name'];
			}

			$count = NULL;
			if ($field['type'] == 'idno') {
				$idno = $field;
				$count = $field['startIncrement'];
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
							`count`='%s',
							`displayTitle`='%s',
							`objectTitleField`='%s'
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
			$engine->openDB->escape($count),                      // count=
			$engine->openDB->escape($form['objectDisplayTitle']), // displayTitle=
			$engine->openDB->escape($form['objectTitleField']),   // objectTitleField=
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
		$sql = sprintf("INSERT INTO `forms` (title, description, fields, idno, submitButton, updateButton, container, production, metadata, count, displayTitle, objectTitleField) VALUES ('%s',%s,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
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
			$engine->openDB->escape($form['objectTitleField'])
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
	foreach (Imagick::queryFormats() as $format) {
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
		localVars::add("formDescription", htmlSanitize($form['description']));
		localVars::add("submitButton",    htmlSanitize($form['submitButton']));
		localVars::add("updateButton",    htmlSanitize($form['updateButton']));
		localVars::add("displayTitle",    htmlSanitize($form['displayTitle']));
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
					<li id="formPreview_%s">
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
				<li id="formPreview_%s">
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
	localVars::add("displayModal",'
		<script type="text/javascript">
			$(function() {
				$("#formTypeSelector").modal("show");
			});
		</script>');
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
			$$targetVar .= sprintf('<li data-type="metadataForm" data-formid="%s"><a href="#" class="btn btn-block">%s</a></li>',
				htmlSanitize($form['formID']),
				htmlSanitize($form['title'])
				);
		}

		localvars::add("selectedMetadataForms",$selectedMetadataForms);
		if (!empty($metadataFormsEven) || !empty($metadataFormsOdd)) {
			localvars::add("metadataForms", sprintf('
				<h3>Metadata Forms</h3>
				<div class="row-fluid">
					<ul class="unstyled draggable span6">%s</ul>
					<ul class="unstyled draggable span6">%s</ul>
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
						<li id="GroupingsPreview_%s">
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
					<li id="GroupingsPreview_%s">
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

	$sql = sprintf("SELECT permissions.type, users.status, users.firstname, users.lastname, users.username, users.ID as userID FROM permissions LEFT JOIN users ON permissions.userID=users.ID WHERE permissions.formID='%s'",
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

localvars::add("selectedEntryUsers",$selectedEntryUsers);
localvars::add("selectedViewUsers",$selectedViewUsers);
localvars::add("selectedUsersAdmins",$selectedUsersAdmins);

localVars::add("results",displayMessages());

$selectedProjects = forms::getProjects(isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : 0);
localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));

$engine->eTemplate("include","header");
?>

<script type="text/javascript" src='{local var="siteRoot"}includes/js/createForm_functions.js'></script>

<section>
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

	<div class="tab-content">
		<div class="tab-pane active" id="formCreator">
			<header class="page-header">
				<h1>Form Creator</h1>
			</header>

			<div class="container-fluid">
				<div class="row-fluid" id="results">
					{local var="results"}
				</div>

				<div class="row-fluid">
					<div class="span5">
						<div id="leftPanel">
							<ul class="nav nav-tabs" id="fieldTab">
								<li><a href="#fieldAdd" data-toggle="tab">Add a Field</a></li>
								<li><a href="#fieldSettings" data-toggle="tab">Field Settings</a></li>
								<li><a href="#formSettings" data-toggle="tab">Form Settings</a></li>
							</ul>

							<div class="tab-content">
								<div class="tab-pane" id="fieldAdd">
									<div class="row-fluid">
										<div class="span6">
											<ul class="unstyled draggable">
												<li><a href="#" class="btn btn-block">ID Number</a></li>
												<li><a href="#" class="btn btn-block">Single Line Text</a></li>
												<li><a href="#" class="btn btn-block">Paragraph Text</a></li>
												<li><a href="#" class="btn btn-block">Radio</a></li>
												<li><a href="#" class="btn btn-block">Checkboxes</a></li>
												<li><a href="#" class="btn btn-block">Number</a></li>
												<li><a href="#" class="btn btn-block">Email</a></li>
												<li><a href="#" class="btn btn-block">Phone</a></li>
											</ul>
										</div>
										<div class="span6">
											<ul class="unstyled draggable">
												<li><a href="#" class="btn btn-block">Dropdown</a></li>
												<li><a href="#" class="btn btn-block">Multi-Select</a></li>
												<li><a href="#" class="btn btn-block">File Upload</a></li>
												<li><a href="#" class="btn btn-block">WYSIWYG</a></li>
												<li><a href="#" class="btn btn-block">Date</a></li>
												<li><a href="#" class="btn btn-block">Time</a></li>
												<li><a href="#" class="btn btn-block">Website</a></li>
											</ul>
										</div>
									</div>
									<hr>
									<ul class="unstyled draggable">
										<li><a href="#" class="btn btn-block">Field Set</a></li>
									</ul>
								</div>

								<div class="tab-pane" id="fieldSettings">
									<div class="alert alert-block" id="noFieldSelected">
										<h4>No Field Selected</h4>
										To change a field, click on it in the form preview to the right.
									</div>

									<form class="form form-horizontal" id="fieldSettings_fieldset_form">
										<div class="control-group well well-small" id="fieldSettings_container_fieldset">
											<label for="fieldSettings_fieldset">
												Fieldset Label
												<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If a label is entered here, the field will be surrounded by a FieldSet, and the label used."></i>
											</label>
											<input type="text" class="input-block-level" id="fieldSettings_fieldset" name="fieldSettings_fieldset" />
											<span class="help-block hidden"></span>
										</div>
									</form>

									<form class="form form-horizontal" id="fieldSettings_form">
										<div class="row-fluid noHide">
											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_name">
													<label for="fieldSettings_name">
														Field Name
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The field name is a unique value that is used to identify a field."></i>
													</label>
													<input type="text" class="input-block-level" id="fieldSettings_name" name="fieldSettings_name" />
													<span class="help-block hidden"></span>
												</div>
											</span>

											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_label">
													<label for="fieldSettings_label">
														Field Label
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The field label tells your users what to enter in this field."></i>
													</label>
													<input type="text" class="input-block-level" id="fieldSettings_label" name="fieldSettings_label" />
													<span class="help-block hidden"></span>
												</div>
											</span>
										</div>

										<div class="row-fluid noHide">
											<span class="span6" id="fieldSettings_container_value">
												<div class="control-group well well-small">
													<label for="fieldSettings_value">
														Value
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
													</label>
													<a href="javascript:;" class="pull-right" onclick="$('#defaultValueVariables').modal('show')" id="fieldVariablesLink" style="display: none;">Variables</a>
													<input type="text" class="input-block-level" id="fieldSettings_value" name="fieldSettings_value" />
													<span class="help-block hidden"></span>
													<div id="defaultValueVariables" class="modal hide fade" rel="modal" data-show="false">
														<div class="modal-header">
															<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
															<h3 id="myModalLabel">Available variables for default value</h3>
														</div>
														<div class="modal-body">
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
											</span>

											<span class="span6" id="fieldSettings_container_placeholder">
												<div class="control-group well well-small">
													<label for="fieldSettings_placeholder">
														Placeholder Text
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If there is no value in the field, this can tell your users what to input."></i>
													</label>
													<input type="text" class="input-block-level" id="fieldSettings_placeholder" name="fieldSettings_placeholder" />
													<span class="help-block hidden"></span>
												</div>
											</span>
										</div>

										<div class="row-fluid noHide">
											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_id">
													<label for="fieldSettings_id">
														CSS ID
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
													</label>
													<input type="text" class="input-block-level" id="fieldSettings_id" name="fieldSettings_id" />
													<span class="help-block hidden"></span>
												</div>
											</span>

											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_class">
													<label for="fieldSettings_class">
														CSS Classes
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
													</label>
													<input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" />
													<span class="help-block hidden"></span>
												</div>
											</span>
										</div>

										<div class="row-fluid noHide">
											<div class="control-group well well-small" id="fieldSettings_container_style">
												<label for="fieldSettings_style">
													Local Styles
													<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="You can set any HTML styles and they will only apply to this field."></i>
												</label>
												<input type="text" class="input-block-level" id="fieldSettings_style" name="fieldSettings_style" />
												<span class="help-block hidden"></span>
											</div>
										</div>

										<div class="row-fluid noHide">
											<div class="control-group well well-small" id="fieldSettings_container_style">
												<label for="fieldSettings_style">
													Field Help
													<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="You can set any help text you want displayed with this field."></i>
												</label>
												<select class="input-block-level" id="fieldSettings_help_type" name="fieldSettings_help_type">
													<option value="">None</option>
													<option value="text">Plain text</option>
													<option value="html">HTML text</option>
													<option value="web">Webpage (URL)</option>
												</select>
												<input type="text" class="input-block-level" id="fieldSettings_help_text" name="fieldSettings_help_text" style="display: none;">
												<textarea class="input-block-level" id="fieldSettings_help_html" name="fieldSettings_help_html" style="display: none;"></textarea>
												<input type="text" class="input-block-level" id="fieldSettings_help_url" name="fieldSettings_help_url" style="display: none;" placeholder="http://example.com">
												<span class="help-block hidden"></span>
											</div>
											<div id="fieldHelpModal" class="modal hide fade">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
													<h3 id="myModalLabel">Field Help</h3>
												</div>
												<div class="modal-body">
													<iframe id="fieldHelpModalURL" seamless="seamless" style="width: 100%; height: 100%;"></iframe>
												</div>
											</div>
										</div>
										<div class="control-group well well-small" id="fieldSettings_container_choices">
											<label for="fieldSettings_choices">
												Choices
												<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title=""></i>
											</label>
											<select class="input-block-level" id="fieldSettings_choices_type" name="fieldSettings_choices_type">
												<option value="manual">Manual</option>
												<option value="form">Another Form</option>
											</select>
												<label style="width: 100%;"><input type="checkbox" id="fieldSettings_choices_null" name="fieldSettings_choices_null"> Include 'Make a selection' placeholder</label></span>
												<div id="fieldSettings_choices_manual"></div>
												<div id="fieldSettings_choices_form">
													<label for="fieldSettings_choices_formSelect">
														Select a Form
													</label>
													<select class="input-block-level" id="fieldSettings_choices_formSelect" name="fieldSettings_choices_formSelect">
														{local var="formsOptions"}
													</select>

													<label for="fieldSettings_choices_fieldSelect">
														Select a Field
													</label>
													<select class="input-block-level" id="fieldSettings_choices_fieldSelect" name="fieldSettings_choices_fieldSelect">
													</select>
												</div>
											</p>
											<span class="help-block hidden"></span>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_range">
											<div class="row-fluid">
												<span class="span3">
													<label for="fieldSettings_range_min">
														Min
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_range_min" name="fieldSettings_range_min" min="0" />
												</span>
												<span class="span3">
													<label for="fieldSettings_range_max">
														Max
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_range_max" name="fieldSettings_range_max" min="0" />
												</span>
												<span class="span2">
													<label for="fieldSettings_range_step">
														Step
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_range_step" name="fieldSettings_range_step" min="0" />
												</span>
												<span class="span4">
													<label for="fieldSettings_range_format">
														Format
													</label>
													<select class="input-block-level" id="fieldSettings_range_format" name="fieldSettings_range_format"></select>
												</span>
											</div>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_idno">
											<label for="fieldSettings_idno_managedBy">
												ID Number Options
											</label>
											<select class="input-block-level" id="fieldSettings_idno_managedBy" name="fieldSettings_idno_managedBy">
												<option value="system">Managed by System</option>
												<option value="user">Managed by User</option>
											</select>

											<p>
												<div class="row-fluid">
													<div class="span6" id="fieldSettings_container_idno_format">
														<label for="fieldSettings_idno_format">
															Format
														</label>
														<input type="text" class="input-block-level" id="fieldSettings_idno_format" name="fieldSettings_idno_format" placeholder="st_###" />
													</div>

													<div class="span6" id="fieldSettings_container_idno_startIncrement">
														<label for="fieldSettings_idno_startIncrement">
															Auto Increment Start
														</label>
														<input type="number" class="input-block-level" id="fieldSettings_idno_startIncrement" name="fieldSettings_idno_startIncrement" min="1" />
													</div>
												</div>
											</p>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_file_allowedExtensions">
											<div id="allowedExtensionsAlert" style="display:none;" class="alert alert-error">No allowed extensions included! Currently, no files will be uploadable!</div>
											<label for="fieldSettings_file_allowedExtensions">
												Allowed Extensions
											</label>
											<div id="fieldSettings_file_allowedExtensions"></div>
											<span class="help-block hidden"></span>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_file_options">
											File Upload Options
											<div>
												<div style="float: left; margin-top: 5px;">
													Images
													<ul class="checkboxList">
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_multipleFiles" name="fieldSettings_file_options_multipleFiles"> Allow multiple files in single upload</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_combine" name="fieldSettings_file_options_combine"> Combine into single PDF</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_ocr" name="fieldSettings_file_options_ocr"> Optical character recognition (OCR)</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_convert" name="fieldSettings_file_options_convert"> Convert uploaded file</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_thumbnail" name="fieldSettings_file_options_thumbnail"> Create thumbnail</label></li>
													</ul>
												</div>
												<div style="float: left; margin-top: 5px;">
													Audio
													<ul class="checkboxList">
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_mp3" name="fieldSettings_file_options_mp3"> Create MP3</label></li>
													</ul>
												</div>
											</div>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_file_convert">
											<label for="fieldSettings_file_convert">
												Conversions
											</label>

											<div class="row-fluid">
												<div class="span3" id="fieldSettings_container_file_convert_height">
													<label for="fieldSettings_file_convert_height">
														Max Height (px)
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_file_convert_height" name="fieldSettings_file_convert_height" min="1" />
												</div>

												<div class="span3" id="fieldSettings_container_file_convert_width">
													<label for="fieldSettings_file_convert_width">
														Max Width (px)
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_file_convert_width" name="fieldSettings_file_convert_width" min="1" />
												</div>

												<div class="span3" id="fieldSettings_container_file_convert_reolution">
													<label for="fieldSettings_file_convert_resolution">
														Resolution (DPI)
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_file_convert_resolution" name="fieldSettings_file_convert_resolution" min="1" />
												</div>

												<div class="span3" id="fieldSettings_container_file_convert_extension">
													<label for="fieldSettings_file_convert_format">
														Format
													</label>
												<select class="input-block-level" id="fieldSettings_file_convert_format" name="fieldSettings_file_convert_format">
														<option value="">Select Format</option>
														{local var="conversionFormats"}
													</select>
												</div>
											</div>

											<ul class="checkboxList">
												<li>
													<label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_watermark" name="fieldSettings_file_convert_watermark">Watermark</label>
													<div class="row-fluid">
														<div class="span6">
															<label for="fieldSettings_file_watermark_image">
																Image
															</label>
															<select class="input-block-level" id="fieldSettings_file_watermark_image" name="fieldSettings_file_watermark_image">
																<option value="">Select Image</option>
																{local var="watermarkList"}
															</select>
														</div>
														<div class="span6">
															<label for="fieldSettings_file_watermark_location">
																Location
															</label>
															<select class="input-block-level" id="fieldSettings_file_watermark_location" name="fieldSettings_file_watermark_location">
																<option value="">Select Location</option>
																{local var="imageLocations"}
															</select>
														</div>
													</div>
												</li>
												<li>
													<label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_border" name="fieldSettings_file_convert_border"> Border</label>
													<div class="row-fluid">
														<div class="span4">
															<label for="fieldSettings_file_border_height">
																Height (px)
																<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Border width of the top and bottom."></i>
															</label>
															<input type="number" class="input-block-level" id="fieldSettings_file_border_height" name="fieldSettings_file_border_height" min="0" />
														</div>

														<div class="span4">
															<label for="fieldSettings_file_border_width">
																Width (px)
																<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Border width of the left and right."></i>
															</label>
															<input type="number" class="input-block-level" id="fieldSettings_file_border_width" name="fieldSettings_file_border_width" min="0" />
														</div>

														<div class="span4">
															<label for="fieldSettings_file_border_color">
																Color
															</label>
															<input type="color" class="input-block-level" id="fieldSettings_file_border_color" name="fieldSettings_file_border_color" />
														</div>
													</div>
												</li>
											</ul>
										</div>

										<div class="control-group well well-small" id="fieldSettings_container_file_thumbnail">
											<label for="fieldSettings_file_thumbnail">
												Thumbnail Options
											</label>
											<div class="row-fluid" id="fieldSettings_file_thumbnail">
												<span class="span4">
													<label for="fieldSettings_file_thumbnail_height">
														Height (px)
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_height" name="fieldSettings_file_thumbnail_height" min="0" />
												</span>
												<span class="span4">
													<label for="fieldSettings_file_thumbnail_width">
														Width (px)
														<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
													</label>
													<input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_width" name="fieldSettings_file_thumbnail_width" min="0" />
												</span>
												<span class="span4">
													<label for="fieldSettings_file_thumbnail_format">
														Format
													</label>
													<select class="input-block-level" id="fieldSettings_file_thumbnail_format" name="fieldSettings_file_thumbnail_format">
														<option value="">Select Format</option>
														{local var="conversionFormats"}
													</select>
												</span>
											</div>
										</div>


										<div class="row-fluid noHide">
											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_options">
													Options
													<ul class="checkboxList">
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required"> Required</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates"> No duplicates</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly"> Read only</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_disabled" name="fieldSettings_options_disabled"> Disabled</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_publicRelease" name="fieldSettings_options_publicRelease"> Public release</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_sortable" name="fieldSettings_options_sortable"> Sortable</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_searchable" name="fieldSettings_options_searchable"> Searchable</label></li>
														<li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_displayTable" name="fieldSettings_options_displayTable"> Display in list table</label></li>
													</ul>
												</div>
											</span>
											<span class="span6">
												<div class="control-group well well-small" id="fieldSettings_container_validation">
													<label for="fieldSettings_validation">
														Validation
													</label>
													<select class="input-block-level" id="fieldSettings_validation" name="fieldSettings_validation">
														{local var="validationTypes"}
													</select>
													<input type="text" class="input-block-level" id="fieldSettings_validationRegex" name="fieldSettings_validationRegex" placeholder="Enter a Regex" />
												</div>

												<!-- <div class="control-group well well-small" id="fieldSettings_container_access">
													<label for="fieldSettings_access">
														Allow Access
													</label>
													<select class="input-block-level" id="fieldSettings_access" name="fieldSettings_access" multiple>
													</select>
												</div> -->
											</span>
										</div>
									</form>
								</div>

								<div class="tab-pane" id="formSettings">
									<div class="row-fluid noHide">
										<div class="control-group well well-small" id="formSettings_formTitle_container">
											<label for="formSettings_formTitle">
												Form Title
												<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The form name is a unique value that is used to identify a form."></i>
											</label>
											<input type="text" class="input-block-level" id="formSettings_formTitle" name="formSettings_formTitle" value="{local var="formTitle"}" />
											<span class="help-block hidden"></span>
										</div>

										<div class="control-group well well-small" id="formSettings_formDescription_container">
											<label for="formSettings_formDescription">
												Form Description
												<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The form description explains the purpose of this form to users."></i>
											</label>
											<input type="text" class="input-block-level" id="formSettings_formDescription" name="formSettings_formDescription" value="{local var="formDescription"}" />
											<span class="help-block hidden"></span>
										</div>
									</div>

									<div class="row-fluid noHide">
										<div class="span6">
											<div class="control-group well well-small" id="formSettings_submitButton_container">
												<label for="formSettings_submitButton">
													Submit Button
													<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's submit button."></i>
												</label>
												<input type="text" class="input-block-level" id="formSettings_submitButton" name="formSettings_submitButton" value="{local var="submitButton"}" />
												<span class="help-block hidden"></span>
											</div>
										</div>

										<div class="span6">
											<div class="control-group well well-small" id="formSettings_updateButton_container">
												<label for="formSettings_updateButton">
													Update Button
													<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's update button."></i>
												</label>
												<input type="text" class="input-block-level" id="formSettings_updateButton" name="formSettings_updateButton" value="{local var="updateButton"}" />
												<span class="help-block hidden"></span>
											</div>
										</div>
									</div>

									<div class="control-group well well-small" id="formSettings_objectDisplayTitle_container">
										<label for="formSettings_objectDisplayTitle">
											Display Title
										</label>
										<input type="text" class="input-block-level" id="formSettings_objectDisplayTitle" name="formSettings_objectDisplayTitle" value="{local var="displayTitle"}">
										<span class="help-block hidden"></span>
									</div>

									<div class="control-group well well-small" id="formSettings_objectTitleField_container">
										<label for="formSettings_objectTitleField">
											Title Field
										</label>
										<select class="input-block-level" id="formSettings_objectTitleField" name="formSettings_objectTitleField">
											{local var="objectTitleFieldOptions"}
										</select>
										<span class="help-block hidden"></span>
									</div>

									<div class="row-fluid noHide">
										<div class="control-group well well-small" id="formSettings_formContainer_container">
											<ul class="checkboxList">
												<li><label class="checkbox" for="formSettings_formContainer"><input type="checkbox" id="formSettings_formContainer" name="formSettings_formContainer" {local var="formContainer"}> Act as Container</label></li>
												<li><label class="checkbox" for="formSettings_formProduction"><input type="checkbox" id="formSettings_formProduction" name="formSettings_formProduction" {local var="formProduction"}> Production Ready</label></li>
												<li><label class="checkbox" for="formSettings_formMetadata"><input type="checkbox" id="formSettings_formMetadata" name="formSettings_formMetadata" {local var="formMetadata"}> Metadata Form</label></li>
											</ul>
											<span class="help-block hidden"></span>
										</div>
									</div>
								</div>
							</div>

							<div class="row-fluid">
								<form class="form form-horizontal" id="submitForm" name="submitForm" method="post">
									<input type="hidden" name="id" value="{local var="formID"}">
									<input type="hidden" name="form">
									<input type="hidden" name="fields">
									<input type="submit" class="btn btn-large btn-block btn-primary" name="submitForm" value="{local var="thisSubmitButton"}" disabled>
									<noscript><p style="color:red; text-align: center; font-weight: bold;">JavaScript failed to load!</p></noscript>
									{engine name="csrf"}
								</form>
							</div>
						</div>
					</div>

					<div class="span7">
						<div id="rightPanel">
							<form class="form-horizontal" id="formPreview_container">
								<h2 id="formTitle"></h2>
								<p id="formDescription"></p>
								<ul class="unstyled sortable" id="formPreview">
									{local var="formPreview"}
								</ul>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>



		<?php if (!isnull($formID)) { ?>

		<?php if (!forms::isMetadataForm($formID)) { ?>
		<div class="tab-pane" id="projects">
			<h2>Change Project Membership</h2>

			<form action="{phpself query="true"}" method="post">
			{local var="projectOptions"}
			{engine name="csrf"}
			<input type="submit" class="btn btn-primary" name="projectForm" disabled>
			<noscript><p style="color:red; text-align: center; font-weight: bold;">JavaScript failed to load!</p></noscript>
			</form>
		</div>
		<?php } ?>

		<div class="tab-pane" id="navigation">
			<header class="page-header">
				<h1>Navigation Creator</h1>
			</header>

			<div class="container-fluid">
				<div class="row-fluid" id="results">
					{local var="results"}
				</div>

				<div class="row-fluid">
					<div class="span6">
						<ul class="nav nav-tabs" id="groupingTab">
							<li><a href="#groupingsAdd" data-toggle="tab">Add</a></li>
							<li><a href="#groupingsSettings" data-toggle="tab">Settings</a></li>
						</ul>

						<div class="tab-content">
							<div class="tab-pane" id="groupingsAdd">
								<ul class="unstyled draggable span6">
									<li><a href="#" class="btn btn-block">New Grouping</a></li>
									<li><a href="#" class="btn btn-block">Log Out</a></li>
								</ul>
								<ul class="unstyled draggable span6">
									<li><a href="#" class="btn btn-block">Export Link (needs definable properties)</a></li>
									<li><a href="#" class="btn btn-block">Link</a></li>
								</ul>

								{local var="metadataForms"}
							</div>

							<div class="tab-pane" id="groupingsSettings">
								<div class="alert alert-block" id="noGroupingSelected">
									<h4>No Grouping Selected</h4>
									To change a grouping, click on it in the preview to the right.
								</div>

								<div class="control-group well well-small" id="groupingsSettings_container_grouping">
									<label for="groupingsSettings_grouping">
										Grouping Label
									</label>
									<input type="text" class="input-block-level" id="groupingsSettings_grouping" name="groupingsSettings_grouping" />
									<span class="help-block hidden"></span>
								</div>

								<div class="control-group well well-small" id="groupingsSettings_container_label">
									<label for="groupingsSettings_label">
										Label
									</label>
									<input type="text" class="input-block-level" id="groupingsSettings_label" name="groupingsSettings_label" />
									<span class="help-block hidden"></span>
								</div>

								<div class="control-group well well-small" id="groupingsSettings_container_url">
									<label for="groupingsSettings_url">
										Address
									</label>
									<input type="text" class="input-block-level" id="groupingsSettings_url" name="groupingsSettings_url" />
									<span class="help-block hidden"></span>
								</div>
							</div>
						</div>

						<div class="row-fluid">
							<form class="form form-horizontal" id="submitNavigation" name="submitNavigation" method="post">
								<input type="hidden" name="id" value="{local var="formID"}">
								<input type="hidden" name="groupings">
								<input type="submit" class="btn btn-large btn-block btn-primary" name="submitNavigation" value="Update Navigation">
								{engine name="csrf"}
							</form>
						</div>
					</div>

					<div class="span6">
						<ul class="sortable unstyled" id="GroupingsPreview">
							{local var="existingGroupings"}
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="permissions">
			<header class="page-header">
				<h1>Form Permissions</h1>
			</header>

			<div class="container-fluid">
				<div class="row-fluid" id="results">
					{local var="results"}
				</div>

				<div class="row-fluid">
					<form name="submitPermissions" method="post">
						{engine name="csrf"}
						<table>
							<tr>
								<th>Data Entry Users</th>
								<th>Data View Users</th>
								<th>Administrators</th>
							</tr>
							<tr>
								<td>
									<select name="selectedEntryUsers[]" id="selectedEntryUsers" size="5" multiple="multiple">
										{local var="selectedEntryUsers"}
									</select>
									<br />
									<select name="availableEntryUsers" id="availableEntryUsers" onchange="addItemToID('selectedEntryUsers', this.options[this.selectedIndex])">
										{local var="availableUsersList"}
									</select>
									<br />
									<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedEntryUsers', this.form.selectedEntryUsers)" />
								</td>
								<td>
									<select name="selectedViewUsers[]" id="selectedViewUsers" size="5" multiple="multiple">
										{local var="selectedViewUsers"}
									</select>
									<br />
									<select name="availableViewUsers" id="availableViewUsers" onchange="addItemToID('selectedViewUsers', this.options[this.selectedIndex])">
										{local var="availableUsersList"}
									</select>
									<br />
									<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedViewUsers', this.form.selectedViewUsers)" />
								</td>
								<td>
									<select name="selectedUsersAdmins[]" id="selectedUsersAdmins" size="5" multiple="multiple">
										{local var="selectedUsersAdmins"}
									</select>
									<br />
									<select name="availableUsersAdmins" id="availableUsersAdmins" onchange="addItemToID('selectedUsersAdmins', this.options[this.selectedIndex])">
										{local var="availableUsersList"}
									</select>
									<br />
									<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedUsersAdmins', this.form.selectedUsersAdmins)" />
								</td>
							</tr>
						</table>
						<input type="submit" class="btn btn-large btn-block btn-primary" name="submitPermissions" value="Update Permissions" />
					</form>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="deleteForm">
			<form method="post" action="" id="deleteFormFrm">
				{engine name="csrf"}
				<input type="hidden" name="deleteForm" value="deleteForm">
				<p>Are you sure you want to delete this form?</p>
				<p>This will permanently delete this form and all associated objects, and cannot be undone.</p>
				<input type="button" value="Cancel" class="btn" id="deleteFormBtn-Cancel">
				<input type="button" value="Delete Form" class="btn btn-danger" id="deleteFormBtn-Submit">
			</form>
		</div>
		<?php } ?>
	</div>
</section>

<div class="modal hide fade" id="formTypeSelector">
	<div class="modal-header">
		<h3>What type of form will this be?</h3>
	</div>
	<div class="modal-body text-center">
		<button class="btn btn-large">Metadata</button>
		<button class="btn btn-large">Object</button>
	</div>
</div>
{local var="displayModal"}

<?php
$engine->eTemplate("include","footer");
?>