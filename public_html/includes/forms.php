<?php

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

		if (($form['fields'] = decodeFields($form['fields'])) === FALSE) {
			errorHandle::newError(__METHOD__."() - fields", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if (($form['idno'] = decodeFields($form['idno'])) === FALSE) {
			errorHandle::newError(__METHOD__."() - idno", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		if (!isempty($form['navigation']) && ($form['navigation'] = decodeFields($form['navigation'])) === FALSE) {
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

		switch ($type) {
			case TRUE:
				$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='0' ORDER BY `title`");
				break;
			case FALSE:
				$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='1' ORDER BY `title`");
				break;
			case NULL:
				$sql = sprintf("SELECT `ID` FROM `forms` ORDER BY `title`");
				break;
			default:
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

		return htmlSanitize(!empty($form['displayTitle']) ? $form['displayTitle'] : (!empty($form['title']) ? $form['title'] : '[No form title]'));

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

	public static function checkFormInProject($projectID,$formID) {

		$projectForms = projects::getForms($projectID);
		if (in_array($formID, projects::getForms($projectID))) {
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

		foreach (sessionGet('currentProject') as $projectID=>$project) {

			if (self::checkFormInProject($projectID,$formID) === TRUE) {
				return TRUE;
			}

		}

		localVars::add("projectWarning",'<div class="alert">This form is not associated with one of your current projects</div>');

		return FALSE;
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
		}else{
			return $form['fields'];
		}
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

		if (isnull($value) && isset($field['choicesDefault']) && !isempty($field['choicesDefault'])) {
			$value = $field['choicesDefault'];
		}

		$output = "";

		if(isset($field['choicesNull']) && str2bool($field['choicesNull'])){
			$output .= '<option value="">Make a selection</option>';
		}
		foreach ($fieldChoices as $choice) {
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

		// $output .= sprintf('<header><h1>%s</h1><h2>%s</h2></header>',
		// 	htmlSanitize($form['title']),
		// 	htmlSanitize($form['description']));

		$currentFieldset = "";

		foreach ($fields as $field) {

			if ($field['type'] == "fieldset") {
				continue;
			}
			if ($field['type'] == "idno" && strtolower($field['managedBy']) == "system") {
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

			$output .= '<div class="">';


			// Handle disabled on insert form
			if ($field['disabledInsert'] == "true" && isnull($objectID)) {
				$field['disabled'] = "true";
			}

			// Handle Read Only on Update form
			if ($field['disabledUpdate'] == "true" && !isnull($objectID)) {
				$field['readonly'] = "true";
			}

			if ($field['type'] != "idno" || ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) != "system")) {
				$output .= sprintf('<label for="%s" class="formLabel %s">%s:</label>',
					htmlSanitize($field['id']),
					(strtolower($field['required']) == "true")?"requiredField":"",
					htmlSanitize($field['label'])
				);
			}

			if ($field['type'] == "textarea" || $field['type'] == "wysiwyg") {
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
					self::getFieldValue($field,$object);
				);

				if ($field['type'] == "wysiwyg") {
					$output .= sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%sincludes/js/CKEditor/"</script>',
						localvars::get("siteRoot")
					);
					$output .= sprintf('<script type="text/javascript" src="%sincludes/js/CKEditor/ckeditor.js"></script>',
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
					$output .= 'if (CKEDITOR.instances["'.$field['name'].'"].dataProcessor) {';
					$output .= sprintf('    htmlParser = CKEDITOR.instances["%s"].dataProcessor.htmlFilter;',
						htmlSanitize($field['id'])
					);
					$output .= '}';

					$output .= '</script>';
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
			else if ($field['type'] == 'multiselect') {

				if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
					return FALSE;
				}

				$output .= '<div class="multiSelectContainer">';
				$output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
					htmlSanitize($field['name']),
					htmlSanitize($field['name'])
				);

				if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {
					foreach ($object['data'][$field['name']] as $selectedItem) {
						$tmpObj  = objects::get($selectedItem);
						$output .= sprintf('<option value="%s">%s</option>',
							htmlSanitize($selectedItem),
							htmlSanitize($tmpObj['data'][$field['choicesField']])
						);
					}
				}
				else if (isset($object['data'][$field['name']])) {
					// print "<pre>";
					// var_dump($object['data'][$field['name']]);
					// print "</pre>";
				}
				$output .= '</select><br />';
				$output .= sprintf('<select name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s onchange="addItemToID(\'%s\', this.options[this.selectedIndex]);">',
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					$field['type'],
					$formID,
					htmlSanitize($field['name']),
					(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
					htmlSanitize($field['name'])
				);

				$output .= self::drawFieldChoices($field,$fieldChoices);

				$output .= '</select>';
				$output .= "<br />";
				$output .= sprintf('<button type="button" onclick="removeFromList(\'%s\')" class="btn">Remove Selected</button>',
					htmlSanitize($field['name'])
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

				if ($field['type'] == "idno") {
					$field['type'] = "text";
					if (isset($object) && !isset($object['data'][$field['name']])) $object['data'][$field['name']] = $object['idno'];
				}

				$fieldValue = self::getFieldValue($field,$object);

				$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s %s />',
					htmlSanitize($field['type']),
					htmlSanitize($field['name']),
					$fieldValue,
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

			// Output field's help (if needed)
			if(isset($field['help']) && $field['help']){
				list($helpType,$helpValue) = explode(',', $field['help'], 2);
				switch($helpType){
					case 'text':
						$output .= sprintf(' <a href="javascript:;" rel="tooltip" class="icon-question-sign" data-placement="right" data-title="%s"></a>', $helpValue);
						break;
					case 'html':
						$output .= sprintf(' <a href="javascript:;" rel="popover" class="icon-question-sign" data-html="true" data-placement="right" data-trigger="hover" data-content="%s"></a>', $helpValue);
						break;
					case 'web':
						$output .= sprintf(' <a href="javascript:;" title="Click for help" class="icon-question-sign" onclick="$(\'#helpModal_%s\').modal(\'show\');"></a>', $field['id']);
						$output .= sprintf('<div id="helpModal_%s" rel="modal" class="modal hide fade" data-show="false">', $field['id']);
						$output .= '	<div class="modal-header">';
						$output .= '		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
						$output .= '		<h3 id="myModalLabel">Field Help</h3>';
						$output .= '	</div>';
						$output .= '	<div class="modal-body">';
						$output .= sprintf('		<iframe src="%s" seamless="seamless" style="width: 100%%; height: 100%%;"></iframe>', $helpValue);
						$output .= '	</div>';
						$output .= '</div>';
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
		if(isset($formHasFiles) and $formHasFiles){
			$output .= '<div class="alert alert-block" id="objectSubmitProcessing"><strong>Processing Files</strong><br>Please Wait...</div>';
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

		if (self::hasFieldVariables($field['value'])) {
			return htmlSanitize(self::applyFieldVariables($field['value']));
		}

		return isset($object['data'][$field['name']])
			? htmlSanitize($object['data'][$field['name']])
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

			$headers = array();
			$headers[] = "Delete";
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
				$temp[] = sprintf('<input type="checkbox" name="delete[]" value="%s"',
					$objects[$I]['ID']
				);

				foreach ($form['fields'] as $field) {
					$temp[] = sprintf('<input type="%s" style="%s" name="%s_%s" value="%s" />',
						$field['type'],
						$field['style'],
						$field['name'],
						$objects[$I]['ID'],
						(isset($objects[$I]['data'][$field['name']]))?htmlSanitize($objects[$I]['data'][$field['name']]):""
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



			$table          = new tableObject("array");
			$table->summary = "Object Listing";
			$table->class   = "tableObject table table-striped table-bordered";
			$table->headers($headers);

			$output = sprintf('<form action="%s?formID=%s" method="%s" name="updateForm" data-formid="%s">',
				$_SERVER['PHP_SELF'],
				htmlSanitize($formID),
				"post",
				mfcs::$engine->openDB->escape($formID)
			);

			$output .= sessionInsertCSRF();

			$output .= $table->display($tableRows);

			$output .= '<input type="submit" name="updateEdit" value="Update" class="btn" />';
			$output .= "</form>";

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
			errorHandle::errorMsg("Invalid data provided in field '".$field['label']."'.");
			return FALSE;
		}

		// Duplicate Checking (Form)
		if (strtolower($field['duplicates']) == "true") {
			if (self::isDupe($formID,$field['name'],$value,$objectID)) {
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
						continue;
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
						return FALSE;
					}

				}

				// Check to see if the objects data has changed. if it has, update it. 
				if (encodeFields($values) != $object['data']) {
					
					if (objects::update($object['ID'],$formID,$values,$form['metadata']) === FALSE) {
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

				$sql       = sprintf("DELETE FROM `dupeMatching` WHERE `objectID`='%s'",
					$objectID
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::errorMsg("Error deleting objects.");
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
				return(FALSE);
			}
		}

		$fields = $form['fields'];

		if (usort($fields, 'sortFieldsByPosition') !== TRUE) {
			errorHandle::newError(__METHOD__."() - usort", errorHandle::DEBUG);
			if (!$importing) errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		$values = array();

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
					$oldObject              = object::get($objectID);
					$values[$field['name']] = $oldObject['data'][$field['name']];
				}
				else {
					// grab the default value from the form.
					$values[$field['name']] = $field['value'];
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

			if (objects::create($formID,$values,$form['metadata'],isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0") === FALSE) {
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

			if (objects::update($objectID,$formID,$values,$form['metadata'],isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0") === FALSE) {
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

		if (count($form1['fields']) != count($form2['fields'])) return FALSE;

		//@TODO there has to be a better way of doing this
		$compatibleFields = array();
		foreach ($form1['fields'] as $field) {
			foreach ($form2['fields'] as $field2) {
				if (
					$field['type']            == $field2['type']            &&
					$field['name']            == $field2['name']            &&
					$field['required']        == $field2['required']        &&
					$field['duplicates']      == $field2['duplicates']      &&
					$field['validation']      == $field2['validation']      &&
					$field['validationRegex'] == $field2['validationRegex'] &&
					$field['min']             == $field2['min']             &&
					$field['max']             == $field2['max']             &&
					$field['step']            == $field2['step']            &&
					$field['format']          == $field2['format']

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

		$allForms = self::getMetadataForms(TRUE);
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
}

?>