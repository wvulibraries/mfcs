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

		$form           = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

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
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

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

			$result = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
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

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

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

	public static function build($formID,$objectID = NULL,$error=FALSE) {

		$engine = EngineAPI::singleton();

		// Get the current Form
		$form   = self::get($formID);

		if ($form === FALSE) {
			return FALSE;
		}

		$fields = $form['fields'];

		if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
			errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if (!isnull($objectID)) {
			$object = objects::get($objectID,TRUE);
			if ($object === FALSE) {
				errorHandle::errorMsg("Error retrieving object.");
				return FALSE;
			}
		}
		else if (isnull($objectID) && $error === TRUE) {
			$object         = array();
			$object['data'] = array();
		}

		$output = sprintf('<form action="%s?formID=%s%s" method="%s" name="insertForm" data-formid="%s">',
			$_SERVER['PHP_SELF'],
			htmlSanitize($formID),
			(!isnull($objectID)) ? '&objectID='.$objectID : "",
			"post",
			mfcs::$engine->openDB->escape($formID)
		);

		$output .= sessionInsertCSRF();

		if (isset($engine->cleanGet['HTML']['parentID'])) {
			$output .= sprintf('<input type="hidden" name="parentID" value="%s">',
				$engine->cleanGet['HTML']['parentID']
				);
		}

		// If there is a Lock ID add it to the form
		if (!isempty(localvars::get("lockID"))) {
			$output .= sprintf('<input type="hidden" name="lockID" value="%s">',
				localvars::get("lockID")
				);
		}

		if ($form['objPublicReleaseShow'] == 1 && $form['metadata'] == 0) {
			$objPublicReleaseDefaultTrueYes = forms::publicReleaseObjSelect($objectID,$object,$form);
			$output .= '<label form="publicReleaseObj">Release to Public:</label>';
			$output .= '<select name="publicReleaseObj" id="publicReleaseObj">';
			$output .= sprintf('<option value="yes" %s>Yes</option>', $objPublicReleaseDefaultTrueYes ? "selected" : "");
			$output .= sprintf('<option value="no" %s>No</option>', !$objPublicReleaseDefaultTrueYes ? "selected" : "");
			$output .= '</select>';
		}

		$currentFieldset = "";

		foreach ($fields as $field) {

			if ($field['type'] == "fieldset") {
				continue;
			}
			if ($field['type'] == "idno" && (strtolower($field['managedBy']) == "system" && isnull($objectID))) {
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


			if ($error === TRUE) {
				// This is RAW because it is post data being displayed back out to the user who submitted it
				// during a submission error. we don't want to corrupt the data by sanitizing it and then
				// sanitizing it again on submissions
				//
				// it should not be a security issue because it is being displayed back out to the user that is submissing the data.
				// this will likely cause issues with security scans
				//
				// @SECURITY False Positive 1
				if (isset($engine->cleanPost['RAW'][$field['name']])) {
					$object['data'][$field['name']] = $engine->cleanPost['RAW'][$field['name']];
					if ($field['type'] == "select") {
						$field['choicesDefault'] = $engine->cleanPost['RAW'][$field['name']];
					}
				}
			}

			// build the actual input box

			$output .= '<div class="formCreator dataEntry">';


			// Handle disabled on insert form
			if (isset($field['disabledInsert']) && $field['disabledInsert'] == "true" && isnull($objectID)) {
				$field['disabled'] = "true";
			}

			// Handle Read Only on Update form
			if (isset($field['disabledUpdate']) &&  $field['disabledUpdate'] == "true" && !isnull($objectID)) {
				$field['readonly'] = "true";
			}

			// @TODO There is excessive logic here. We have already continued/skipped passed IDNOs that we aren't displaying at this point.
			// version 2.0 cleanup.
			if ($field['type'] != "idno"
				|| ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) != "system")
				|| ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) == "system" && !isnull($objectID))
				) {
				$output .= sprintf('<label for="%s" class="formLabel %s">%s:</label>',
					htmlSanitize($field['id']),
					(strtolower($field['required']) == "true")?"requiredField":"",
					htmlSanitize($field['label'])
				);
			}

			if ($field['type'] == "textarea" || $field['type'] == "wysiwyg") {
				$output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s %s" %s %s %s %s>%s</textarea>',
					htmlSanitize($field['name']),
					htmlSanitize($field['placeholder']),
					htmlSanitize($field['id']),
					htmlSanitize($field['class']),
					($field['type'] == "wysiwyg" ? "wysiwyg" : ""),
					(!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
					//true/false type attributes
					(strtoupper($field['required']) == "TRUE")?"required":"",
					(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
					(strtoupper($field['disabled']) == "TRUE")?"disabled":"",
					self::getFieldValue($field,(isset($object))?$object:NULL)
				);

				if ($field['type'] == "wysiwyg") {
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

					$output .= 'htmlParser = "";';
					$output .= '';
					$output .= sprintf('if(CKEDITOR.instances["%s"].dataProcessor){ CKEDITOR.instances["%s"].dataProcessor.htmlFilter;}',
						$field['name'],
						htmlSanitize($field['id'])
					);

					$output .= '});</script>';

				}

			}
			else if ($field['type'] == "checkbox" || $field['type'] == "radio") {

				if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
					return FALSE;
				}

				$output .= sprintf('<div data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
					$field['type'],
					$formID,
					htmlSanitize($field['name']),
					(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
				);


				$output .= self::drawFieldChoices($field,$fieldChoices);

				$output .= '</div>';

			}
			else if ($field['type'] == "select") {

				if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
					return FALSE;
				}

				$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					$field['type'],
					$formID,
					htmlSanitize($field['name']),
					(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
				);

				$output .= self::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL);

				$output .= "</select>";

			}
			// else if ($field['type'] == "select") {

			// 	if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
			// 		if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
			// 			return FALSE;
			// 		}

			// 		$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>%s</select>',
			// 			htmlSanitize($field['name']),
			// 			htmlSanitize($field['name']),
			// 			$field['type'],
			// 			$formID,
			// 			htmlSanitize($field['name']),
			// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
			// 			self::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL)
			// 		);
			// 	}
			// 	else {
			// 		$output .= sprintf('<input type="hidden" name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
			// 			htmlSanitize($field['name']),
			// 			htmlSanitize($field['name']),
			// 			$field['type'],
			// 			$formID,
			// 			htmlSanitize($field['name']),
			// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
			// 			htmlSanitize($field['name'])
			// 		);

			// 		$output .= sprintf("<script charset=\"utf-8\">
			// 				$(function() {
			// 					$('#%s')
			// 						.select2({
			// 							minimumResultsForSearch: 10,
			// 							placeholder: 'Make a Selection',
			// 							ajax: {
			// 								url: 'retrieveOptions.php',
			// 								dataType: 'json',
			// 								quietMillis: 300,
			// 								data: function(term, page) {
			// 									return {
			// 										q: term,
			// 										page: page,
			// 										pageSize: 1000,
			// 										formID: '%s',
			// 										fieldName: '%s'
			// 									};
			// 								},
			// 								results: function(data, page) {
			// 									var more = (page * data.pageSize) < data.total;

			// 									return {
			// 										results: data.options,
			// 										more: more
			// 									};
			// 								},
			// 							},
			// 							// initSelection: function(element, callback) {

			// 					  //           var id = $(element).val();
			// 					  //           if(id !== '') {
			// 					  //           	$.ajax('retrieveSingleOption.php', {
			// 					  //           		data: function() {
			// 					  //           			return {
			// 					  //           				formID: '%s',
			// 					  //           				id: id
			// 					  //           			};
			// 					  //           		},
			// 					  //                   dataType: 'json'
			// 					  //               }).done(function(data) {
			// 					  //                   callback(data.results[0]);
			// 					  //               });
			// 					  //           }
			// 					  //       }
			// 						});
			// 					// $('#%s').select2( 'val', '%s' );
			// 				});

			// 			</script>",
			// 			htmlSanitize($field['name']),
			// 			htmlSanitize($field['choicesForm']),
			// 			htmlSanitize($field['choicesField']),
			// 			$object['data'][$field['name']]
			// 		);
			// 	}

			// }
			else if ($field['type'] == 'multiselect') {


				$output .= '<div class="multiSelectContainer">';
				$output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
					htmlSanitize($field['name']),
					htmlSanitize(str_replace("/","_",$field['name']))
				);

				if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {

					foreach ($object['data'][$field['name']] as $selectedItem) {
						$tmpObj  = objects::get($selectedItem, true);
						$output .= sprintf('<option value="%s">%s</option>',
							htmlSanitize($selectedItem),
							htmlSanitize($tmpObj['data'][$field['choicesField']])
						);

						// if the temp object is false then we have a problem
						// if($tmpObj === false){
						// 	errorHandle::newError("Can't get Object for Metadata Object", errorHandle::DEBUG);
						// }
					}
				}

				$output .= '</select><br />';

				if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
					if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
						return FALSE;
					}

					$output .= sprintf('<select name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s onchange="addItemToID(\'%s\', this.options[this.selectedIndex]);">%s</select>',
						htmlSanitize(str_replace("/","_",$field['name'])),
						htmlSanitize($field['name']),
						$field['type'],
						$formID,
						htmlSanitize($field['name']),
						(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
						htmlSanitize(str_replace("/","_",$field['name'])),
						self::drawFieldChoices($field,$fieldChoices)
					);
				}
				else {
					$output .= sprintf('<input type="hidden" name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
						htmlSanitize($field['name']),
						htmlSanitize(str_replace("/","_",$field['name'])),
						$field['type'],
						$formID,
						htmlSanitize($field['name']),
						(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
						htmlSanitize($field['name'])
					);

					$output .= sprintf("<script charset=\"utf-8\">
							$(function() {
								$('#%s_available')
									.select2({
										minimumResultsForSearch: 10,
										placeholder: 'Make a Selection',
										ajax: {
											url: 'retrieveOptions.php',
											dataType: 'json',
											quietMillis: 300,
											async: true,
											data: function(term, page) {
												return {
													q: term,
													page: page,
													pageSize: 1000,
													formID: '%s',
													fieldName: '%s'
												};
											},
											results: function(data, page) {
												var more = (page * data.pageSize) < data.total;
												return {
													results: data.options,
													more: more
												};
											},
										},
									})
									.on('select2-selecting', function(e) {
										addToID('%s', e.val, e.choice.text);
										console.log(%s);
									});
							});
						</script>",
						htmlSanitize(str_replace("/","_",$field['name'])),
						htmlSanitize($field['choicesForm']),
						htmlSanitize($field['choicesField']),
						htmlSanitize(str_replace("/","_",$field['name'])),
						htmlSanitize(str_replace("/","_",$field['name']))
					);
				}

				$output .= "<br />";
				$output .= sprintf('<button type="button" onclick="removeFromList(\'%s\')" class="btn">Remove Selected</button>',
					htmlSanitize(htmlSanitize(str_replace("/","_",$field['name'])))
					);

				$output .= "</div>";
			}
			else if ($field['type'] == 'file') {
				$formHasFiles = true;
				$output .= '<div style="display: inline-block;">';
				if(!isnull($objectID)){
					$output .= empty($object['data'][ $field['name'] ])
						? '<span style="color: #666;font-style: italic;">No file uploaded</span><br>'
						: '<a href="javascript:;" onclick="$(\'#filesTab\').click();">Click to view files tab</a><br>';
				}
				$uploadID = md5($field['name'].mt_rand());
				$output .= sprintf('<div class="fineUploader" data-multiple="%s" data-upload_id="%s" data-allowed_extensions="%s" style="display: inline-block;"></div><input type="hidden" name="%s" value="%s">',
					htmlSanitize($field['multipleFiles']),
					$uploadID,
					htmlSanitize(implode(',',$field['allowedExtensions'])),
					htmlSanitize($field['name']),
					$uploadID);
				$output .= '</div>';
			}
			else {

				// populate the idno field
				if ($field['type'] == "idno") {
					$field['type'] = "text";
					if (isset($object) && !isset($object['data'][$field['name']])) $object['data'][$field['name']] = $object['idno'];

					// the IDNO is managed by the user. It shouldn't be set to read only
					if (isset($field['managedBy']) && strtolower($field['managedBy']) != "system") {
						$field['readonly'] = "false";
					}
					else {
						// just in case ...
						$field['readonly'] = "true";
					}

				}

				// get the field value, if the object exists
				$fieldValue = self::getFieldValue($field,(isset($object))?$object:NULL);


				$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s />',
					htmlSanitize($field['type']),
					htmlSanitize($field['name']),
					$fieldValue,
					htmlSanitize($field['placeholder']),
					//for numbers
					($field['type'] == "number")?(buildNumberAttributes($field)):"",
					htmlSanitize($field['id']),
					htmlSanitize($field['class']),
					// (!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
					//true/false type attributes
					(strtoupper($field['required']) == "TRUE")?"required":"",
					(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
					(strtoupper($field['disabled']) == "TRUE")?"disabled":""
				);
			}

			if(isset($field['help']) && $field['help']){

				list($helpType,$helpValue) = explode('|', $field['help'], 2);
				$helpType = trim($helpType);

				switch($helpType){
					case 'text':
						$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-placement="right" data-content="%s"> <i class="fa fa-question-circle"></i> </a>', $helpValue);
						break;
					case 'html':
						$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-html="true" data-placement="right" data-trigger="hover" data-content="%s"><i class="fa fa-question-circle"></i></a>', $helpValue);
						break;
					case 'web':
						$output .= sprintf(' <a class="creatorFormHelp" href="%s" target="_blank" style="target-new: tab;"> <i class="fa fa-question-circle"></i> </a>', $helpValue);
						// $output .= sprintf(' <a href="javascript:;" title="Click for help" class="icon-question-sign" onclick="$(\'#helpModal_%s\').modal(\'show\');"></a>', $field['id']);
						// $output .= sprintf('<div id="helpModal_%s" rel="modal" class="modal hide fade" data-show="false">', $field['id']);
						// $output .= '	<div class="modal-header">';
						// $output .= '		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
						// $output .= '		<h3 id="myModalLabel">Field Help</h3>';
						// $output .= '	</div>';
						// $output .= '	<div class="modal-body">';
						// $output .= sprintf('		<iframe src="%s" seamless="seamless" style="width: 100%%; height: 100%%;"></iframe>', $helpValue);
						// $output .= '	</div>';
						// $output .= '</div>';
						break;
				}
			}

			$output .= "</div>";
		}

		if (!isempty($currentFieldset)) {
			$output .= "</fieldset>";
		}

		$output .= sprintf('<input type="submit" value="%s" name="%s" id="objectSubmitBtn" class="btn" />',
			(isnull($objectID))?htmlSanitize($form["submitButton"]):htmlSanitize($form["updateButton"]),
			$objectID ? "updateForm" : "submitForm"
		);

		// Display a delete link on updates to metadate forms
		if (!isnull($objectID) && self::isMetadataForm($formID)) {
			$output .= sprintf('<a href="%sdata/metadata/edit/delete/?objectID=%s&formID=%s" id="delete_metadata_link"><i class="fa fa-trash"></i>Delete</a>',
				localvars::get('siteRoot'),
				$objectID,
				$formID
				);
		}

		if(isset($formHasFiles) and $formHasFiles){
			$output .= '<div class="alert alert-info" id="objectSubmitProcessing">
							<strong>Processing Files</strong>

							<br>Please Wait... <i class="fa fa-refresh fa-spin fa-2x"></i>
						</div>';
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
	private static function validateSubmission($formID,$field,$value=NULL,$objectID) {


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
					$validationTests = self::validateSubmission($formID,$field,$value,$object['ID']);

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
	public static function submit($formID,$objectID=NULL,$importing=FALSE) {

		$engine               = mfcs::$engine;
		$backgroundProcessing = array();

		if (isnull($objectID)) {
			$newObject = TRUE;
		}
		else {
			$newObject = FALSE;
		}

		// Check the Lock, if this is an update
		// we don't lock metadata updates.
		if ($newObject === FALSE && !self::isMetadataForm($formID)) {

			if (!locks::check_for_update($objectID,"object")) {
				return FALSE;
			}

		}

		// Get the current Form
		if (($form = self::get($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
			return FALSE;
		}

		// the form is an object form, make sure that it has an ID field defined.
		// @TODO this check can probably be removed, its being checked in object class
		if ($form['metadata'] == "0") {
			$idnoInfo = self::getFormIDInfo($formID);
			if ($idnoInfo === FALSE) {
				errorHandle::newError(__METHOD__."() - no IDNO field for object form.", errorHandle::DEBUG);
				return FALSE;
			}
		}

		$fields = $form['fields'];

		if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
			errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
			if (!$importing) errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		$values = array();

		if (isset($engine->cleanPost['RAW']['publicReleaseObj'])) {
			$publicReleaseObj = strtolower($engine->cleanPost['RAW']['publicReleaseObj']) == "no" ? 0 : 1;
		} else {
			// create the public release object as a default
			$publicReleaseObj = 1;
		}

		// go through all the fields, get their values
		foreach ($fields as $field) {

			$value           = (isset($engine->cleanPost['RAW'][$field['name']]))?$engine->cleanPost['RAW'][$field['name']]:"";
			$validationTests = self::validateSubmission($formID,$field,$value,$objectID);

			if (isnull($validationTests) || $validationTests === FALSE) {
				continue;
			}

			if (strtolower($field['readonly']) == "true") {
				// need to pull the data that loaded with the form
				if ($newObject === FALSE) {
					// grab it from the database
					$oldObject              = objects::get($objectID);
					$values[$field['name']] = $oldObject['data'][$field['name']];
				}
				else {
					// If the form has a variable in the value we apply the variable, otherwise, field value.
					// we need to check for disabled on insert form
					if (!isset($field['disabledInsert']) || (isset($field['disabledInsert']) && $field['disabledInsert'] == "false")) {
						$values[$field['name']] = (self::hasFieldVariables($field['value']))?self::applyFieldVariables($value):$field['value'];
					}
					// grab the default value from the form.
					// $values[$field['name']] = $field['value'];
				}
			}
			else if (strtolower($field['type']) == "file" && isset($engine->cleanPost['MYSQL'][$field['name']])) {

				// Process uploaded files
				$uploadID = $engine->cleanPost['MYSQL'][$field['name']];

				// Process the uploads and put them into their archival locations
				if (($tmpArray = files::processObjectUploads($objectID, $uploadID)) === FALSE) {
					errorHandle::newError(__METHOD__."() - Archival Location", errorHandle::DEBUG);
					return FALSE;
				}

				if ($tmpArray !== TRUE) {

					// didn't generate a proper uuid for the items, rollback
					if (!isset($tmpArray['uuid'])) {
						$engine->openDB->transRollback();
						$engine->openDB->transEnd();
						errorHandle::newError(__METHOD__."() - No UUID", errorHandle::DEBUG);
						return FALSE;
					}

					// ads this field to the files object
					// we can't do inserts yet because we don't have the objectID on
					// new objects
					files::addProcessingField($field['name']);

					// Should the files be processed now or later?
					if (isset($field['bgProcessing']) && str2bool($field['bgProcessing']) === TRUE) {
						$backgroundProcessing[$field['name']] = TRUE;
					}
					else {
						$backgroundProcessing[$field['name']] = FALSE;
					}

					$values[$field['name']] = $tmpArray;
				}
				else {
					// if we don't have files, and this is an update, we need to pull the files information from the
					// version that is already in the system.
					if ($newObject === FALSE) {
						$oldObject = objects::get($objectID);

						if (objects::hasFiles($objectID,$field['name']) === TRUE) {
							$values[$field['name']] = $oldObject['data'][$field['name']];
						}
					}
				}

			}
			else {
				$values[$field['name']] = $value;
			}
		}

		if (isset($engine->errorStack['error']) && count($engine->errorStack['error']) > 0) {
			// errorHandle::newError(__METHOD__."() - Error stack not empty.", errorHandle::DEBUG);
			return FALSE;
		}

		// start transactions
		$result = $engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			if (!$importing) errorHandle::errorMsg("Database transactions could not begin.");
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		if ($newObject === TRUE) {

			if (objects::create($formID,$values,$form['metadata'],isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",null,null,$publicReleaseObj) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				if (!$importing) errorHandle::errorMsg("Error inserting new object.");
				errorHandle::newError(__METHOD__."() - Error inserting new object.", errorHandle::DEBUG);

				return FALSE;
			}

			// Grab the objectID of the new object
			$objectID = localvars::get("newObjectID");

		}
		else {

			if (objects::update($objectID,$formID,$values,$form['metadata'],isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",null,$publicReleaseObj) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();


				if (!$importing) errorHandle::errorMsg("Error updating.");
				errorHandle::newError(__METHOD__."() - Error updating.", errorHandle::DEBUG);

				return FALSE;
			}

		}

		// Now that we have a valid objectID, we insert into the processing table
		if (files::insertIntoProcessingTable($objectID) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - Processing Table", errorHandle::DEBUG);

				return FALSE;
		}

		// end transactions
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		if (!is_empty($backgroundProcessing)) {
			foreach ($backgroundProcessing as $fieldName=>$V) {

				// insert into the virusChecks table
				if (virus::insert_into_table($objectID,$fieldName) === FALSE) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::newError(__METHOD__."() - Virus checks table", errorHandle::DEBUG);

					return FALSE;
				}

				if ($V === FALSE) {
					// No background processing. do it now.
					files::process($objectID,$fieldName);
				}
			}
		}

		if ($newObject === TRUE) {
			if (!$importing) errorHandle::successMsg("Object created successfully.");
		}
		else {
			if (!$importing) errorHandle::successMsg("Object updated successfully.");
		}

		return TRUE;
	}

	public static function formsAreCompatible($form1,$form2) {

		// if intefers are passed in, grab the forms.
		if (is_numeric($form1)) $form1 = self::get($form1);
		if (is_numeric($form2)) $form2 = self::get($form2);

		// if we don't have forms, return false;
		if (!is_array($form1) || !is_array($form2)) return FALSE;

		if (count($form1['fields']) != count($form2['fields'])) return FALSE;

		//@TODO there has to be a better way of doing this
		$compatibleFields = array();
		foreach ($form1['fields'] as $field) {
			foreach ($form2['fields'] as $field2) {
				if (
					$field['type']            == $field2['type']            &&
					$field['name']            == $field2['name']
					) {

					array_push($compatibleFields,$field);

				}

			}
		}

		if (count($compatibleFields) == count($form1['fields'])) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	public static function compatibleForms($formID) {

		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		$allForms = self::getForms(NULL,TRUE);
		$forms    = array();

		foreach ($allForms as $fid=>$f) {

			if ($f       === FALSE)       continue;
			if ($f['ID'] ==  $form['ID']) continue;

			if (self::formsAreCompatible($form,$f)) $forms[$f['ID']] = $f;

		}

		return $forms;

	}

	public static function delete($formID){
		$engine = mfcs::$engine;

		// Start the transaction
		$engine->openDB->transBegin();

		try{
			// Delete forms_projects
			$sql       = sprintf("DELETE FROM `forms_projects` WHERE `formID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("Failed to delete from forms_projects ({$sqlResult['error']})");

			// Delete objects
			$sql       = sprintf("DELETE FROM `objects` WHERE `formID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("Failed to delete from objects ({$sqlResult['error']})");

			// Delete forms
			$sql       = sprintf("DELETE FROM `forms` WHERE `ID`='%s'", $engine->openDB->escape($formID));
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("Failed to delete from forms ({$sqlResult['error']})");

			// If we get here then all went well
			$engine->openDB->transCommit();
			$engine->openDB->transEnd();
			return TRUE;
		}catch(Exception $e){
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
		$return = array();

		$sql = sprintf("SELECT `projectID` FROM `forms_projects` WHERE formID='%s'",
			$engine->openDB->escape($formID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return array();
		}

		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$return[] = $row['projectID'];
		}

		return $return;
	}

	public static function deleteAllProjects($formID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("DELETE FROM `forms_projects` WHERE `formID`='%s'",
			$engine->openDB->escape($formID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function addProject($formID,$projectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("INSERT INTO `forms_projects` (`formID`,`projectID`) VALUES('%s','%s')",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($projectID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function addProjects($formID,$projects) {

		if (!is_array($projects)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		$result = $engine->openDB->transBegin("objectProjects");

		foreach ($projects as $projectID) {
			if (self::addProject($formID,$projectID) === FALSE) {
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
		// Process user variables
		if(stripos($formatString, '%userid%') !== FALSE)    $formatString = str_ireplace('%userid%', users::user('ID'), $formatString);
		if(stripos($formatString, '%username%') !== FALSE)  $formatString = str_ireplace('%username%', users::user('username'), $formatString);
		if(stripos($formatString, '%firstname%') !== FALSE) $formatString = str_ireplace('%firstname%', users::user('firstname'), $formatString);
		if(stripos($formatString, '%lastname%') !== FALSE)  $formatString = str_ireplace('%lastname%', users::user('lastname'), $formatString);
		// Process static (no custom format) date/time variables
		if(stripos($formatString, '%date%') !== FALSE)      $formatString = str_ireplace('%date%', date('Y-m-d'), $formatString);
		if(stripos($formatString, '%time%') !== FALSE)      $formatString = str_ireplace('%time%', date('H:i:s'), $formatString);
		if(stripos($formatString, '%time12%') !== FALSE)    $formatString = str_ireplace('%time12%', date('g:i:s A'), $formatString);
		if(stripos($formatString, '%time24%') !== FALSE)    $formatString = str_ireplace('%time24%', date('H:i:s'), $formatString);
		if(stripos($formatString, '%timestamp%') !== FALSE) $formatString = str_ireplace('%timestamp%', time(), $formatString);
		// Process custom date/time variables
		$formatString = preg_replace_callback('/%date\((.+?)\)%/i', function($matches){
			return date($matches[1]);
		}, $formatString);

		// And, return the result
		return $formatString;
	}

	public static function retrieveData($formID, $fieldName=NULL) {
		$sql = sprintf("SELECT * FROM `objectsData` WHERE `formID`='%s'",
			mfcs::$engine->openDB->escape($formID)
			);

		if (!isnull($fieldName)) {
			$sql .= "AND `fieldName`='".mfcs::$engine->openDB->escape($fieldName)."'";
		}

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$data = array();

		while($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			if (!isnull($fieldName) && $row['fieldName'] != $fieldName) {
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

		if (!is_array($fields)) {
			return false;
		}

		$new_fields = array();

		foreach ($fields as $field) {
			$new_fields[$field['name']] = $field;
		}

		return $new_fields;
	}

}
?>
