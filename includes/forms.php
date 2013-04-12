<?php

class forms {

	function get($formID=NULL) {

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

		$form['fields'] = decodeFields($form['fields']);

		if ($form['fields'] === FALSE) {
			errorHandle::newError(__METHOD__."() - fields", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		$form['idno']   = decodeFields($form['idno']);

		if ($form['idno'] === FALSE) {
			errorHandle::newError(__METHOD__."() - idno", errorHandle::DEBUG);
			errorHandle::errorMsg("Error retrieving form.");
			return FALSE;
		}

		$cache = $mfcs->cache("create",$cachID,$form);
		if ($cache === FALSE) {
			errorHandle::newError(__METHOD__."() - unable to cache form", errorHandle::DEBUG);
		}

		return $form;
	}

	public static function getForms($type = NULL) {

		$engine = EngineAPI::singleton();

		switch ($type) {
			case isnull($type):
				$sql = sprintf("SELECT `ID` FROM `forms` ORDER BY `title`");
				break;
			case TRUE:
				$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='0' ORDER BY `title`");
				break;
			case FALSE:
				$sql = sprintf("SELECT `ID` FROM `forms` WHERE `metadata`='1' ORDER BY `title`");
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

	/*
	 * Returns all of the linked metadata forms for an object form
	 */
	public static function getObjectFormMetaForms($formID) {
		$form = self::get($formID);

		$metadataForms = array();
		foreach ($form['fields'] as $field) {
			if (isset($field['choicesForm']) && validate::integer($field['choicesForm'])) {
				$metaForm        = self::get($field['choicesForm']);
				$metadataForms[] = array($field['choicesForm'] => $metaForm['title']);
			}
		}

		return $metadataForms;

	}

	public static function checkFormInProject($projectID,$formID) {

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

	public static function getFormIDInfo($formID) {
		$form = getForm($formID);
		return decodeFields($form['idno']);
	}

	public static function build($formID,$objectID = NULL) {

		$engine = EngineAPI::singleton();

		// Get the current Form
		$form   = getForm($formID);

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
			$object = getObject($objectID);
			if ($object === FALSE) {
				errorHandle::errorMsg("Error retrieving object.");
				return FALSE;
			}
		}

		$output = sprintf('<form action="%s?formID=%s%s" method="%s">',
			$_SERVER['PHP_SELF'],
			htmlSanitize($formID),
			(!isnull($objectID)) ? '&objectID='.$objectID : "",
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


			// build the actual input box

			$output .= '<div class="">';


			if ($field['type'] != "idno" || ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) != "system")) {
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
				$output .= sprintf('<div id="fineUploader_%s"></div><input type="hidden" id="%s" name="%s" value="%s">',
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					htmlSanitize($field['name']),
					md5(microtime(TRUE))
					);

				localvars::add("fieldName",htmlSanitize($field['name']));
				localvars::add("multipleFiles",(strtoupper($field['multipleFiles']) == "TRUE") ? "true" : "false");
				localvars::add("allowedExtensions",implode('", "',$field['allowedExtensions']));

				$output .= sprintf('<script type="text/javascript">%s</script>',
					file_get_contents(__DIR__."/js/fineUploader.formBuilder.js")
					);

           		// Do we display a current file?
				if(isset($object['data'][$field['name']])){
					$output .= '<div class="filePreview"><a href="#">Click to view current file</a>';
					$output .= sprintf('<div style="display: none;"><iframe src="fileViewer.php?objectID=%s&field=%s" sandbox="" seamless></iframe></div>',
						$objectID,
						$field['name']
						);
					$output .= '</div>';
				}
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
			htmlSanitize($form["submitButton"]),
			$objectID ? "updateForm" : "submitForm"
			);

		$output .= "</form>";

		return $output;

	}

	// NOTE: data is being saved as RAW from the array.
	function submit($formID,$objectID=NULL) {
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
			if (strtolower($field['duplicatesForm']) == "true") {
				if (self::isDupe($formID,$field['name'],$engine->cleanPost['RAW'][$field['name']])) {
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
				processUploads($field,$engine->cleanPost['RAW'][$field['name']]);
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


		// end transactions
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		return TRUE;
	}

	// $value must be RAW
	public static function isDupe($formID,$field,$value) {

		$engine = EngineAPI::singleton();

		$sql = sprintf("SELECT COUNT(*) FROM dupeMatching WHERE `formID`='%s' AND `field`='%s' AND `value`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($field),
			$engine->openDB->escape($value)
			);

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

}

?>