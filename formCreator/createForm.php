<?php
include("../header.php");

recurseInsert("acl.php","php");

$formID = isset($engine->cleanPost['HTML']['id']) ? $engine->cleanPost['HTML']['id'] : (isset($engine->cleanGet['HTML']['id']) ? $engine->cleanGet['HTML']['id'] : NULL);
if (is_empty($formID)) {
	$formID = NULL;
}

if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
	$engine->openDB->transBegin();

	$form   = json_decode($engine->cleanPost['RAW']['form'], TRUE);
	$fields = json_decode($engine->cleanPost['RAW']['fields'], TRUE);

	// Ensure all fields have an ID for the label. Assign it the value of name if needed.
	foreach ($fields as $I => $field) {
		if (is_empty($field['cssID'])) {
			$fields[$I]['cssID'] = $field['name'];
		}
	}

	if (!isnull($formID)) {
		// Update forms table
		$sql = sprintf("UPDATE `%s` SET `title`='%s', `description`=%s, `fields`='%s', `container`='%s', `production`='%s', `metadata`='%s' WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($form['formTitle']),
			!is_empty($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL",
			encodeFields($fields),
			$engine->openDB->escape($form['formContainer']),
			$engine->openDB->escape($form['formProduction']),
			$engine->openDB->escape($form['formMetadata']),
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - updating form: ".$sqlResult['error'], errorHandle::DEBUG);
			errorHandle::errorMsg("Failed to update form.");
		}
	}
	else {
		// Insert into forms table
		$sql = sprintf("INSERT INTO `%s` (title, description, fields, container, production, metadata) VALUES ('%s',%s,'%s','%s','%s','%s')",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($form['formTitle']),
			isset($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL",
			encodeFields($fields),
			$engine->openDB->escape($form['formContainer']),
			$engine->openDB->escape($form['formProduction']),
			$engine->openDB->escape($form['formMetadata'])
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

$tmp = '<option value="none">None</option>';
foreach (validator::getValidationTypes() as $val => $text) {
	$tmp .= '<option value="'.$val.'">'.$text.'</option>';
}
localVars::add("validationTypes",$tmp);

// Get list of forms for choices dropdown
$sql = sprintf("SELECT ID, `title` FROM `%s` ORDER BY `title`",
	$engine->openDB->escape($engine->dbTables("forms"))
	);
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$tmp = array();
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$tmp[] = sprintf('<option value="%s">%s</option>',
			$row['ID'],
			$row['title']
			);
	}
	localVars::add("formsOptions",implode(",",$tmp));
}


if (!is_empty($engine->errorStack)) {
	localVars::add("results",errorHandle::prettyPrint());
}

if (!isnull($formID)) {
	// Get form info for display
	$sql = sprintf("SELECT * FROM `%s` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($formID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
		localVars::add("formID",htmlSanitize($row['ID']));
		localVars::add("formTitle",htmlSanitize($row['title']));
		localVars::add("formDescription",htmlSanitize($row['description']));

		$formPreview = NULL;
		if (!is_empty($row['fields'])) {
			$fields = decodeFields($row['fields']);

			foreach((array)$fields as $I => $field) {
				$values = json_encode($field);

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
					htmlSanitize($field['position']),
					htmlSanitize($field['position']),
					htmlSanitize($field['position']),
					htmlSanitize($field['type']),
					htmlSanitize($field['position']),
					htmlSanitize($field['position']),
					htmlSanitize($field['type']),
					$values
					);
			}
		}
		localVars::add("formPreview",$formPreview);
	}
}

$engine->eTemplate("include","header");
?>

<script type="text/javascript" src="{local var="siteRoot"}includes/js/createForm.js"></script>

<section>
	<header class="page-header">
		<h1>Form Creator</h1>
	</header>

	<div class="container-fluid">
		<div class="row-fluid" id="results">
			{local var="results"}
		</div>

		<div class="row-fluid">
			<div class="span5">
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
									<li><a href="#" class="btn btn-block">Single Line Text</a></li>
									<li><a href="#" class="btn btn-block">Paragraph Text</a></li>
									<li><a href="#" class="btn btn-block">Radio</a></li>
									<li><a href="#" class="btn btn-block">Checkboxes</a></li>
									<li><a href="#" class="btn btn-block">Dropdown</a></li>
									<li><a href="#" class="btn btn-block">Number</a></li>
								</ul>
							</div>
							<div class="span6">
								<ul class="unstyled draggable">
									<li><a href="#" class="btn btn-block">Email</a></li>
									<li><a href="#" class="btn btn-block">Phone</a></li>
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
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_value">
										<label for="fieldSettings_value">
											Value
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_value" name="fieldSettings_value" />
										<span class="help-block hidden"></span>
									</div>
								</span>

								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_placeholder">
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
									<div class="control-group well well-small" id="fieldSettings_container_cssID">
										<label for="fieldSettings_cssID">
											CSS ID
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_cssID" name="fieldSettings_cssID" />
										<span class="help-block hidden"></span>
									</div>
								</span>

								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_cssClass">
										<label for="fieldSettings_cssClass">
											CSS Classes
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_cssClass" name="fieldSettings_cssClass" />
										<span class="help-block hidden"></span>
									</div>
								</span>
							</div>

							<div class="row-fluid noHide">
								<div class="control-group well well-small" id="fieldSettings_container_styles">
									<label for="fieldSettings_styles">
										Local Styles
										<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="You can set any HTML styles and they will only apply to this field."></i>
									</label>
									<input type="text" class="input-block-level" id="fieldSettings_styles" name="fieldSettings_styles" />
									<span class="help-block hidden"></span>
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
								<p>
									<div id="fieldSettings_choices_manual">
										<div class="input-prepend input-append">
											<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>
											<input name="fieldSettings_choices_text" class="input-block-level" type="text" value="First Choice">
											<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>
											<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>
										</div>
										<div class="input-prepend input-append">
											<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>
											<input name="fieldSettings_choices_text" class="input-block-level" type="text" value="Second Choice">
											<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>
											<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>
										</div>
									</div>
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

							<div class="row-fluid noHide">
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_options">
										<label for="fieldSettings_options">
											Options
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required"> Required
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates"> No Duplicates
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly"> Read Only
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_disabled" name="fieldSettings_options_disabled"> Disabled
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_publicRelease" name="fieldSettings_options_publicRelease"> Public Release
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_sortable" name="fieldSettings_options_sortable"> Sortable
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_searchable" name="fieldSettings_options_searchable"> Searchable
										</label>
									</div>
								</span>
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_access">
										<label for="fieldSettings_validation">
											Validation
										</label>
										<select class="input-block-level" id="fieldSettings_validation" name="fieldSettings_validation">
											{local var="validationTypes"}
										</select>
										<input type="text" class="input-block-level" id="fieldSettings_validationRegex" name="fieldSettings_validationRegex" placeholder="Enter a Regex" />
									</div>

									<div class="control-group well well-small" id="fieldSettings_container_access">
										<label for="fieldSettings_access">
											Allow Access
										</label>
										<select class="input-block-level" id="fieldSettings_access" name="fieldSettings_access" multiple>
										</select>
									</div>
								</span>
							</div>

							<div class="control-group well well-small" id="fieldSettings_container_range">
								<div class="row-fluid">
									<span class="span4">
										<label for="fieldSettings_range_min">
											Min
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_range_min" name="fieldSettings_range_min" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_range_max">
											Max
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_range_max" name="fieldSettings_range_max" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_range_format">
											Format
										</label>
										<select class="input-block-level" id="fieldSettings_range_format" name="fieldSettings_range_format"></select>
									</span>
								</div>
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

							<div class="control-group well well-small" id="formSettings_formContainer_container">
								<input type="checkbox" id="formSettings_formContainer" name="formSettings_formContainer"> <label class="checkbox" for="formSettings_formContainer" style="display: inline;">Act as Container</label> <br />
								<input type="checkbox" id="formSettings_formProduction" name="formSettings_formProduction"> <label class="checkbox" for="formSettings_formProduction" style="display: inline;">Production Ready</label> <br />
								<input type="checkbox" id="formSettings_formMetadata" name="formSettings_formMetadata"> <label class="checkbox" for="formSettings_formMetadata" style="display: inline;">Metadata Form</label> <br />
								<span class="help-block hidden"></span>
							</div>
						</div>

						<div class="row-fluid noHide">
							<form class="form form-horizontal" id="submitForm" name="submitForm" method="post">
								<input type="hidden" name="id" value="{local var="formID"}">
								<input type="hidden" name="form">
								<input type="hidden" name="fields">
								<input type="submit" class="btn btn-large btn-block btn-primary" name="submitForm" value="Add/Update Form">
								{engine name="csrf"}
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="span7">
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
</section>

<?php
$engine->eTemplate("include","footer");
?>
