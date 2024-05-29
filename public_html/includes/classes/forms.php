<?php

// @TODO this file should be broken up. It is currently handling two distinct functions
// 1. building and taking web form submissions
// 1. handling everything about a "form"
// A place holder file has been created, called web_forms.php to support the refactoring

class forms {

	// @TODO this needs to be expanded to include callbacks for the replacement text as well.
	private static $fieldVariables = array('%userid%', '%username%', '%firstname%', '%lastname%', '%date%', '%time%', '%time12%', '%time24%', '%timestamp%');

	function validID() {
		$engine = EngineAPI::singleton();

		if (!isset($engine->cleanGet['MYSQL']['formID'])
			|| is_empty($engine->cleanGet['MYSQL']['formID'])
			|| !validate::integer($engine->cleanGet['MYSQL']['formID'])) {

			if (objects::validID($engine->cleanGet['MYSQL']['objectID'])) {
				$object = objects::get($engine->cleanGet['MYSQL']['objectID']);

				if ($object === FALSE) {
					return FALSE;
				}

				http::setGet('formID',$object['formID']);

			}
			else {
				return FALSE;
			}
		}

		return TRUE;

	}

	public static function get($formID=NULL,$productionOnly=FALSE) {

		if (isnull($formID)) {
			return self::getForms();
		}

		$mfcs      = mfcs::singleton();
		$cachID    = "getForm:".$formID;
		$cache     = $mfcs->cache("get",$cachID);

		if (!isnull($cache)) {
			return($cache);
		}

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT * FROM `forms` WHERE `ID`='%s'%s",
			$engine->openDB->escape($formID),
			($productionOnly === TRUE)?" AND `production`='1'":""
		);

		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}


		if ($sqlResult['numrows'] == 0) {
			return FALSE;
		}

		$form           = mysqli_fetch_array($sqlResult['result']);

		if (($form['fields'] = unserialize(base64_decode($form['fields']))) === FALSE) {
			errorHandle::newError(__METHOD__."() - fields", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if (($form['idno'] = unserialize(base64_decode($form['idno']))) === FALSE) {
			errorHandle::newError(__METHOD__."() - idno", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if (!isempty($form['navigation']) && ($form['navigation'] = unserialize(base64_decode($form['navigation']))) === FALSE) {
			errorHandle::newError(__METHOD__."() - navigation!", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if ($mfcs->cache("create",$cachID,$form) === FALSE) {
			errorHandle::newError(__METHOD__."() - unable to cache form", errorHandle::DEBUG);
		}

		return $form;
	}

	public static function getForms($type = NULL,$productionOnly=FALSE) {

		$engine = EngineAPI::singleton();

		if ($type === TRUE) {
			$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='0' ORDER BY `title`");
		}
		else if ($type === FALSE) {
			$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='1' ORDER BY `title`");
		}
		else if (isnull($type)) {
			$sql = sprintf("SELECT `ID` FROM `forms` ORDER BY `title`");
		}
		else {
			return(FALSE);
		}

		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$forms = array();
		while ($row = mysqli_fetch_array($sqlResult['result'])) {

			$forms[$row['ID']] = self::get($row['ID'],$productionOnly);

		}

		return $forms;

	}

	public static function getObjectForms($productionOnly=FALSE) {
		return self::getForms(TRUE,$productionOnly);
	}

	public static function getMetadataForms($productionOnly=FALSE) {
		return self::getForms(FALSE,$productionOnly);
	}

	public static function title($formID) {
		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		$form_title = (is_empty($form['title']))?'[No form title]':$form['title'];
		if (!is_empty($form['displayTitle'])) {
			$form_title = $form['displayTitle'];
		}

		return htmlSanitize($form_title);
	}

	public static function description($formID){
		$form = self::get($formID);

		if($form === FALSE) {
			return FALSE;
		}

		return htmlSanitize(isnull($form['description']) ? '' : $form['description']);
	}

	public static function getObjectTitleField($formID) {
		$form = self::get($formID);

		return $form['objectTitleField'];
	}

	public static function isContainer($formID) {
		$form = self::get($formID);

		if ((int)$form['container'] === 1) {
			return TRUE;
		}
		else {
			return FALSE;
		}

		return FALSE;
	}

	public static function isProductionReady($formID) {
		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		if ((int)$form['production'] == 1) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	/*
	 * Returns all of the linked metadata forms for an object form
	 */
	public static function getObjectFormMetaForms($formID) {

		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		$metadataForms = array();
		foreach ((array)$form['fields'] as $field) {
			if (isset($field['choicesForm']) && validate::integer($field['choicesForm'])) {
				$metaForm           = self::get($field['choicesForm']);
				$metaForm['formID'] = $field['choicesForm'];
				$metadataForms[$field['choicesForm']] = $metaForm;
			}
		}

		return $metadataForms;

	}

	/*
	 * Returns all of the object forms that link to this metadataform
	 */
	public static function getFormsLinkedTo($formID) {

		// make sure the provided form exists
		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		// make sure its a metadata form
		if (self::isMetadataForm($formID) === FALSE) {
			return FALSE;
		}

		if (($forms = self::getObjectForms()) === FALSE) {
			return FALSE;
		}

		$linkedForms = array();
		foreach ($forms as $form) {
			foreach ($form['fields'] as $field) {
				if (isset($field['choicesForm']) && validate::integer($field['choicesForm']) && $field['choicesForm'] == $formID) {
					$linkedForms[$form['ID']] = $field;
					break;
				}
			}
		}

		return $linkedForms;

	}

	public static function isMetadataForm($formID) {
		$form = self::get($formID);

		if ($form['metadata'] == 1) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	public static function formHasProjects($formID){
		if(validate::integer($formID)){
			$engine    = EngineAPI::singleton();
			$sql       = sprintf("SELECT count(*) FROM forms_projects WHERE formID = %s",  $formID);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

			$result = mysqli_fetch_array($sqlResult['result']);
			return $result['count(*)'];
		} else {
			return FALSE;
		}
	}


	public static function checkFormInProject($projectID,$formID) {
		$projectForms = projects::getForms($projectID);

		if (in_array($formID, $projectForms)) {
			return TRUE;
		}

		foreach ($projectForms as $projectFormID) {
			$metadataForms = self::getObjectFormMetaForms($projectFormID);
			if (isset($metadataForms[$formID])) {
				return TRUE;
			}
		}

		return FALSE;

	}

	public static function checkFormInCurrentProjects($formID) {
		$formsProjectCount = self::formHasProjects($formID);

		if(validate::integer($formsProjectCount) && $formsProjectCount == 0){
			return TRUE;
		}

		foreach (sessionGet('currentProject') as $projectID=>$project) {
			if (self::checkFormInProject($projectID,$formID) === TRUE) {
				return TRUE;
			}
		}

		$formWarning = '<div class="formAlert alert alert-warning alert-dismissible" role="alert">
  							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  							<strong>Warning!</strong>
  							<p> This form is not associated with any of your current projects. This message will close in 15 seconds. </p>
						</div>';

		localVars::add("projectWarning",$formWarning);

		return FALSE;
	}

	/**
	 * Returns the number of items in the given formID
	 * @param  int $formID ID of the form we are counting objects in
	 * @return int         number of objects in the form
	 */
	public static function countInForm($formID) {

		$sql       = sprintf("SELECT COUNT(*) FROM `objects` WHERE `formID`='%s'",
			mfcs::$engine->openDB->escape($formID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$row = mysqli_fetch_array($sqlResult['result']);

		return $row['COUNT(*)'];

	}

	// Gets all the forms that are in all the projects that that $objectID is in
	public static function getObjectProjectForms($objectID) {
		if (($object = objects::get($objectID)) === FALSE) {
			return FALSE;
		}

		if (($projects = projects::getAllObjectProjects($objectID)) === FALSE) {
			return FALSE;
		}

		$forms = array();
		foreach ($projects as $project) {
			if (($projectForms = projects::getForms($project['ID'],TRUE)) === FALSE) {
				return FALSE;
			}

			foreach ($projectForms as $formID=>$form) {
				$forms[$formID] = $form;
			}

		}

		return $forms;
	}

	public static function getFormIDInfo($formID) {
		$form = self::get($formID);
		return $form['idno'];
	}

	public static function IDNO_is_managed($formID) {
		$idno_info = self::getFormIDInfo($formID);

		if ($idno_info['managedBy'] == "system") {
			return TRUE;
		}

		return FALSE;
	}

	public static function getField($formID,$fieldName) {

		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		foreach ($form['fields'] as $field) {
			if ($field['name'] == $fieldName) {
				return $field;
			}
		}

		return FALSE;

	}

	public static function getFields($formID) {
		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}
		else {
			return $form['fields'];
		}
	}

	public static function get_file_fields($formID) {
		if (($fields = self::getFields($formID)) === FALSE) {
			return FALSE;
		}

		$file_fields = array();
		foreach ($fields as $field) {
			if ($field['type'] == "file") $file_fields[] = $field;
		}

		return $file_fields;
	}

	public static function getFieldChoices($field) {

		$choices = array();

		// Manually selected
		if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
			if (isempty($field['choicesOptions'])) {
				errorHandle::errorMsg("No options provided, '".$field['label']."'");
				return FALSE;
			}

			foreach ($field['choicesOptions'] as $I=>$option) {
				$choices[] = array("value" => $option, "index" => $I, "display" => $option);
			}

		}
		// Pull from another Form
		else {

			if (($objects = objects::getAllObjectsForForm($field['choicesForm'])) === FALSE) {
				errorHandle::errorMsg("Can't retrieved linked metadata objects.");
				return FALSE;
			}

			$objects = objects::sort($objects);

			if ($field['required'] == "true" && count($objects) < 1) {
				errorHandle::errorMsg("Required linked table doesn't have any options.");
				return FALSE;
			}

			foreach ($objects as $object) {
				$choices[] = array("value" => $object['ID'], "index" => $object['ID'], "display" => $object['data'][$field['choicesField']]);
			}

		}

		return $choices;
	}

	private static function drawCheckBoxes($field,$fieldChoices) {

		$output = "";

		foreach ($fieldChoices as $choice) {
			$output .= sprintf('<input type="%s" name="%s" id="%s_%s" value="%s" %s/><label for="%s_%s">%s</label>',
				htmlSanitize($field['type']),
				htmlSanitize($field['name']),
				htmlSanitize($field['name']),
				htmlSanitize($choice['index']),
				htmlSanitize($choice['value']),
				(isset($field['choicesDefault']) && !isempty($field['choicesDefault']) && $field['choicesDefault'] == $option)?'checked="checked"':"",
				htmlSanitize($field['name']),
				htmlSanitize($choice['index']),
				htmlSanitize($choice['display'])
			);
		}

		return $output;

	}

	private static function drawSelectDropdowns($field,$fieldChoices,$value=NULL) {

		$output = "";

		if(isset($field['choicesNull']) && str2bool($field['choicesNull'])){
			$output .= '<option value="">Make a selection</option>';
		}
		foreach ($fieldChoices as $choice) {

			if (isnull($value) && isset($field['choicesFieldDefault']) && !isempty($field['choicesFieldDefault']) && $choice['display'] == $field['choicesFieldDefault']) {
				$value = $choice['value'];
			}
			else if (isnull($value) && isset($field['choicesDefault']) && !isempty($field['choicesDefault'])) {
				$value = $field['choicesDefault'];
			}

			$output .= sprintf('<option value="%s" %s>%s</option>',
				htmlSanitize($choice['value']),
				(!isnull($value) && $value == $choice['value'])?'selected="selected"':"",
				htmlSanitize($choice['display'])
			);
		}
		return $output;
	}

	private static function drawMultiselectBoxes($field,$fieldChoices) {
		$output = "";

		if(isset($field['choicesNull']) && str2bool($field['choicesNull'])){
			$output .= '<option value="">Make a selection</option>';
		}

		foreach ($fieldChoices as $choice) {
			$output .= sprintf('<option value="%s" %s>%s</option>',
				htmlSanitize($choice['value']),
				(isset($field['choicesDefault']) && !isempty($field['choicesDefault']) && $field['choicesDefault'] == $choice['value'])?'selected="selected"':"",
				htmlSanitize($choice['display'])
			);
		}
		return $output;
	}

	public static function drawFieldChoices($field,$choices,$value=NULL) {

		if (!isset($field['type'])) return FALSE;

		switch($field['type']) {
			case "checkbox":
			case "radio":
				return self::drawCheckBoxes($field,$choices);
				break;
			case "select":
				return self::drawSelectDropdowns($field,$choices,$value);
				break;
			case "multiselect":
				return self::drawMultiselectBoxes($field,$choices);
				break;
			default:
				return FALSE;
				break;
		}

		return FALSE;

	}

	private static function publicReleaseObjSelect($objectID, $object, $form) {
		if(!isnull($objectID)) {
			if ($object['publicRelease'] == "0") return false;
		}
		else if ($form['objPublicReleaseDefaultTrue'] == "0") {
			return false;
		}
		return true;
	}

	public static function build($formID, $objectID = NULL, $error = FALSE) {
		$form = self::getForm($formID);
		if ($form === FALSE) {
			return FALSE;
		}
	
		$object = self::getObject($objectID, $error);
		$output = self::generateFormStart($formID, $objectID);
	
		$output .= sessionInsertCSRF();
		$output .= self::addHiddenFields();
		$output .= self::addPublicReleaseField($form, $objectID, $object);
		$output .= self::generateFields($form['fields'], $object, $error);
		$output .= self::generateFormEnd($form, $objectID);
	
		return $output;
	}
	
	private static function getForm($formID) {
		$form = self::get($formID);
		return ($form !== FALSE) ? $form : FALSE;
	}
	
	private static function getObject($objectID, $error) {
		if (!is_null($objectID)) {
			$object = objects::get($objectID, TRUE);
			if ($object === FALSE) {
				errorHandle::errorMsg("Error retrieving object.");
			}
			return $object;
		} elseif (is_null($objectID) && $error === TRUE) {
			return ['data' => []];
		}
		return NULL;
	}
	
	private static function generateFormStart($formID, $objectID) {
		$action = sprintf('%s?formID=%s%s', $_SERVER['PHP_SELF'], htmlSanitize($formID), (!is_null($objectID)) ? '&objectID=' . $objectID : "");
		return sprintf('<form action="%s" method="post" name="insertForm" data-formid="%s">', $action, mfcs::$engine->openDB->escape($formID));
	}
	
	private static function addHiddenFields() {
		$output = '';
		if (isset($engine->cleanGet['HTML']['parentID'])) {
			$output .= sprintf('<input type="hidden" name="parentID" value="%s">', $engine->cleanGet['HTML']['parentID']);
		}
		if (!isempty(localvars::get("lockID"))) {
			$output .= sprintf('<input type="hidden" name="lockID" value="%s">', localvars::get("lockID"));
		}
		return $output;
	}
	
	private static function addPublicReleaseField($form, $objectID, $object) {
		$output = '';
		if ($form['objPublicReleaseShow'] == 1 && $form['metadata'] == 0) {
			$objPublicReleaseDefaultTrueYes = forms::publicReleaseObjSelect($objectID, $object, $form);
			$output .= '<label form="publicReleaseObj">Release to Public:</label>';
			$output .= '<select name="publicReleaseObj" id="publicReleaseObj">';
			$output .= sprintf('<option value="yes" %s>Yes</option>', $objPublicReleaseDefaultTrueYes ? "selected" : "");
			$output .= sprintf('<option value="no" %s>No</option>', !$objPublicReleaseDefaultTrueYes ? "selected" : "");
			$output .= '</select>';
		}
		return $output;
	}
	
	private static function generateFields($fields, $object, $error) {
		$output = '';
		foreach ($fields as $field) {
			if ($field['type'] == "fieldset" || ($field['type'] == "idno" && strtolower($field['managedBy']) == "system" && is_null($object['id']))) {
				continue;
			}
			$output .= self::generateField($field, $object, $error);
		}
		return $output;
	}
	
	private static function generateField($field, $object, $error) {
		$output = '';
		$currentFieldset = '';
	
		if ($field['type'] == "fieldset") {
			// Handle fieldsets
			$currentFieldset = $field['fieldset'];
			if (!isempty($currentFieldset)) {
				$output .= sprintf('<fieldset><legend>%s</legend>', $currentFieldset);
			}
			return $output;
		}
	
		if ($error === TRUE && isset($engine->cleanPost['RAW'][$field['name']])) {
			$object['data'][$field['name']] = $engine->cleanPost['RAW'][$field['name']];
			if ($field['type'] == "select") {
				$field['choicesDefault'] = $engine->cleanPost['RAW'][$field['name']];
			}
		}
	
		$output .= '<div class="formCreator dataEntry">';
	
		// Logic to handle specific field types (textarea, checkbox, select, etc.)
		switch ($field['type']) {
			case 'textarea':
			case 'wysiwyg':
				$output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s %s" %s %s %s %s>%s</textarea>',
					htmlSanitize($field['name']),
					htmlSanitize($field['placeholder']),
					htmlSanitize($field['id']),
					htmlSanitize($field['class']),
					($field['type'] == "wysiwyg" ? "wysiwyg" : ""),
					(!isempty($field['style'])) ? 'style="' . htmlSanitize($field['style']) . '"' : "",
					(strtoupper($field['required']) == "TRUE") ? "required" : "",
					(strtoupper($field['readonly']) == "TRUE") ? "readonly" : "",
					(strtoupper($field['disabled']) == "TRUE") ? "disabled" : "",
					self::getFieldValue($field, (isset($object)) ? $object : NULL)
				);
				if ($field['type'] == "wysiwyg") {
					// Include CKEditor scripts
					$output .= sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%sincludes/js/CKEditor/"</script>',
						localvars::get("siteRoot")
					);
					$output .= sprintf('<script type="text/javascript" src="%sincludes/js/CKEditor/ckeditor.js"></script>',
						localvars::get("siteRoot")
					);
					$output .= '<script type="text/javascript">$(function(){';
					$output .= sprintf('if (CKEDITOR.instances["%s"]){ CKEDITOR.remove(CKEDITOR.instances["%s"]); }',
						htmlSanitize($field['id']),
						htmlSanitize($field['id'])
					);
					$output .= sprintf(' CKEDITOR.replace("%s"); ',
						htmlSanitize($field['id'])
					);
					$output .= '});</script>';
				}
				break;
			case 'checkbox':
			case 'radio':
				// Code to generate checkbox or radio field
				$output .= sprintf('<div data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
					$field['type'],
					$formID,
					htmlSanitize($field['name']),
					(isset($field['choicesForm']) && !isempty($field['choicesForm'])) ? 'data-choicesForm="' . $field['choicesForm'] . '"' : ""
				);
				$output .= self::drawFieldChoices($field, $fieldChoices);
				$output .= '</div>';
				break;
			case 'select':
				// Code to generate select field
				$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					$field['type'],
					$formID,
					htmlSanitize($field['name']),
					(isset($field['choicesForm']) && !isempty($field['choicesForm'])) ? 'data-choicesForm="' . $field['choicesForm'] . '"' : ""
				);
				$output .= self::drawFieldChoices($field, $fieldChoices, (isset($object['data'][$field['name']])) ? $object['data'][$field['name']] : NULL);
				$output .= "</select>";
				break;
			case 'multiselect':
				// Code to generate multiselect field
				$output .= '<div class="multiSelectContainer">';
				$output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
					htmlSanitize($field['name']),
					htmlSanitize(str_replace("/", "_", $field['name']))
				);
				// Logic to populate options in the multiselect field
				$output .= '</select><br />';
				// Additional logic for handling manual choices or AJAX retrieval
				$output .= "</div>";
				break;
			case 'file':
				// Code to generate file upload field
				$output .= '<div style="display: inline-block;">';
				// Logic to display existing file or upload input
				$output .= '</div>';
				break;
			default:
				// Code to generate other input types (text, number, etc.)
				$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s />',
					htmlSanitize($field['type']),
					htmlSanitize($field['name']),
					self::getFieldValue($field, (isset($object)) ? $object : NULL),
					htmlSanitize($field['placeholder']),
					($field['type'] == "number") ? (buildNumberAttributes($field)) : "",
					htmlSanitize($field['id']),
					htmlSanitize($field['class']),
					(strtoupper($field['required']) == "TRUE") ? "required" : "",
					(strtoupper($field['readonly']) == "TRUE") ? "readonly" : "",
					(strtoupper($field['disabled']) == "TRUE") ? "disabled" : ""
				);
				break;
		}
	
		if (isset($field['help']) && $field['help']) {
			// Code to add help icon or link for the field
			list($helpType, $helpValue) = explode('|', $field['help'], 2);
			$helpType = trim($helpType);
			switch ($helpType) {
				case 'text':
					$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-placement="right" data-content="%s"> <i class="fa fa-question-circle"></i> </a>', $helpValue);
					break;
				case 'html':
					$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-html="true" data-placement="right" data-trigger="hover" data-content="%s"><i class="fa fa-question-circle"></i></a>', $helpValue);
					break;
				case 'web':
					$output .= sprintf(' <a class="creatorFormHelp" href="%s" target="_blank"><i class="fa fa-question-circle"></i></a>', $helpValue);
					break;
				default:
					break;
			}
		}
	
		$output .= "</div>";
	
		if (!isempty($currentFieldset)) {
			$output .= "</fieldset>";
		}
	
		return $output;
	}	
	
	private static function generateFormEnd($form, $objectID) {
		$btnValue = (is_null($objectID)) ? htmlSanitize($form["submitButton"]) : htmlSanitize($form["updateButton"]);
		$btnName = ($objectID) ? "updateForm" : "submitForm";
		$output = sprintf('<input type="submit" value="%s" name="%s" id="objectSubmitBtn" class="btn" />', $btnValue, $btnName);
	
		if (!is_null($objectID) && self::isMetadataForm($form['formID'])) {
			$output .= sprintf('<a href="%sdata/metadata/edit/delete/?objectID=%s&formID=%s" id="delete_metadata_link"><i class="fa fa-trash"></i>Delete</a>', localvars::get('siteRoot'), $objectID, $form['formID']);
		}
	
		if (isset($formHasFiles) && $formHasFiles) {
			$output .= '<div class="alert alert-info" id="objectSubmitProcessing"><strong>Processing Files</strong><br>Please Wait... <i class="fa fa-refresh fa-spin fa-2x"></i></div>';
		}
	
		$output .= "</form>";
		return $output;
	}

	private static function hasFieldVariables($value) {
		foreach (self::$fieldVariables as $variable) {
			if(stripos($variable, $value) !== FALSE) {
				return TRUE;
			}
		}

		return FALSE;

	}

	private static function getFieldValue($field,$object) {
		$field['value'] = convertString($field['value']);

		if (self::hasFieldVariables($field['value'])) {
			return htmlSanitize(self::applyFieldVariables($field['value']));
		}

		return isset($object['data'][$field['name']])
			? htmlSanitize(convertString($object['data'][$field['name']]))
			: htmlSanitize(self::applyFieldVariables($field['value']));
	}

	// @TODO it doesnt look like the edit table is honoring form creator choices on
	// which fields are displayed
	//
	// @TODO File uploads should never be displayed on the edit table.
	public static function buildEditTable($formID) {

		$form = self::get($formID);

		// Get all objects from this form
		$objects = objects::getAllObjectsForForm($formID);
		$objects = objects::sort($objects,$form['objectTitleField']);

		if (count($objects) > 0) {

			// @todo -- we are modifying this so that we can scale. Large forms
			// are timing out because browsers are having difficulty submitting
			// 15,000 input fields.
			//
			// We are penalizing smaller forms to make larger forms work. it would
			// be nice to allow smaller forms to be able to use the edit table.

			$headers = array();
			// $headers[] = "Delete";
			$headers[] = "Edit";

			foreach ($form['fields'] as $field) {
				$headers[] = $field['label'];
			}

			if (forms::isMetadataForm($formID) === TRUE) {
				$headers[] = "Search";
				$headers[] = "Move";
			}

			$tableRows = array();
			for($I=0;$I<count($objects);$I++) {

				$temp   = array();
				// $temp[] = sprintf('<input type="checkbox" name="delete[]" value="%s"',
				// 	$objects[$I]['ID']
				// );

				$temp[] = sprintf('<a href="%sdata/metadata/edit/?objectID=%s" target="_window">Edit</a>',
						localvars::get('siteRoot'),
						$objects[$I]['ID']
						);

				foreach ($form['fields'] as $field) {

          $field_value = (isset($objects[$I]['data'][$field['name']]))?htmlSanitize($objects[$I]['data'][$field['name']]):"";
          if ($field['type'] == "select" && !is_empty($field_value)) {
            $field_object = objects::get($field_value);
            $field_value = $field_object['data'][$field['choicesField']];
          }

					$temp[] = sprintf('<input type="%s" style="%s" name="%s_%s" value="%s" readonly />',
						$field['type'],
						$field['style'],
						$field['name'],
						$objects[$I]['ID'],
						$field_value
					);
				}

				if (forms::isMetadataForm($formID) === TRUE) {
					$temp[] = sprintf('<a href="%sdataView/list.php?listType=metadataObjects&amp;formID=%s&amp;objectID=%s">Find Objects</a>',
						localvars::get('siteRoot'),
						htmlSanitize($formID),
						$objects[$I]['ID']
						);
					$temp[] = sprintf('<a href="%sdataEntry/move.php?objectID=%s">Move</a>',
						localvars::get('siteRoot'),
						$objects[$I]['ID']
						);
				}

				$tableRows[] = $temp;
			}


			$formName = preg_replace('/\s+/', '', $form["title"]);
			$table          = new tableObject("array");
			$table->summary = "Object Listing";
			$table->class   = "tableObject table table-striped table-bordered {$formName}";
			$table->headers($headers);

			$output = "";

			$output .= sprintf('<form action="%s?formID=%s" method="%s" name="updateForm" data-formid="%s">',
				$_SERVER['PHP_SELF'],
				htmlSanitize($formID),
				"post",
				mfcs::$engine->openDB->escape($formID)
			);

			$output .= sessionInsertCSRF();

			$output .= $table->display($tableRows);

			// $output .= '<input type="submit" name="updateEdit" value="Update" class="btn" />';
			$output .= "</form>";

			// Add in pagination bar
			if (isset($pagination)) {
				$output .= $pagination->nav_bar();
			}

			return $output;
		}
		else {
			return "No data entered for this Metadata Form.";
		}

	}

	// returns NULL when the other function should continue
	// returns FALSE when something doesn't validate
	// returns TRUE when something does validate
	private static function validateSubmission($formID, $field, $objectID, $value=NULL) {


		if ($field['type'] == "fieldset" || $field['disabled'] == "true") return NULL;

		// If the IDNO is managed by the system, skip it:
		if ($field['type'] == "idno" && $field['managedBy'] != "user") return NULL;

		if (strtolower($field['required']) == "true" && (isnull($value) || !isset($value) || isempty($value))) {
			errorHandle::newError(__METHOD__."() - missing", errorHandle::DEBUG);
			errorHandle::errorMsg("Missing data for required field '".$field['label']."'.");
			return FALSE;

		}

		// Perform validations here
		$valid = TRUE;
		if (!empty($field['format'])) {
			if (strtolower($field['format']) == 'characters' || strtolower($field['format']) == 'digits') {
				if (!empty($field['min']) && $field['min'] > strlen($value)) {
					$valid = FALSE;
				}
				if (!empty($field['max']) && $field['max'] < strlen($value)) {
					$valid = FALSE;
				}
			}
			else if (strtolower($field['format']) == 'words') {
				if (!empty($field['min']) && $field['min'] > str_word_count($value)) {
					$valid = FALSE;
				}
				if (!empty($field['max']) && $field['max'] < str_word_count($value)) {
					$valid = FALSE;
				}
			}
		}

		// Skip if it's already invalid
		if ($valid === TRUE) {
			// No validation to test
			if (isempty($field['validation']) || $field['validation'] == "none") {
				$valid = TRUE;
			}
			// Empty fields that are not required are valid
			else if (!str2bool($field['required']) && is_empty($value)) {
				$valid = TRUE;
			}
			else {
				$valid = FALSE;
				if (validate::isValidMethod($field['validation']) === TRUE) {
					if ($field['validation'] == "regexp") {
						$valid = validate::$field['validation']($field['validationRegex'],$value);
					}
					else {
						$valid = validate::$field['validation']($value);
					}
				}
			}
		}

		if ($valid === FALSE) {
			errorHandle::newError(__METHOD__."() - data", errorHandle::DEBUG);
			errorHandle::errorMsg("Invalid data provided in field '".$field['label']."'.");
			return FALSE;
		}

		// Duplicate Checking (Form)
		if (strtolower($field['duplicates']) == "true") {
			if (self::isDupe($formID,$field['name'],$value,$objectID)) {
				errorHandle::newError(__METHOD__."() - Dupe -- ".$field['name'], errorHandle::DEBUG);
				errorHandle::errorMsg("Duplicate data (in form) provided in field '".$field['label']."'.");
				return FALSE;
			}
		}

		if (!is_empty(mfcs::$engine->errorStack)) {
			return FALSE;
		}
		else {
			return TRUE;
		}

	}

	public static function submitEditTable($formID) {

		$form = self::get($formID);

		if ($form === FALSE) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		// begin transactions
		$result = $engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			errorHandle::errorMsg("Database transactions could not begin.");
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		// Do the Updates
		$objects = objects::getAllObjectsForForm($formID,NULL,FALSE);
		if (count($objects) > 0) {
			foreach ($objects as $object) {

				$values = array();

				foreach ($form['fields'] as $field) {

					// @TODO keep an eye on this with edit tables ... this was added to help the modal inserts
					// from being deleted because the edit table didn't have them listed.
					if (!isset($engine->cleanPost['RAW'][$field['name']."_".$object['ID']])) continue 2;

					$value = (isset($engine->cleanPost['RAW'][$field['name']."_".$object['ID']]))?$engine->cleanPost['RAW'][$field['name']."_".$object['ID']]:$object['data'][$field['name']];
					$validationTests = self::validateSubmission($formID,$field,$object['ID'],$value);

					if (isnull($validationTests) || $validationTests === FALSE) {
						continue 2;
					}

					if (strtolower($field['readonly']) == "true") {
						// need to pull the data that loaded with the form
						if ($newObject === TRUE) {
							// grab it from the database
							$oldObject              = object::get($objectID);
							$values[$field['name']] = $oldObject['data'][$field['name']];
						}
						else {
							// grab the default value from the form.
							$values[$field['name']] = $field['value'];
						}

						continue;
					}

					if(!isset($values[$field['name']])) $values[$field['name']] = $value;

					if (!is_empty($engine->errorStack)) {
						$engine->openDB->transRollback();
						$engine->openDB->transEnd();

						return FALSE;
					}

				}

				// Check to see if the objects data has changed. if it has, update it.
				if (encodeFields($values) != $object['data']) {

					if (objects::update($object['ID'],$formID,$values,$form['metadata'],$object['parentID'],null,$object['publicRelease']) === FALSE) {
						$engine->openDB->transRollback();
						$engine->openDB->transEnd();

						errorHandle::newError(__METHOD__."() - error updating edit table", errorHandle::DEBUG);
						errorHandle::errorMsg("Error updating.");

						return FALSE;
					}


				}

			}
		}

		// do the deletes

		if (isset($engine->cleanPost['MYSQL']['delete']) && count($engine->cleanPost['MYSQL']['delete']) > 0) {
			foreach ($engine->cleanPost['MYSQL']['delete'] as $objectID) {
				$sql       = sprintf("DELETE FROM `objects` WHERE `ID`='%s'",
					$objectID);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::errorMsg("Error deleting objects.");
					errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
					return FALSE;
				}

				// delete from duplicates table
				if (!duplicates::delete($objectID)) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::errorMsg("Error deleting objects.");
					return FALSE;
				}

				$sql       = sprintf("DELETE FROM `objectsData` WHERE `objectID`='%s'",
					$objectID
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::errorMsg("Error deleting objects. Objects Data table.");
					errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
					return FALSE;
				}

			}
		}

		// end transactions
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		return TRUE;

	}

	// NOTE: data is being saved as RAW from the array.
	public static function submit($formID, $objectID = NULL, $importing = FALSE) {
		// Check if the form is a metadata form
		if (!self::isMetadataForm($formID)) {
			return FALSE;
		}
	
		// Check for object updates if not a new object
		if (!$objectID && !locks::check_for_update($objectID, "object")) {
			return FALSE;
		}
	
		// Retrieve the form and handle errors
		$form = self::getForm($formID);
		if ($form === FALSE) {
			return FALSE;
		}
	
		// Sort fields by position
		$fields = self::sortFieldsByPosition($form['fields']);
	
		// Process fields
		$values = self::processFields($fields, $objectID, $importing);
		if ($values === FALSE) {
			return FALSE;
		}
	
		// Begin database transaction
		if (!self::startTransaction("objects")) {
			return FALSE;
		}
	
		// Create or update object
		if (!$objectID) {
			$publicReleaseObj = self::processPublicReleaseObj();
			$result = self::createObject($formID, $values, $form['metadata'], $publicReleaseObj);
		} else {
			$publicReleaseObj = self::processPublicReleaseObj();
			$result = self::updateObject($objectID, $formID, $values, $form['metadata'], $publicReleaseObj);
		}
	
		// Handle errors during object creation/update
		if ($result === FALSE) {
			self::rollbackTransaction();
			return FALSE;
		}
	
		// Insert into processing table
		if (files::insertIntoProcessingTable($objectID) === FALSE) {
			self::rollbackTransaction();
			return FALSE;
		}
	
		// Commit transaction
		self::commitTransaction();
	
		// Process background tasks
		self::processBackgroundTasks($objectID);
	
		// Display success messages
		self::displaySuccessMessage(!$objectID, $importing);
	
		return TRUE;
	}
	
	public static function formsAreCompatible($form1, $form2) {
		// if integers are passed in, grab the forms.
		if (is_numeric($form1)) {
			$form1 = self::get($form1);
		}
		if (is_numeric($form2)) {
			$form2 = self::get($form2);
		}

		// if we don't have forms, return false;
		if (!is_array($form1) || !is_array($form2)) {
			return false;
		}

		if (count($form1['fields']) != count($form2['fields'])) {
			return false;
		}

		$compatibleFields = [];
		foreach ($form1['fields'] as $field) {
			foreach ($form2['fields'] as $field2) {
				if ($field['type'] == $field2['type'] && $field['name'] == $field2['name']) {
					$compatibleFields[] = $field;
				}
			}
		}

		return count($compatibleFields) == count($form1['fields']);
	}

	public static function compatibleForms($formID) {
		$form = self::get($formID);
		if ($form === FALSE) {
			return FALSE;
		}

		$allForms = self::getForms(NULL, TRUE);
		$compatibleForms = [];

		foreach ($allForms as $fid => $f) {
			if ($f === FALSE || $f['ID'] === $form['ID']) {
				continue;
			}

			if (self::formsAreCompatible($form, $f)) {
				$compatibleForms[$f['ID']] = $f;
			}
		}

		return $compatibleForms;
	}

	public static function delete($formID){
		$engine = mfcs::$engine;

		// Start the transaction
		$engine->openDB->transBegin();

		try{
			// Delete forms_projects
			$sql = sprintf("DELETE FROM `forms_projects` WHERE `formID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) {
				throw new Exception("Failed to delete from forms_projects ({$sqlResult['error']})");
			}

			// Delete objects
			$sql = sprintf("DELETE FROM `objects` WHERE `formID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) {
				throw new Exception("Failed to delete from objects ({$sqlResult['error']})");
			}

			// Delete forms
			$sql = sprintf("DELETE FROM `forms` WHERE `ID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) {
				throw new Exception("Failed to delete from forms ({$sqlResult['error']})");
			}

			// If we get here then all went well
			$engine->openDB->transCommit();
			$engine->openDB->transEnd();
			return TRUE;
		} catch(Exception $e) {
			// Something went wrong - Report the error and rollback the transaction
			errorHandle::newError(__METHOD__."() - ".$e->getMessage(), errorHandle::HIGH);
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			return FALSE;
		}
	}

	// $value must be RAW
	public static function isDupe($formID,$field,$value,$objectID=NULL) {
		return duplicates::isDupe($formID,$field,$value,$objectID);
	}

	public static function getProjects($formID) {
		$engine = EngineAPI::singleton();
		$return = [];

		$sql = sprintf("SELECT `projectID` FROM `forms_projects` WHERE formID='%s'",
			$engine->openDB->escape($formID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return [];
		}

		while ($row = mysqli_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$return[] = $row['projectID'];
		}

		return $return;
	}

	public static function deleteAllProjects($formID) {
		$engine = EngineAPI::singleton();
		$sql = "DELETE FROM `forms_projects` WHERE `formID`=:formID";
		$params = array(
			':formID' => $formID
		);
		$sqlResult = $engine->openDB->query($sql, $params);
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		return TRUE;
	}

	public static function addProject($formID, $projectID) {
		$engine = EngineAPI::singleton();
		$sql = "INSERT INTO `forms_projects` (`formID`,`projectID`) VALUES(:formID, :projectID)";
		$params = array(
			':formID' => $formID,
			':projectID' => $projectID
		);
		$sqlResult = $engine->openDB->query($sql, $params);
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		return TRUE;
	}

	public static function addProjects($formID, $projects) {
		if (!is_array($projects)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		$result = $engine->openDB->transBegin("objectProjects");

		foreach ($projects as $projectID) {
			if (self::addProject($formID, $projectID) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				return FALSE;
			}
		}

		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		return TRUE;
	}

	public static function applyFieldVariables($formatString){
		$formatString = str_ireplace(
			array('%userid%', '%username%', '%firstname%', '%lastname%'),
			array(users::user('ID'), users::user('username'), users::user('firstname'), users::user('lastname')),
			$formatString
		);
		$formatString = str_ireplace(
			array('%date%', '%time%', '%time12%', '%time24%', '%timestamp%'),
			array(date('Y-m-d'), date('H:i:s'), date('g:i:s A'), date('H:i:s'), time()),
			$formatString
		);
		$formatString = preg_replace_callback('/%date\((.+?)\)%/i', function($matches){
			return date($matches[1]);
		}, $formatString);

		return $formatString;
	}

	public static function retrieveData($formID, $fieldName=NULL) {
		$sql = sprintf("SELECT * FROM `objectsData` WHERE `formID`='%s'",
			mfcs::$engine->openDB->escape($formID)
		);

		if (!is_null($fieldName)) {
			$sql .= " AND `fieldName`='".mfcs::$engine->openDB->escape($fieldName)."'";
		}

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$data = array();

		while($row = mysqli_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			if (!is_null($fieldName) && $row['fieldName'] != $fieldName) {
				continue;
			}

			if ($row['encoded'] == "1") {
				$row['value'] = unserialize(base64_decode($row['value']));
			}
			$data[] = $row;
		}

		return $data;
	}

	// form is an already retrieved object.
	// @TODO, can we just do this in the "get" method? or will that break something?
	// unit testing fail.
	public static function associate_fields($form) {
		$form['fields'] = self::rebuild_form_fields($form['fields']);
		return $form;
	}

	// This returns an array with the same fields, in the same order, but with the
	// field name as the index to the field.
	public static function rebuild_form_fields($fields) {
		$new_fields = [];

		foreach ($fields as $field) {
			$new_fields[$field['name']] = $field;
		}

		return $new_fields;
	}

}
?>
