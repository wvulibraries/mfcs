<?php

class forms {

	function get($formID=NULL) {

		if (isnull($formID)) {
			return self::getForms();
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

		return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
	}

	public static function getForms($type = NULL) {

		$engine = EngineAPI::singleton();

		switch ($type) {
			case isnull($type):
				$sql = sprintf("SELECT * FROM `forms` ORDER BY `title`");
				break;
			case TRUE:
				$sql = sprintf("SELECT * FROM `forms` WHERE `metadata`='0' ORDER BY `title`");
				break;
			case FALSE:
				$sql = sprintf("SELECT * FROM `forms` WHERE `metadata`='1' ORDER BY `title`");
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
			$forms[] = $row;
		}

		return $forms;

	}

	public static function getObjectForms() {
		return self::getForms(TRUE);
	}

	public static function getMetadataForms() {
		return self::getForms(FALSE);
	}

	public static function build($formID,$objectID = NULL) {

		$engine = EngineAPI::singleton();

	// Get the current Form
		$form   = getForm($formID);

		if ($form === FALSE) {
			return FALSE;
		}

		$fields = decodeFields($form['fields']);

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
			$object['data'] = decodeFields($object['data']);
			if ($object['data'] === FALSE) {
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

		// }
		// else if ($field['type'] == "radio") {
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
				$output .= sprintf('
					<script type="text/javascript">
					$("#fineUploader_%s")
					.fineUploader({
						request: {
							endpoint: "{local var="siteRoot"}includes/uploader.php",
							params: {
								engineCSRFCheck: "{engine name="csrf" insert="false"}",
								uploadID: $("#%s").val(),
							}
						},
						failedUploadTextDisplay: {
							mode: "custom",
							maxChars: 40,
							responseProperty: "error",
							enableTooltip: true
						},
						multiple: %s,
						validation: {
							allowedExtensions: ["%s"],
						},
						text: {
							uploadButton: \'<i class="icon-plus icon-white"></i> Select Files\'
						},
						showMessage: function(message) {
							$("#fineUploader_%s .qq-upload-list").append(\'<li class="alert alert-error">\' + message + \'</li >\');
						},
						classes: {
							success: "alert alert-success",
							fail: "alert alert-error"
						},
					})
				.on("complete", function(event,id,fileName,responseJSON) {
				});
				</script>',
				htmlSanitize($field['name']),
				htmlSanitize($field['name']),
				(strtoupper($field['multipleFiles']) == "TRUE") ? "true" : "false",
				implode('", "',$field['allowedExtensions']),
				htmlSanitize($field['name'])
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
		if (strtolower($field['managedBy']) == "system") continue;
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

}

?>