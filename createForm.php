<?php
include("header.php");

recurseInsert("acl.php","php");

$formID = isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : NULL;

if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
	$engine->openDB->transBegin();

	$form   = json_decode($engine->cleanPost['RAW']['form'], TRUE);
	$fields = json_decode($engine->cleanPost['RAW']['fields'], TRUE);

	if (!isnull($formID)) {
		// Update forms table
		$sql = sprintf("UPDATE `%s` SET `title`='%s', `description`=%s WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($form['formTitle']),
			!is_empty($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL",
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::errorMsg("Failed to update form.");
		}

		// Delete from fields table
		$sql = sprintf("DELETE FROM `%s` WHERE formID='%s'",
			$engine->openDB->escape($engine->dbTables("fields")),
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::errorMsg("Failed to update fields.");
		}
	}
	else {
		// Insert into forms table
		$sql = sprintf("INSERT INTO `%s` VALUES (NULL,'%s',%s)",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($form['formTitle']),
			isset($form['formDescription']) ? "'".$engine->openDB->escape($form['formDescription'])."'" : "NULL"
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::errorMsg("Failed to create form.");
		}
		else {
			$formID = $sqlResult['id'];
		}
	}

	// Insert into fields table
	foreach ($fields as $fieldOptions) {
		$sql = sprintf("INSERT INTO `%s` VALUES (NULL,'%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
			$engine->openDB->escape($engine->dbTables("fields")),
			$engine->openDB->escape($formID),
			$engine->openDB->escape($fieldOptions['position']),
			$engine->openDB->escape($fieldOptions['type']),
			$engine->openDB->escape($fieldOptions['name']),
			$engine->openDB->escape($fieldOptions['label']),
			!is_empty($fieldOptions['rangeMin'])        ? "'".$engine->openDB->escape($fieldOptions['rangeMin'])."'"        : "NULL",
			!is_empty($fieldOptions['rangeMax'])        ? "'".$engine->openDB->escape($fieldOptions['rangeMax'])."'"        : "NULL",
			!is_empty($fieldOptions['rangeFormat'])     ? "'".$engine->openDB->escape($fieldOptions['rangeFormat'])."'"     : "NULL",
			!is_empty($fieldOptions['placeholder'])     ? "'".$engine->openDB->escape($fieldOptions['placeholder'])."'"     : "NULL",
			!is_empty($fieldOptions['cssClass'])        ? "'".$engine->openDB->escape($fieldOptions['cssClass'])."'"        : "NULL",
			!is_empty($fieldOptions['cssID'])           ? "'".$engine->openDB->escape($fieldOptions['cssID'])."'"           : "NULL",
			!is_empty($fieldOptions['validation'])      ? "'".$engine->openDB->escape($fieldOptions['validation'])."'"      : "NULL",
			!is_empty($fieldOptions['validationRegex']) ? "'".$engine->openDB->escape($fieldOptions['validationRegex'])."'" : "NULL",
			!is_empty($fieldOptions['access'])          ? "'".$engine->openDB->escape($fieldOptions['access'])."'"          : "NULL",
			!is_empty($fieldOptions['publicRelease'])   ? "'".$engine->openDB->escape($fieldOptions['publicRelease'])."'"   : "NULL",
			!is_empty($fieldOptions['required'])        ? "'".$engine->openDB->escape($fieldOptions['required'])."'"        : "NULL",
			!is_empty($fieldOptions['duplicates'])      ? "'".$engine->openDB->escape($fieldOptions['duplicates'])."'"      : "NULL",
			!is_empty($fieldOptions['defaultValue'])    ? "'".$engine->openDB->escape($fieldOptions['defaultValue'])."'"    : "NULL",
			!is_empty($fieldOptions['readonly'])        ? "'".$engine->openDB->escape($fieldOptions['readonly'])."'"        : "NULL",
			!is_empty($fieldOptions['disable'])         ? "'".$engine->openDB->escape($fieldOptions['disable'])."'"         : "NULL",
			!is_empty($fieldOptions['sortable'])        ? "'".$engine->openDB->escape($fieldOptions['sortable'])."'"        : "NULL",
			!is_empty($fieldOptions['searchable'])      ? "'".$engine->openDB->escape($fieldOptions['searchable'])."'"      : "NULL",
			!is_empty($fieldOptions['localCSS'])        ? "'".$engine->openDB->escape($fieldOptions['localCSS'])."'"        : "NULL",
			!is_empty($fieldOptions['fieldset'])        ? "'".$engine->openDB->escape($fieldOptions['fieldset'])."'"        : "NULL",
			!is_empty($fieldOptions['choicesType'])     ? "'".$engine->openDB->escape($fieldOptions['choicesType'])."'"     : "NULL",
			!is_empty($fieldOptions['choicesDefault'])  ? "'".$engine->openDB->escape($fieldOptions['choicesDefault'])."'"  : "NULL",
			!is_empty($fieldOptions['choicesOptions'])  ? "'".$engine->openDB->escape($fieldOptions['choicesOptions'])."'"  : "NULL"
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::errorMsg("Failed to create form field '".$fieldOptions['name']."'.");
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
		localVars::add("formTitle",$row['title']);
		localVars::add("formDescription",$row['description']);

		// Get field info for display
		$sql = sprintf("SELECT * FROM `%s` WHERE formID='%s'",
			$engine->openDB->escape($engine->dbTables("fields")),
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			$formPreview = NULL;
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				$values = json_encode($row);
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
					htmlSanitize($row['position']),
					htmlSanitize($row['position']),
					htmlSanitize($row['position']),
					htmlSanitize($row['type']),
					htmlSanitize($row['position']),
					htmlSanitize($row['position']),
					htmlSanitize($row['type']),
					$values
					);
			}
			localVars::add("formPreview",$formPreview);
		}

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
					<li class="active"><a href="#fieldAdd" data-toggle="tab">Add a Field</a></li>
					<li><a href="#fieldSettings" data-toggle="tab">Field Settings</a></li>
					<li><a href="#formSettings" data-toggle="tab">Form Settings</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="fieldAdd">
						<div class="span6">
							<ul class="unstyled">
								<li><a href="#" class="btn btn-block">Single Line Text</a></li>
								<li><a href="#" class="btn btn-block">Paragraph Text</a></li>
								<li><a href="#" class="btn btn-block">Multiple Choice</a></li>
								<li><a href="#" class="btn btn-block">Checkboxes</a></li>
								<li><a href="#" class="btn btn-block">Dropdown</a></li>
								<li><a href="#" class="btn btn-block">Number</a></li>
							</ul>
						</div>
						<div class="span6">
							<ul class="unstyled">
								<li><a href="#" class="btn btn-block">Email</a></li>
								<li><a href="#" class="btn btn-block">Phone</a></li>
								<li><a href="#" class="btn btn-block">Date</a></li>
								<li><a href="#" class="btn btn-block">Time</a></li>
								<li><a href="#" class="btn btn-block">Website</a></li>
							</ul>
						</div>
					</div>

					<div class="tab-pane" id="fieldSettings">
						<div class="alert alert-block" id="noFieldSelected">
							<h4>No Field Selected</h4>
							To change a field, click on it in the form preview to the right.
						</div>

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
									<div class="control-group well well-small" id="fieldSettings_container_defaultValue">
										<label for="fieldSettings_defaultValue">
											Default Value
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_defaultValue" name="fieldSettings_defaultValue" />
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
									<div class="control-group well well-small" id="fieldSettings_container_ID">
										<label for="fieldSettings_ID">
											HTML ID
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_ID" name="fieldSettings_ID" />
										<span class="help-block hidden"></span>
									</div>
								</span>

								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_fieldset">
										<label for="fieldSettings_fieldset">
											Fieldset Label
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If a label is entered here, the field will be surrounded by a FieldSet, and the label used."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_fieldset" name="fieldSettings_fieldset" />
										<span class="help-block hidden"></span>
									</div>
								</span>
							</div>

							<div class="row-fluid noHide">
								<div class="control-group well well-small" id="fieldSettings_container_class">
									<label for="fieldSettings_class">
										HTML Classes
										<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
									</label>
									<input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" />
									<span class="help-block hidden"></span>
								</div>
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
											<input type="checkbox" id="fieldSettings_options_disable" name="fieldSettings_options_disable"> Disabled
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
								<label for="fieldSettings_min">
									Range
									<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title=""></i>
								</label>

								<div class="row-fluid">
									<span class="span4">
										<label for="fieldSettings_min">
											Min
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_min" name="fieldSettings_min" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_max">
											Max
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_max" name="fieldSettings_max" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_format">
											Format
										</label>
										<select class="input-block-level" id="fieldSettings_format" name="fieldSettings_format"></select>
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
						</div>

						<div class="row-fluid noHide">
							<form class="form form-horizontal" id="submitForm" name="submitForm" method="post">
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
					<ul class="unstyled" id="formPreview">
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
