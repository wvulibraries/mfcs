<?php

class forms {

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

	public static function get($formID=NULL) {

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

		$sql       = sprintf("SELECT * FROM `forms` WHERE `ID`='%s'",
			$engine->openDB->escape($formID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
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

	public static function getForms($type = NULL) {

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

			$forms[] = self::get($row['ID']);

		}

		return $forms;

	}

	public static function getObjectForms() {
		return self::getForms(TRUE);
	}

	public static function getMetadataForms() {
		return self::getForms(FALSE);
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

	/*
	 * Returns all of the linked metadata forms for an object form
	 */
	public static function getObjectFormMetaForms($formID) {

		if (($form = self::get($formID)) === FALSE) {
			return FALSE;
		}

		$metadataForms = array();
		foreach ($form['fields'] as $field) {
			if (isset($field['choicesForm']) && validate::integer($field['choicesForm'])) {
				$metaForm        = self::get($field['choicesForm']);
				$metadataForms[] = array('formID' => $field['choicesForm'], 'title' => $metaForm['title']);
			}
		}

		return $metadataForms;

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

		$sql = sprintf("SELECT COUNT(*) FROM `forms_projects` WHERE formID='%s' AND projectID='%s'",
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($projectID)
		);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return array();
		}

		$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

		if ((int) $row['COUNT(*)']){
			return TRUE;
		}

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
			$projectForms = projects::getForms($project['ID'],TRUE);

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

	private static function drawSelectDropdowns($field,$fieldChoices) {
		$output = "";
		foreach ($fieldChoices as $choice) {
			$output .= sprintf('<option value="%s" %s>%s</option>',
				htmlSanitize($choice['value']),
				(isset($field['choicesDefault']) && !isempty($field['choicesDefault']) && $field['choicesDefault'] == $row['value'])?'selected="selected"':"",
				htmlSanitize($choice['display'])
			);
		}
		return $output;
	}

	private static function drawMultiselectBoxes($field,$fieldChoices) {
		$output = "";
		foreach ($fieldChoices as $choice) {
			$output .= sprintf('<option value="%s" %s>%s</option>',
				htmlSanitize($choice['value']),
				(isset($field['choicesDefault']) && !isempty($field['choicesDefault']) && $field['choicesDefault'] == $row['value'])?'selected="selected"':"",
				htmlSanitize($choice['display'])
			);
		}
		return $output;
	}

	public static function drawFieldChoices($field,$choices) {

		if (!isset($field['type'])) return FALSE;

		switch($field['type']) {
			case "checkbox":
			case "radio":
				return self::drawCheckBoxes($field,$choices);
				break;
			case "select":
				return self::drawSelectDropdowns($field,$choices);
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
			$object = objects::get($objectID);
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

		$output .= sprintf('<header><h1>%s</h1><h2>%s</h2></header>',
			htmlSanitize($form['title']),
			htmlSanitize($form['description']));

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
				// @TODO should this be raw? // security issue?
				if (isset($engine->cleanPost['RAW'][$field['name']])) {
					$object['data'][$field['name']] = $engine->cleanPost['RAW'][$field['name']];
					if ($field['type'] == "select") {
						$field['choicesDefault'] = $engine->cleanPost['RAW'][$field['name']];
					}
				}
			}

			// build the actual input box

			$output .= '<div class="">';


			if ($field['type'] != "idno" || ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) != "system")) {
				$output .= sprintf('<label for="%s">%s</label>',
					htmlSanitize($field['id']),
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

				$output .= self::drawFieldChoices($field,$fieldChoices);

				$output .= "</select>";

			}
			else if ($field['type'] == 'multiselect') {

				if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
					return FALSE;
				}

				$output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
					htmlSanitize($field['name']),
					htmlSanitize($field['name'])
				);

				if (isset($object['data'][$field['name']])) {
					foreach ($object['data'][$field['name']] as $selectedItem) {
						$tmpObj  = objects::get($selectedItem);
						$output .= sprintf('<option value="%s">%s</option>',
							$engine->openDB->escape($selectedItem),
							$engine->openDB->escape($tmpObj['data'][$field['choicesField']])
						);
					}
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
			}
			else if ($field['type'] == 'file') {
				localvars::add("fieldName",htmlSanitize($field['name']));
				localvars::add("multipleFiles",(strtoupper($field['multipleFiles']) == "TRUE") ? "true" : "false");
				localvars::add("allowedExtensions",implode('", "',$field['allowedExtensions']));

				// Do we display a current file?
				if(isset($object['data'][$field['name']]) and sizeof($object['data'][$field['name']])){
					if(str2bool($field['multipleFiles'])){
						for($i=1;$i<=sizeof($object['data'][$field['name']]); $i++){
							$output .= sprintf('<div class="filePreview"><a class="previewLink" href="#">Click to view file #%s</a> | <a class="downloadLink" href="fileViewer.php?objectID=%s&field=%s&fileNum=%s&download=1">Click to download file #%s</a>',
								$i,
								$objectID,
								$field['name'],
								$i,
								$i
							);
							$output .= sprintf('<div style="display: none;"><iframe src="fileViewer.php?objectID=%s&field=%s&fileNum=%s" sandbox="" seamless></iframe></div>',
								$objectID,
								$field['name'],
								$i
							);
							$output .= '</div>';
						}
					}else{
						$output .= sprintf('<div class="filePreview"><a class="previewLink" href="#">Click to view file</a> | <a class="downloadLink" href="fileViewer.php?objectID=%s&field=%s&download=1">Click to download file</a>',
							$objectID,
							$field['name']
						);
						$output .= sprintf('<div style="display: none;"><iframe src="fileViewer.php?objectID=%s&field=%s" sandbox="" seamless></iframe></div>',
							$objectID,
							$field['name']
						);
						$output .= '</div>';
					}
				}

				// Output "Select Files" button for new uploads
				$output .= sprintf('<div id="fineUploader_%s"></div><input type="hidden" id="%s" name="%s" value="%s"><script type="text/javascript">%s</script>',
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					md5(microtime(TRUE)),
					file_get_contents(__DIR__."/js/fineUploader.formBuilder.js")
				);

			}
			else {
				if ($field['type'] == "idno") {
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
			(isnull($objectID))?htmlSanitize($form["submitButton"]):htmlSanitize($form["updateButton"]),
			$objectID ? "updateForm" : "submitForm"
		);

		$output .= "</form>";

		return $output;

	}

	public static function buildEditTable($formID) {

		$form = self::get($formID);

		// Get all objects from this form
		$objects = objects::getAllObjectsForForm($formID);

		if (count($objects) > 0) {

			$headers = array();
			$headers[] = "Delete";
			foreach ($form['fields'] as $field) {
				$headers[] = $field['label'];
			}

			$tableRows = array();
			for($I=0;$I<count($objects);$I++) {
				$temp   = array();
				$temp[] = sprintf('<input type="checkbox" name="delete[]" value="%s"',
					$objects[$I]['ID']
				);

				foreach ($form['fields'] as $field) {
					$temp[] = sprintf('<input type="%s" name="%s_%s" value="%s" />',
						$field['type'],
						$field['name'],
						$objects[$I]['ID'],
						htmlSanitize($objects[$I]['data'][$field['name']])
					);
				}

				$tableRows[] = $temp;
			}

			$table          = new tableObject("array");
			$table->summary = "Object Listing";
			$table->headers($headers);

			$output = sprintf('<form action="%s?formID=%s" method="%s" name="updateForm" data-formid="%s">',
				$_SERVER['PHP_SELF'],
				htmlSanitize($formID),
				"post",
				mfcs::$engine->openDB->escape($formID)
			);

			$output .= sessionInsertCSRF();

			$output .= $table->display($tableRows);

			$output .= '<input type="submit" name="updateEdit" value="Update" />';
			$output .= "</form>";

			return $output;
		}
		else {
			return "No data entered for this Metadata Form.";
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
		$objects = objects::getAllObjectsForForm($formID);
		if (count($objects) > 0) {
			foreach ($objects as $object) {

				$values = array();

				foreach ($form['fields'] as $field) {

					// @TODO this needs to be broken off into a method, duplicated in submit() as well
					if ($field['type'] == "fieldset" || $field['type'] == "idno" || $field['disabled'] == "true") continue;

					if (strtolower($field['required']) == "true"           &&
						(!isset($engine->cleanPost['RAW'][$field['name']."_".$object['ID']]) ||
							isempty($engine->cleanPost['RAW'][$field['name']."_".$object['ID']]))
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
							$valid = validate::$field['validation']($engine->cleanPost['RAW'][$field['name']."_".$object['ID']]);
						}
					}
				}

				if ($valid === FALSE) {
					errorHandle::errorMsg("Invalid data provided in field '".$field['label']."'.");
					continue;
				}

				// Duplicate Checking (Form)
				if (strtolower($field['duplicates']) == "true") {
					if (self::isDupe($formID,$field['name'],$engine->cleanPost['RAW'][$field['name']."_".$object['ID']],$object['ID'])) {
						errorHandle::errorMsg("Duplicate data (in form) provided in field '".$field['label']."'.");
						continue;
					}
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
				}

				if (strtolower($field['type']) == "file") {
					$values[$field['name']] = (array)files::processUploads($field,$engine->cleanPost['RAW'][$field['name']."_".$object['ID']]);
				}

				if(!isset($values[$field['name']])) $values[$field['name']] = $engine->cleanPost['RAW'][$field['name']."_".$object['ID']];
				

				if (!is_empty($engine->errorStack)) {
					return FALSE;
				}

			// place old version into revision control
				$rcs = new revisionControlSystem('objects','revisions','ID','modifiedTime');
				$return = $rcs->insertRevision($object['ID']);

				if ($return !== TRUE) {

					$engine->openDB->transRollback();
					$engine->openDB->transEnd();

					errorHandle::errorMsg("Error inserting revision.");
					errorHandle::newError(__METHOD__."() - unable to insert revisions", errorHandle::DEBUG);
					return FALSE;
				}

			// insert new version
				$sql = sprintf("UPDATE `objects` SET `data`='%s', `modifiedTime`='%s' WHERE `ID`='%s'",
					encodeFields($values),
					time(),
					$engine->openDB->escape($object['ID'])
					);

				$sqlResult = $engine->openDB->query($sql);
			}

			if (!$sqlResult['result']) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

		}}



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
	public static function submit($formID,$objectID=NULL) {
		$engine = EngineAPI::singleton();

		if (isnull($objectID)) {
			$newObject = TRUE;
		}
		else {
			$newObject = FALSE;
		}

		// Get the current Form
		$form   = self::get($formID);

		if ($form === FALSE) {
			return FALSE;
		}

		// the form is an object form, make sure that it has an ID field defined.
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

			// Duplicate Checking (Form)
			if (strtolower($field['duplicates']) == "true") {
				if (self::isDupe($formID,$field['name'],$engine->cleanPost['RAW'][$field['name']],$objectID)) {
					errorHandle::errorMsg("Duplicate data (in form) provided in field '".$field['label']."'.");
					continue;
				}
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
			}

			if (strtolower($field['type']) == "file") {
				$values[$field['name']] = (array)files::processUploads($field,$engine->cleanPost['RAW'][$field['name']]);
			}

			if(!isset($values[$field['name']])) $values[$field['name']] = $engine->cleanPost['RAW'][$field['name']];
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
			$sql       = sprintf("INSERT INTO `objects` (parentID,formID,data,metadata,modifiedTime) VALUES('%s','%s','%s','%s','%s')",
				isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",
				$engine->openDB->escape($formID),
				encodeFields($values),
				$engine->openDB->escape($form['metadata']),
				time()
			);
		}
		else {
			// place old version into revision control
			$rcs = new revisionControlSystem('objects','revisions','ID','modifiedTime');
			$return = $rcs->insertRevision($objectID);

			if ($return !== TRUE) {

				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				errorHandle::errorMsg("Error inserting revision.");
				errorHandle::newError(__METHOD__."() - unable to insert revisions", errorHandle::DEBUG);
				return FALSE;
			}

			// insert new version
			$sql = sprintf("UPDATE `objects` SET `parentID`='%s', `formID`='%s', `data`='%s', `metadata`='%s', `modifiedTime`='%s' WHERE `ID`='%s'",
				isset($engine->cleanPost['MYSQL']['parentID'])?$engine->cleanPost['MYSQL']['parentID']:"0",
				$engine->openDB->escape($formID),
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

			errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		if ($newObject === TRUE) {
			$objectID = $sqlResult['id'];
			localvars::add("newObjectID",$objectID);
		}

		// Check to see if this object already exists in the objectProjects table. If not, add it.
		// @TODO
		// $sql       = sprintf("SELECT COUNT(*) FROM `objectProjects` WHERE `objectID`='%s' AND `projectID`='%s'",
		// 	$engine->openDB->escape($objectID),
		// 	$engine->openDB->escape($project['ID'])
		// 	);
		// $sqlResult = $engine->openDB->query($sql);

		// if (!$sqlResult['result']) {
		// 	$engine->openDB->transRollback();
		// 	$engine->openDB->transEnd();

		// 	errorHandle::newError(__METHOD__."() - error getting count: ".$sqlResult['error'], errorHandle::DEBUG);
		// 	return FALSE;
		// }

		// $row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		// if ($row['COUNT(*)'] == 0) {
		// 	$sql       = sprintf("INSERT INTO `objectProjects` (objectID,projectID) VALUES('%s','%s')",
		// 		$engine->openDB->escape($objectID),
		// 		$engine->openDB->escape($project['ID'])
		// 		);
		// 	$sqlResult = $engine->openDB->query($sql);

		// 	if (!$sqlResult['result']) {
		// 		$engine->openDB->transRollback();
		// 		$engine->openDB->transEnd();

		// 		errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
		// 		return FALSE;
		// 	}
		// }



		// if it is an object form (not a metadata form)
		// do the IDNO stuff
		if ($form['metadata'] == "0") {
			// increment the project counter
			$sql       = sprintf("UPDATE `forms` SET `count`=`count`+'1' WHERE `ID`='%s'",
				$engine->openDB->escape($formID['ID'])
			);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - Error incrementing form counter: ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

			// if the idno is managed by the system get a new idno
			if ($idnoInfo['managedBy'] == "system") {
				$idno = $engine->openDB->escape(mfcs::getIDNO($formID));
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
			// update all the fields in the dupeMatching Table

			// delete all matching fields
			$sql       = sprintf("DELETE FROM `dupeMatching` WHERE `formID`='%s' AND `objectID`='%s'",
				$engine->openDB->escape($formID),
				$engine->openDB->escape($objectID)
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
			$sql       = sprintf("INSERT INTO `dupeMatching` (`formID`,`objectID`,`field`,`value`) VALUES('%s','%s','%s','%s')",
				$engine->openDB->escape($formID),
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

		// Add it to the users current projects
		if ($newObject === TRUE) {
			if (($currentProjects = users::loadProjects()) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				return FALSE;
			}
			foreach ($currentProjects as $projectID => $projectName) {
				if ((objects::addProject($objectID,$projectID)) === FALSE) {
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();
					return FALSE;
				}
			}
		}

		// end transactions
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		if ($newObject === TRUE) {
			errorHandle::successMsg("Object created successfully.");
		}
		else {
			errorHandle::successMsg("Object updated successfully.");
		}

		return TRUE;
	}

	// $value must be RAW
	public static function isDupe($formID,$field,$value,$objectID=NULL) {
		$sql = sprintf("SELECT COUNT(*) FROM `dupeMatching` WHERE `formID`='%s' AND `field`='%s' AND `value`='%s' %s",
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($field),
			mfcs::$engine->openDB->escape($value),
			(!isnull($objectID))?"AND `objectID`!='".mfcs::$engine->openDB->escape($objectID)."'":""
		);

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if ($sqlResult['result'] === FALSE) {
			return TRUE;
		}

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		// we return TRUE on Error, because if a dupe is encountered we want it to fail out.

		if ((INT)$row['COUNT(*)'] > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}
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

}

?>