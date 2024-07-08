<?php

class formBuilder {
	public static function build($formID,$objectID = NULL,$error=FALSE) {
		$engine = EngineAPI::singleton();
		// Get the current Form
		$form   = forms::get($formID);
		if ($form === FALSE) {
			return FALSE;
		}

		$fields = forms::sortFields($form['fields']);
		if ($fields === FALSE) {
			return FALSE;
		}

		$object = forms::getObject($objectID, $error);
		if ($object === FALSE) {
			return FALSE;
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
					forms::getFieldValue($field,(isset($object))?$object:NULL)
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


				$output .= forms::drawFieldChoices($field,$fieldChoices);

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

				$output .= forms::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL);

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
						forms::drawFieldChoices($field,$fieldChoices)
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
				$fieldValue = forms::getFieldValue($field,(isset($object))?$object:NULL);


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
		if (!isnull($objectID) && forms::isMetadataForm($formID)) {
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

	// public static function build($formID, $objectID = NULL, $error=FALSE) {
    //     $form = forms::get($formID);
    //     if (!$form) return FALSE;

	// 	$fields = forms::sortFields($form['fields']);
    //     if (!$fields) return FALSE;

	// 	$object = forms::getObject($objectID, $error);
	// 	if (!$object) return FALSE;

    //     $output = self::initializeForm($formID, $objectID);

    //     $output .= self::addHiddenFields($objectID);

    //     $output .= self::handlePublicRelease($form, $objectID, $object);

    //     $output .= self::buildFields($fields, $object, $error);

    //     $output .= self::closeForm();

    //     return $output;

	// 	// $output = sprintf(
	// 	// 	'<form action="%s?formID=%s%s" method="%s" name="insertForm" data-formid="%s">',
	// 	// 	$_SERVER['PHP_SELF'],
	// 	// 	htmlSanitize($formID),
	// 	// 	(!is_null($objectID)) ? '&objectID=' . $objectID : "",
	// 	// 	"post",
	// 	// 	mfcs::$engine->openDB->escape($formID)
	// 	// );

	// 	// $output .= sessionInsertCSRF();

	// 	// if (isset($engine->cleanGet['HTML']['parentID'])) {
	// 	// 	$output .= sprintf(
	// 	// 		'<input type="hidden" name="parentID" value="%s">',
	// 	// 		$engine->cleanGet['HTML']['parentID']
	// 	// 	);
	// 	// }

	// 	// // If there is a Lock ID add it to the form
	// 	// if (!empty(localvars::get("lockID"))) {
	// 	// 	$output .= sprintf(
	// 	// 		'<input type="hidden" name="lockID" value="%s">',
	// 	// 		localvars::get("lockID")
	// 	// 	);
	// 	// }

	// 	// if ($form['objPublicReleaseShow'] == 1 && $form['metadata'] == 0) {
	// 	// 	$objPublicReleaseDefaultTrueYes = forms::publicReleaseObjSelect($objectID, $object, $form);
	// 	// 	$output .= '<label for="publicReleaseObj">Release to Public:</label>';
	// 	// 	$output .= '<select name="publicReleaseObj" id="publicReleaseObj">';
	// 	// 	$output .= sprintf(
	// 	// 		'<option value="yes" %s>Yes</option>',
	// 	// 		$objPublicReleaseDefaultTrueYes ? "selected" : ""
	// 	// 	);
	// 	// 	$output .= sprintf(
	// 	// 		'<option value="no" %s>No</option>',
	// 	// 		!$objPublicReleaseDefaultTrueYes ? "selected" : ""
	// 	// 	);
	// 	// 	$output .= '</select>';
	// 	// }

	// 	// $currentFieldset = "";

	// 	// foreach ($fields as $field) {

	// 	// 	if ($field['type'] == "fieldset") {
	// 	// 		continue;
	// 	// 	}
	// 	// 	if ($field['type'] == "idno" && (strtolower($field['managedBy']) == "system" && isnull($objectID))) {
	// 	// 		continue;
	// 	// 	}

	// 	// 	// deal with field sets
	// 	// 	if ($field['fieldset'] != $currentFieldset) {
	// 	// 		if ($currentFieldset != "") {
	// 	// 			$output .= "</fieldset>";
	// 	// 		}
	// 	// 		if (!isempty($field['fieldset'])) {
	// 	// 			$output .= sprintf('<fieldset><legend>%s</legend>',
	// 	// 				$field['fieldset']
	// 	// 			);
	// 	// 		}
	// 	// 		$currentFieldset = $field['fieldset'];
	// 	// 	}


	// 	// 	if ($error === TRUE) {
	// 	// 		// This is RAW because it is post data being displayed back out to the user who submitted it
	// 	// 		// during a submission error. we don't want to corrupt the data by sanitizing it and then
	// 	// 		// sanitizing it again on submissions
	// 	// 		//
	// 	// 		// it should not be a security issue because it is being displayed back out to the user that is submissing the data.
	// 	// 		// this will likely cause issues with security scans
	// 	// 		//
	// 	// 		// @SECURITY False Positive 1
	// 	// 		if (isset($engine->cleanPost['RAW'][$field['name']])) {
	// 	// 			$object['data'][$field['name']] = $engine->cleanPost['RAW'][$field['name']];
	// 	// 			if ($field['type'] == "select") {
	// 	// 				$field['choicesDefault'] = $engine->cleanPost['RAW'][$field['name']];
	// 	// 			}
	// 	// 		}
	// 	// 	}

	// 	// 	// build the actual input box

	// 	// 	$output .= '<div class="formCreator dataEntry">';


	// 	// 	// Handle disabled on insert form
	// 	// 	if (isset($field['disabledInsert']) && $field['disabledInsert'] == "true" && isnull($objectID)) {
	// 	// 		$field['disabled'] = "true";
	// 	// 	}

	// 	// 	// Handle Read Only on Update form
	// 	// 	if (isset($field['disabledUpdate']) &&  $field['disabledUpdate'] == "true" && !isnull($objectID)) {
	// 	// 		$field['readonly'] = "true";
	// 	// 	}

	// 	// 	// @TODO There is excessive logic here. We have already continued/skipped passed IDNOs that we aren't displaying at this point.
	// 	// 	// version 2.0 cleanup.
	// 	// 	if ($field['type'] != "idno"
	// 	// 		|| ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) != "system")
	// 	// 		|| ($field['type'] == "idno" && isset($field['managedBy']) && strtolower($field['managedBy']) == "system" && !isnull($objectID))
	// 	// 		) {
	// 	// 		$output .= sprintf('<label for="%s" class="formLabel %s">%s:</label>',
	// 	// 			htmlSanitize($field['id']),
	// 	// 			(strtolower($field['required']) == "true")?"requiredField":"",
	// 	// 			htmlSanitize($field['label'])
	// 	// 		);
	// 	// 	}

	// 	// 	if ($field['type'] == "textarea" || $field['type'] == "wysiwyg") {
	// 	// 		$output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s %s" %s %s %s %s>%s</textarea>',
	// 	// 			htmlSanitize($field['name']),
	// 	// 			htmlSanitize($field['placeholder']),
	// 	// 			htmlSanitize($field['id']),
	// 	// 			htmlSanitize($field['class']),
	// 	// 			($field['type'] == "wysiwyg" ? "wysiwyg" : ""),
	// 	// 			(!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
	// 	// 			//true/false type attributes
	// 	// 			(strtoupper($field['required']) == "TRUE")?"required":"",
	// 	// 			(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
	// 	// 			(strtoupper($field['disabled']) == "TRUE")?"disabled":"",
	// 	// 			forms::getFieldValue($field,(isset($object))?$object:NULL)
	// 	// 		);

	// 	// 		if ($field['type'] == "wysiwyg") {
	// 	// 			$output .= sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%sincludes/js/CKEditor/"</script>',
	// 	// 				localvars::get("siteRoot")
	// 	// 			);
	// 	// 			$output .= sprintf('<script type="text/javascript" src="%sincludes/js/CKEditor/ckeditor.js"></script>',
	// 	// 				localvars::get("siteRoot")
	// 	// 			);
	// 	// 			$output .= '<script type="text/javascript">$(function(){';
	// 	// 			$output .= sprintf('if (CKEDITOR.instances["%s"]){ CKEDITOR.remove(CKEDITOR.instances["%s"]); }',
	// 	// 				htmlSanitize($field['id']),
	// 	// 				htmlSanitize($field['id'])
	// 	// 			);
	// 	// 			$output .= sprintf(' CKEDITOR.replace("%s"); ',
	// 	// 				htmlSanitize($field['id'])
	// 	// 			);

	// 	// 			$output .= 'htmlParser = "";';
	// 	// 			$output .= '';
	// 	// 			$output .= sprintf('if(CKEDITOR.instances["%s"].dataProcessor){ CKEDITOR.instances["%s"].dataProcessor.htmlFilter;}',
	// 	// 				$field['name'],
	// 	// 				htmlSanitize($field['id'])
	// 	// 			);

	// 	// 			$output .= '});</script>';

	// 	// 		}

	// 	// 	}
	// 	// 	else if ($field['type'] == "checkbox" || $field['type'] == "radio") {

	// 	// 		if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
	// 	// 			return FALSE;
	// 	// 		}

	// 	// 		$output .= sprintf('<div data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
	// 	// 			$field['type'],
	// 	// 			$formID,
	// 	// 			htmlSanitize($field['name']),
	// 	// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
	// 	// 		);


	// 	// 		$output .= forms::drawFieldChoices($field,$fieldChoices);

	// 	// 		$output .= '</div>';

	// 	// 	}
	// 	// 	else if ($field['type'] == "select") {

	// 	// 		if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
	// 	// 			return FALSE;
	// 	// 		}

	// 	// 		$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
	// 	// 			htmlSanitize($field['name']),
	// 	// 			htmlSanitize($field['name']),
	// 	// 			$field['type'],
	// 	// 			$formID,
	// 	// 			htmlSanitize($field['name']),
	// 	// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
	// 	// 		);

	// 	// 		$output .= forms::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL);

	// 	// 		$output .= "</select>";

	// 	// 	}
	// 	// 	// else if ($field['type'] == "select") {

	// 	// 	// 	if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
	// 	// 	// 		if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
	// 	// 	// 			return FALSE;
	// 	// 	// 		}

	// 	// 	// 		$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>%s</select>',
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			$field['type'],
	// 	// 	// 			$formID,
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
	// 	// 	// 			self::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL)
	// 	// 	// 		);
	// 	// 	// 	}
	// 	// 	// 	else {
	// 	// 	// 		$output .= sprintf('<input type="hidden" name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			$field['type'],
	// 	// 	// 			$formID,
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
	// 	// 	// 			htmlSanitize($field['name'])
	// 	// 	// 		);

	// 	// 	// 		$output .= sprintf("<script charset=\"utf-8\">
	// 	// 	// 				$(function() {
	// 	// 	// 					$('#%s')
	// 	// 	// 						.select2({
	// 	// 	// 							minimumResultsForSearch: 10,
	// 	// 	// 							placeholder: 'Make a Selection',
	// 	// 	// 							ajax: {
	// 	// 	// 								url: 'retrieveOptions.php',
	// 	// 	// 								dataType: 'json',
	// 	// 	// 								quietMillis: 300,
	// 	// 	// 								data: function(term, page) {
	// 	// 	// 									return {
	// 	// 	// 										q: term,
	// 	// 	// 										page: page,
	// 	// 	// 										pageSize: 1000,
	// 	// 	// 										formID: '%s',
	// 	// 	// 										fieldName: '%s'
	// 	// 	// 									};
	// 	// 	// 								},
	// 	// 	// 								results: function(data, page) {
	// 	// 	// 									var more = (page * data.pageSize) < data.total;

	// 	// 	// 									return {
	// 	// 	// 										results: data.options,
	// 	// 	// 										more: more
	// 	// 	// 									};
	// 	// 	// 								},
	// 	// 	// 							},
	// 	// 	// 							// initSelection: function(element, callback) {

	// 	// 	// 					  //           var id = $(element).val();
	// 	// 	// 					  //           if(id !== '') {
	// 	// 	// 					  //           	$.ajax('retrieveSingleOption.php', {
	// 	// 	// 					  //           		data: function() {
	// 	// 	// 					  //           			return {
	// 	// 	// 					  //           				formID: '%s',
	// 	// 	// 					  //           				id: id
	// 	// 	// 					  //           			};
	// 	// 	// 					  //           		},
	// 	// 	// 					  //                   dataType: 'json'
	// 	// 	// 					  //               }).done(function(data) {
	// 	// 	// 					  //                   callback(data.results[0]);
	// 	// 	// 					  //               });
	// 	// 	// 					  //           }
	// 	// 	// 					  //       }
	// 	// 	// 						});
	// 	// 	// 					// $('#%s').select2( 'val', '%s' );
	// 	// 	// 				});

	// 	// 	// 			</script>",
	// 	// 	// 			htmlSanitize($field['name']),
	// 	// 	// 			htmlSanitize($field['choicesForm']),
	// 	// 	// 			htmlSanitize($field['choicesField']),
	// 	// 	// 			$object['data'][$field['name']]
	// 	// 	// 		);
	// 	// 	// 	}

	// 	// 	// }
	// 	// 	else if ($field['type'] == 'multiselect') {


	// 	// 		$output .= '<div class="multiSelectContainer">';
	// 	// 		$output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
	// 	// 			htmlSanitize($field['name']),
	// 	// 			htmlSanitize(str_replace("/","_",$field['name']))
	// 	// 		);

	// 	// 		if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {

	// 	// 			foreach ($object['data'][$field['name']] as $selectedItem) {
	// 	// 				$tmpObj  = objects::get($selectedItem, true);
	// 	// 				$output .= sprintf('<option value="%s">%s</option>',
	// 	// 					htmlSanitize($selectedItem),
	// 	// 					htmlSanitize($tmpObj['data'][$field['choicesField']])
	// 	// 				);

	// 	// 				// if the temp object is false then we have a problem
	// 	// 				// if($tmpObj === false){
	// 	// 				// 	errorHandle::newError("Can't get Object for Metadata Object", errorHandle::DEBUG);
	// 	// 				// }
	// 	// 			}
	// 	// 		}

	// 	// 		$output .= '</select><br />';

	// 	// 		if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
	// 	// 			if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
	// 	// 				return FALSE;
	// 	// 			}

	// 	// 			$output .= sprintf('<select name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s onchange="addItemToID(\'%s\', this.options[this.selectedIndex]);">%s</select>',
	// 	// 				htmlSanitize(str_replace("/","_",$field['name'])),
	// 	// 				htmlSanitize($field['name']),
	// 	// 				$field['type'],
	// 	// 				$formID,
	// 	// 				htmlSanitize($field['name']),
	// 	// 				(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
	// 	// 				htmlSanitize(str_replace("/","_",$field['name'])),
	// 	// 				forms::drawFieldChoices($field,$fieldChoices)
	// 	// 			);
	// 	// 		}
	// 	// 		else {
	// 	// 			$output .= sprintf('<input type="hidden" name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
	// 	// 				htmlSanitize($field['name']),
	// 	// 				htmlSanitize(str_replace("/","_",$field['name'])),
	// 	// 				$field['type'],
	// 	// 				$formID,
	// 	// 				htmlSanitize($field['name']),
	// 	// 				(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
	// 	// 				htmlSanitize($field['name'])
	// 	// 			);

	// 	// 			$output .= sprintf("<script charset=\"utf-8\">
	// 	// 					$(function() {
	// 	// 						$('#%s_available')
	// 	// 							.select2({
	// 	// 								minimumResultsForSearch: 10,
	// 	// 								placeholder: 'Make a Selection',
	// 	// 								ajax: {
	// 	// 									url: 'retrieveOptions.php',
	// 	// 									dataType: 'json',
	// 	// 									quietMillis: 300,
	// 	// 									async: true,
	// 	// 									data: function(term, page) {
	// 	// 										return {
	// 	// 											q: term,
	// 	// 											page: page,
	// 	// 											pageSize: 1000,
	// 	// 											formID: '%s',
	// 	// 											fieldName: '%s'
	// 	// 										};
	// 	// 									},
	// 	// 									results: function(data, page) {
	// 	// 										var more = (page * data.pageSize) < data.total;
	// 	// 										return {
	// 	// 											results: data.options,
	// 	// 											more: more
	// 	// 										};
	// 	// 									},
	// 	// 								},
	// 	// 							})
	// 	// 							.on('select2-selecting', function(e) {
	// 	// 								addToID('%s', e.val, e.choice.text);
	// 	// 								console.log(%s);
	// 	// 							});
	// 	// 					});
	// 	// 				</script>",
	// 	// 				htmlSanitize(str_replace("/","_",$field['name'])),
	// 	// 				htmlSanitize($field['choicesForm']),
	// 	// 				htmlSanitize($field['choicesField']),
	// 	// 				htmlSanitize(str_replace("/","_",$field['name'])),
	// 	// 				htmlSanitize(str_replace("/","_",$field['name']))
	// 	// 			);
	// 	// 		}

	// 	// 		$output .= "<br />";
	// 	// 		$output .= sprintf('<button type="button" onclick="removeFromList(\'%s\')" class="btn">Remove Selected</button>',
	// 	// 			htmlSanitize(htmlSanitize(str_replace("/","_",$field['name'])))
	// 	// 			);

	// 	// 		$output .= "</div>";
	// 	// 	}
	// 	// 	else if ($field['type'] == 'file') {
	// 	// 		$formHasFiles = true;
	// 	// 		$output .= '<div style="display: inline-block;">';
	// 	// 		if(!isnull($objectID)){
	// 	// 			$output .= empty($object['data'][ $field['name'] ])
	// 	// 				? '<span style="color: #666;font-style: italic;">No file uploaded</span><br>'
	// 	// 				: '<a href="javascript:;" onclick="$(\'#filesTab\').click();">Click to view files tab</a><br>';
	// 	// 		}
	// 	// 		$uploadID = md5($field['name'].mt_rand());
	// 	// 		$output .= sprintf('<div class="fineUploader" data-multiple="%s" data-upload_id="%s" data-allowed_extensions="%s" style="display: inline-block;"></div><input type="hidden" name="%s" value="%s">',
	// 	// 			htmlSanitize($field['multipleFiles']),
	// 	// 			$uploadID,
	// 	// 			htmlSanitize(implode(',',$field['allowedExtensions'])),
	// 	// 			htmlSanitize($field['name']),
	// 	// 			$uploadID);
	// 	// 		$output .= '</div>';
	// 	// 	}
	// 	// 	else {

	// 	// 		// populate the idno field
	// 	// 		if ($field['type'] == "idno") {
	// 	// 			$field['type'] = "text";
	// 	// 			if (isset($object) && !isset($object['data'][$field['name']])) $object['data'][$field['name']] = $object['idno'];

	// 	// 			// the IDNO is managed by the user. It shouldn't be set to read only
	// 	// 			if (isset($field['managedBy']) && strtolower($field['managedBy']) != "system") {
	// 	// 				$field['readonly'] = "false";
	// 	// 			}
	// 	// 			else {
	// 	// 				// just in case ...
	// 	// 				$field['readonly'] = "true";
	// 	// 			}

	// 	// 		}

	// 	// 		// get the field value, if the object exists
	// 	// 		$fieldValue = forms::getFieldValue($field,(isset($object))?$object:NULL);


	// 	// 		$output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s />',
	// 	// 			htmlSanitize($field['type']),
	// 	// 			htmlSanitize($field['name']),
	// 	// 			$fieldValue,
	// 	// 			htmlSanitize($field['placeholder']),
	// 	// 			//for numbers
	// 	// 			($field['type'] == "number")?(buildNumberAttributes($field)):"",
	// 	// 			htmlSanitize($field['id']),
	// 	// 			htmlSanitize($field['class']),
	// 	// 			// (!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
	// 	// 			//true/false type attributes
	// 	// 			(strtoupper($field['required']) == "TRUE")?"required":"",
	// 	// 			(strtoupper($field['readonly']) == "TRUE")?"readonly":"",
	// 	// 			(strtoupper($field['disabled']) == "TRUE")?"disabled":""
	// 	// 		);
	// 	// 	}

	// 	// 	if(isset($field['help']) && $field['help']){

	// 	// 		list($helpType,$helpValue) = explode('|', $field['help'], 2);
	// 	// 		$helpType = trim($helpType);

	// 	// 		switch($helpType){
	// 	// 			case 'text':
	// 	// 				$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-placement="right" data-content="%s"> <i class="fa fa-question-circle"></i> </a>', $helpValue);
	// 	// 				break;
	// 	// 			case 'html':
	// 	// 				$output .= sprintf(' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-html="true" data-placement="right" data-trigger="hover" data-content="%s"><i class="fa fa-question-circle"></i></a>', $helpValue);
	// 	// 				break;
	// 	// 			case 'web':
	// 	// 				$output .= sprintf(' <a class="creatorFormHelp" href="%s" target="_blank" style="target-new: tab;"> <i class="fa fa-question-circle"></i> </a>', $helpValue);
	// 	// 				// $output .= sprintf(' <a href="javascript:;" title="Click for help" class="icon-question-sign" onclick="$(\'#helpModal_%s\').modal(\'show\');"></a>', $field['id']);
	// 	// 				// $output .= sprintf('<div id="helpModal_%s" rel="modal" class="modal hide fade" data-show="false">', $field['id']);
	// 	// 				// $output .= '	<div class="modal-header">';
	// 	// 				// $output .= '		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
	// 	// 				// $output .= '		<h3 id="myModalLabel">Field Help</h3>';
	// 	// 				// $output .= '	</div>';
	// 	// 				// $output .= '	<div class="modal-body">';
	// 	// 				// $output .= sprintf('		<iframe src="%s" seamless="seamless" style="width: 100%%; height: 100%%;"></iframe>', $helpValue);
	// 	// 				// $output .= '	</div>';
	// 	// 				// $output .= '</div>';
	// 	// 				break;
	// 	// 		}
	// 	// 	}

	// 	// 	$output .= "</div>";
	// 	// }

	// 	// if (!isempty($currentFieldset)) {
	// 	// 	$output .= "</fieldset>";
	// 	// }

	// 	$output .= sprintf('<input type="submit" value="%s" name="%s" id="objectSubmitBtn" class="btn" />',
	// 		(isnull($objectID))?htmlSanitize($form["submitButton"]):htmlSanitize($form["updateButton"]),
	// 		$objectID ? "updateForm" : "submitForm"
	// 	);

	// 	// Display a delete link on updates to metadate forms
	// 	if (!isnull($objectID) && forms::isMetadataForm($formID)) {
	// 		$output .= sprintf('<a href="%sdata/metadata/edit/delete/?objectID=%s&formID=%s" id="delete_metadata_link"><i class="fa fa-trash"></i>Delete</a>',
	// 			localvars::get('siteRoot'),
	// 			$objectID,
	// 			$formID
	// 			);
	// 	}

	// 	if(isset($formHasFiles) and $formHasFiles){
	// 		$output .= '<div class="alert alert-info" id="objectSubmitProcessing">
	// 						<strong>Processing Files</strong>

	// 						<br>Please Wait... <i class="fa fa-refresh fa-spin fa-2x"></i>
	// 					</div>';
	// 	}

	// 	$output .= "</form>";

	// 	return $output;

	// }

    // public static function build($formID, $objectID = NULL, $error = FALSE) {
    //     // Fetch the form details
    //     $form = forms::get($formID);
    //     if ($form === FALSE) {
    //         $error = "Invalid form ID.";
    //         return FALSE;
    //     }

    //     // Sort the fields
    //     $fields = forms::sortFields($form['fields']);
    //     if ($fields === FALSE) {
    //         $error = "Error sorting form fields.";
    //         return FALSE;
    //     }

    //     // Fetch the object details if available
    //     $object = forms::getObject($objectID, $error);
    //     // if (!$object) return FALSE;
    //     if ($object === FALSE) {
    //         return FALSE;
    //     }

    //     // Initialize form
    //     $output = self::initializeForm($formID, $objectID);

    //     // Add hidden fields
    //     $output .= self::addHiddenFields($objectID);

    //     // Handle public release
    //     $output .= self::handlePublicRelease($form, $objectID, $object);

    //     // Build fields
    //     $output .= self::buildFields($fields, $object, $error);

    //     // Add submit button and other footer elements
    //     $output .= self::addFooter($form, $formID, $objectID);

    //     // Close form
    //     $output .= self::closeForm();

    //     return $output;
    // }  

    // public static function build($formID, $objectID, &$error) {
    //     // Fetch the form details
    //     $form = forms::get($formID);
    //     if ($form === FALSE) {
    //         $error = "Invalid form ID.";
    //         return FALSE;
    //     }

    //     // Fetch the object details if available
    //     $object = !isnull($objectID) ? objects::get($objectID) : NULL;

    //     // Begin building the form output
    //     $output = '<form action="" method="post">'; // Adjust action attribute as needed

    //     // Loop through each field in the form
    //     foreach ($form['fields'] as $field) {
    //         // Process each field type accordingly
    //         switch ($field['type']) {
    //             case 'textarea':
    //             case 'wysiwyg':
    //                 $output .= self::buildTextarea($field, $object);
    //                 break;
    //             case 'checkbox':
    //             case 'radio':
    //                 $output .= self::buildCheckboxRadio($field);
    //                 break;
    //             case 'select':
    //                 $output .= self::buildSelect($field, $object);
    //                 break;
    //             case 'multiselect':
    //                 $output .= self::buildMultiSelect($field, $object);
    //                 break;
    //             case 'file':
    //                 $output .= self::buildFileInput($field, $objectID);
    //                 break;
    //             default:
    //                 $output .= self::buildInput($field, $object);
    //                 break;
    //         }
    //     }

    //     // Add the submit button
    //     $output .= sprintf('<input type="submit" value="%s" name="%s" id="objectSubmitBtn" class="btn" />',
    //         (isnull($objectID)) ? htmlSanitize($form["submitButton"]) : htmlSanitize($form["updateButton"]),
    //         $objectID ? "updateForm" : "submitForm"
    //     );

    //     // Display a delete link on updates to metadata forms
    //     if (!isnull($objectID) && forms::isMetadataForm($formID)) {
    //         $output .= sprintf('<a href="%sdata/metadata/edit/delete/?objectID=%s&formID=%s" id="delete_metadata_link"><i class="fa fa-trash"></i>Delete</a>',
    //             localvars::get('siteRoot'),
    //             $objectID,
    //             $formID
    //         );
    //     }

    //     // Display processing files message if applicable
    //     if (isset($formHasFiles) && $formHasFiles) {
    //         $output .= '<div class="alert alert-info" id="objectSubmitProcessing">';
    //         $output .= '<strong>Processing Files</strong>';
    //         $output .= '<br>Please Wait... <i class="fa fa-refresh fa-spin fa-2x"></i>';
    //         $output .= '</div>';
    //     }

    //     $output .= '</form>';

    //     return $output;
    // }     

    private static function getSortedFields($fields) {
        return forms::sortFields($fields);
    }

    private static function getObjectData($objectID, $error) {
        return forms::getObject($objectID, $error);
    }

    private static function initializeForm($formID, $objectID) {
        $engine = EngineAPI::singleton();

		$output = sprintf(
			'<form action="%s?formID=%s%s" method="%s" name="insertForm" data-formid="%s">',
			$_SERVER['PHP_SELF'],
			htmlSanitize($formID),
			(!is_null($objectID)) ? '&objectID=' . $objectID : "",
			"post",
			mfcs::$engine->openDB->escape($formID)
        ) . sessionInsertCSRF();
    }

    private static function addHiddenFields($objectID) {
        $engine = EngineAPI::singleton();
        $output = "";

        if (isset($engine->cleanGet['HTML']['parentID'])) {
            $output .= sprintf(
                '<input type="hidden" name="parentID" value="%s">',
                $engine->cleanGet['HTML']['parentID']
            );
        }

        if (!empty(localvars::get("lockID"))) {
            $output .= sprintf(
                '<input type="hidden" name="lockID" value="%s">',
                localvars::get("lockID")
            );
        }   

        return $output;
    }

    private static function handlePublicRelease($form, $objectID, $object) {
        $output = "";

        if ($form['objPublicReleaseShow'] == 1 && $form['metadata'] == 0) {
            $objPublicReleaseDefaultTrueYes = forms::publicReleaseObjSelect($objectID, $object, $form);
            $output .= '<label for="publicReleaseObj">Release to Public:</label>';
            $output .= '<select name="publicReleaseObj" id="publicReleaseObj">';
            $output .= sprintf(
                '<option value="yes" %s>Yes</option>',
                $objPublicReleaseDefaultTrueYes ? "selected" : ""
            );
            $output .= sprintf(
                '<option value="no" %s>No</option>',
                !$objPublicReleaseDefaultTrueYes ? "selected" : ""
            );
            $output .= '</select>';
        }
        return $output;
    }

    private static function buildFields($fields, $object, $error) {
        $output = "";
		$currentFieldset = "";

		foreach ($fields as $field) {

			if ($field['type'] == "fieldset") {
				continue;
			}
			if ($field['type'] == "idno" && (strtolower($field['managedBy']) == "system" && isnull($objectID))) {
				continue;
			}

			// deal with field sets
            $output .= self::handleFieldset($field, $currentFieldset);
            
            // not sure if this is needed
            $currentFieldset = $field['fieldset'];            

            // not sure if this is needed
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

            $output .= self::processField($field, $object, $error);
		}

        if ($currentFieldset != "") {
            $output .= "</fieldset>";
        }

        return $output;
    }

    private static function handleFieldset($field, $currentFieldset) {
        $output = "";
        if ($field['fieldset'] != $currentFieldset) {
            if ($currentFieldset != "") {
                $output .= "</fieldset>";
            }
            if (!isempty($field['fieldset'])) {
                $output .= sprintf('<fieldset><legend>%s</legend>', $field['fieldset']);
            }
        }
        return $output;
    }

    // private static function processField($field, $object, $error) {
    //     // build the actual input box
    //     $output .= '<div class="formCreator dataEntry">';

    //     // Handle disabled and Read Only on insert form
    //     self::applyFieldSettings($field, $object['ID']);

    //     $output .= self::addLabel($field);

    //     if ($field['type'] == "textarea" || $field['type'] == "wysiwyg") {
    //         $output .= sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s %s" %s %s %s %s>%s</textarea>',
    //             htmlSanitize($field['name']),
    //             htmlSanitize($field['placeholder']),
    //             htmlSanitize($field['id']),
    //             htmlSanitize($field['class']),
    //             ($field['type'] == "wysiwyg" ? "wysiwyg" : ""),
    //             (!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
    //             //true/false type attributes
    //             (strtoupper($field['required']) == "TRUE")?"required":"",
    //             (strtoupper($field['readonly']) == "TRUE")?"readonly":"",
    //             (strtoupper($field['disabled']) == "TRUE")?"disabled":"",
    //             forms::getFieldValue($field,(isset($object))?$object:NULL)
    //         );

    //         if ($field['type'] == "wysiwyg") {
    //             $output .= sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%sincludes/js/CKEditor/"</script>',
    //                 localvars::get("siteRoot")
    //             );
    //             $output .= sprintf('<script type="text/javascript" src="%sincludes/js/CKEditor/ckeditor.js"></script>',
    //                 localvars::get("siteRoot")
    //             );
    //             $output .= '<script type="text/javascript">$(function(){';
    //             $output .= sprintf('if (CKEDITOR.instances["%s"]){ CKEDITOR.remove(CKEDITOR.instances["%s"]); }',
    //                 htmlSanitize($field['id']),
    //                 htmlSanitize($field['id'])
    //             );
    //             $output .= sprintf(' CKEDITOR.replace("%s"); ',
    //                 htmlSanitize($field['id'])
    //             );

    //             $output .= 'htmlParser = "";';
    //             $output .= '';
    //             $output .= sprintf('if(CKEDITOR.instances["%s"].dataProcessor){ CKEDITOR.instances["%s"].dataProcessor.htmlFilter;}',
    //                 $field['name'],
    //                 htmlSanitize($field['id'])
    //             );

    //             $output .= '});</script>';

    //         }

    //     }
    //     else if ($field['type'] == "checkbox" || $field['type'] == "radio") {

    //         if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
    //             return FALSE;
    //         }

    //         $output .= sprintf('<div data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
    //             $field['type'],
    //             $formID,
    //             htmlSanitize($field['name']),
    //             (isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
    //         );


    //         $output .= forms::drawFieldChoices($field,$fieldChoices);

    //         $output .= '</div>';

    //     }
    //     else if ($field['type'] == "select") {

    //         if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
    //             return FALSE;
    //         }

    //         $output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
    //             htmlSanitize($field['name']),
    //             htmlSanitize($field['name']),
    //             $field['type'],
    //             $formID,
    //             htmlSanitize($field['name']),
    //             (isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':""
    //         );

    //         $output .= forms::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL);

    //         $output .= "</select>";

    //     }
    //     // else if ($field['type'] == "select") {

    //     // 	if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
    //     // 		if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
    //     // 			return FALSE;
    //     // 		}

    //     // 		$output .= sprintf('<select name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>%s</select>',
    //     // 			htmlSanitize($field['name']),
    //     // 			htmlSanitize($field['name']),
    //     // 			$field['type'],
    //     // 			$formID,
    //     // 			htmlSanitize($field['name']),
    //     // 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
    //     // 			self::drawFieldChoices($field,$fieldChoices,(isset($object['data'][$field['name']]))?$object['data'][$field['name']]:NULL)
    //     // 		);
    //     // 	}
    //     // 	else {
    //     // 		$output .= sprintf('<input type="hidden" name="%s" id="%s" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
    //     // 			htmlSanitize($field['name']),
    //     // 			htmlSanitize($field['name']),
    //     // 			$field['type'],
    //     // 			$formID,
    //     // 			htmlSanitize($field['name']),
    //     // 			(isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
    //     // 			htmlSanitize($field['name'])
    //     // 		);

    //     // 		$output .= sprintf("<script charset=\"utf-8\">
    //     // 				$(function() {
    //     // 					$('#%s')
    //     // 						.select2({
    //     // 							minimumResultsForSearch: 10,
    //     // 							placeholder: 'Make a Selection',
    //     // 							ajax: {
    //     // 								url: 'retrieveOptions.php',
    //     // 								dataType: 'json',
    //     // 								quietMillis: 300,
    //     // 								data: function(term, page) {
    //     // 									return {
    //     // 										q: term,
    //     // 										page: page,
    //     // 										pageSize: 1000,
    //     // 										formID: '%s',
    //     // 										fieldName: '%s'
    //     // 									};
    //     // 								},
    //     // 								results: function(data, page) {
    //     // 									var more = (page * data.pageSize) < data.total;

    //     // 									return {
    //     // 										results: data.options,
    //     // 										more: more
    //     // 									};
    //     // 								},
    //     // 							},
    //     // 							// initSelection: function(element, callback) {

    //     // 					  //           var id = $(element).val();
    //     // 					  //           if(id !== '') {
    //     // 					  //           	$.ajax('retrieveSingleOption.php', {
    //     // 					  //           		data: function() {
    //     // 					  //           			return {
    //     // 					  //           				formID: '%s',
    //     // 					  //           				id: id
    //     // 					  //           			};
    //     // 					  //           		},
    //     // 					  //                   dataType: 'json'
    //     // 					  //               }).done(function(data) {
    //     // 					  //                   callback(data.results[0]);
    //     // 					  //               });
    //     // 					  //           }
    //     // 					  //       }
    //     // 						});
    //     // 					// $('#%s').select2( 'val', '%s' );
    //     // 				});

    //     // 			</script>",
    //     // 			htmlSanitize($field['name']),
    //     // 			htmlSanitize($field['choicesForm']),
    //     // 			htmlSanitize($field['choicesField']),
    //     // 			$object['data'][$field['name']]
    //     // 		);
    //     // 	}

    //     // }
    //     else if ($field['type'] == 'multiselect') {


    //         $output .= '<div class="multiSelectContainer">';
    //         $output .= sprintf('<select name="%s[]" id="%s" size="5" multiple="multiple">',
    //             htmlSanitize($field['name']),
    //             htmlSanitize(str_replace("/","_",$field['name']))
    //         );

    //         if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {

    //             foreach ($object['data'][$field['name']] as $selectedItem) {
    //                 $tmpObj  = objects::get($selectedItem, true);
    //                 $output .= sprintf('<option value="%s">%s</option>',
    //                     htmlSanitize($selectedItem),
    //                     htmlSanitize($tmpObj['data'][$field['choicesField']])
    //                 );

    //                 // if the temp object is false then we have a problem
    //                 // if($tmpObj === false){
    //                 // 	errorHandle::newError("Can't get Object for Metadata Object", errorHandle::DEBUG);
    //                 // }
    //             }
    //         }

    //         $output .= '</select><br />';

    //         if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
    //             if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
    //                 return FALSE;
    //             }

    //             $output .= sprintf('<select name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s onchange="addItemToID(\'%s\', this.options[this.selectedIndex]);">%s</select>',
    //                 htmlSanitize(str_replace("/","_",$field['name'])),
    //                 htmlSanitize($field['name']),
    //                 $field['type'],
    //                 $formID,
    //                 htmlSanitize($field['name']),
    //                 (isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
    //                 htmlSanitize(str_replace("/","_",$field['name'])),
    //                 forms::drawFieldChoices($field,$fieldChoices)
    //             );
    //         }
    //         else {
    //             $output .= sprintf('<input type="hidden" name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
    //                 htmlSanitize($field['name']),
    //                 htmlSanitize(str_replace("/","_",$field['name'])),
    //                 $field['type'],
    //                 $formID,
    //                 htmlSanitize($field['name']),
    //                 (isset($field['choicesForm']) && !isempty($field['choicesForm']))?'data-choicesForm="'.$field['choicesForm'].'"':"",
    //                 htmlSanitize($field['name'])
    //             );

    //             $output .= sprintf("<script charset=\"utf-8\">
    //                     $(function() {
    //                         $('#%s_available')
    //                             .select2({
    //                                 minimumResultsForSearch: 10,
    //                                 placeholder: 'Make a Selection',
    //                                 ajax: {
    //                                     url: 'retrieveOptions.php',
    //                                     dataType: 'json',
    //                                     quietMillis: 300,
    //                                     async: true,
    //                                     data: function(term, page) {
    //                                         return {
    //                                             q: term,
    //                                             page: page,
    //                                             pageSize: 1000,
    //                                             formID: '%s',
    //                                             fieldName: '%s'
    //                                         };
    //                                     },
    //                                     results: function(data, page) {
    //                                         var more = (page * data.pageSize) < data.total;
    //                                         return {
    //                                             results: data.options,
    //                                             more: more
    //                                         };
    //                                     },
    //                                 },
    //                             })
    //                             .on('select2-selecting', function(e) {
    //                                 addToID('%s', e.val, e.choice.text);
    //                                 console.log(%s);
    //                             });
    //                     });
    //                 </script>",
    //                 htmlSanitize(str_replace("/","_",$field['name'])),
    //                 htmlSanitize($field['choicesForm']),
    //                 htmlSanitize($field['choicesField']),
    //                 htmlSanitize(str_replace("/","_",$field['name'])),
    //                 htmlSanitize(str_replace("/","_",$field['name']))
    //             );
    //         }

    //         $output .= "<br />";
    //         $output .= sprintf('<button type="button" onclick="removeFromList(\'%s\')" class="btn">Remove Selected</button>',
    //             htmlSanitize(htmlSanitize(str_replace("/","_",$field['name'])))
    //             );

    //         $output .= "</div>";
    //     }
    //     else if ($field['type'] == 'file') {
    //         $formHasFiles = true;
    //         $output .= '<div style="display: inline-block;">';
    //         if(!isnull($objectID)){
    //             $output .= empty($object['data'][ $field['name'] ])
    //                 ? '<span style="color: #666;font-style: italic;">No file uploaded</span><br>'
    //                 : '<a href="javascript:;" onclick="$(\'#filesTab\').click();">Click to view files tab</a><br>';
    //         }
    //         $uploadID = md5($field['name'].mt_rand());
    //         $output .= sprintf('<div class="fineUploader" data-multiple="%s" data-upload_id="%s" data-allowed_extensions="%s" style="display: inline-block;"></div><input type="hidden" name="%s" value="%s">',
    //             htmlSanitize($field['multipleFiles']),
    //             $uploadID,
    //             htmlSanitize(implode(',',$field['allowedExtensions'])),
    //             htmlSanitize($field['name']),
    //             $uploadID);
    //         $output .= '</div>';
    //     }
    //     else {

    //         // populate the idno field
    //         if ($field['type'] == "idno") {
    //             $field['type'] = "text";
    //             if (isset($object) && !isset($object['data'][$field['name']])) $object['data'][$field['name']] = $object['idno'];

    //             // the IDNO is managed by the user. It shouldn't be set to read only
    //             if (isset($field['managedBy']) && strtolower($field['managedBy']) != "system") {
    //                 $field['readonly'] = "false";
    //             }
    //             else {
    //                 // just in case ...
    //                 $field['readonly'] = "true";
    //             }

    //         }

    //         // get the field value, if the object exists
    //         $fieldValue = forms::getFieldValue($field,(isset($object))?$object:NULL);


    //         $output .= sprintf('<input type="%s" name="%s" value="%s" placeholder="%s" %s id="%s" class="%s" %s %s %s />',
    //             htmlSanitize($field['type']),
    //             htmlSanitize($field['name']),
    //             $fieldValue,
    //             htmlSanitize($field['placeholder']),
    //             //for numbers
    //             ($field['type'] == "number")?(buildNumberAttributes($field)):"",
    //             htmlSanitize($field['id']),
    //             htmlSanitize($field['class']),
    //             // (!isempty($field['style']))?'style="'.htmlSanitize($field['style']).'"':"",
    //             //true/false type attributes
    //             (strtoupper($field['required']) == "TRUE")?"required":"",
    //             (strtoupper($field['readonly']) == "TRUE")?"readonly":"",
    //             (strtoupper($field['disabled']) == "TRUE")?"disabled":""
    //         );
    //     }

    //     if (isset($field['help']) && $field['help']) {
    //         $output .= self::addHelp($field);
    //     }        

    //     $output .= "</div>";

    //     return $output;
    // }

    private static function processField($field, $object, $error) {
        $output = '<div class="formCreator dataEntry">';

        self::applyFieldSettings($field, $object['ID']);
        $output .= self::addLabel($field);

        switch ($field['type']) {
            case "textarea":
            case "wysiwyg":
                $output .= self::buildTextarea($field, $object);
                break;
            case "checkbox":
            case "radio":
                $output .= self::buildCheckboxRadio($field);
                break;
            case "select":
                $output .= self::buildSelect($field, $object);
                break;
            case "multiselect":
                $output .= self::buildMultiSelect($field, $object);
                break;
            case "file":
                $output .= self::buildFileInput($field, $object['ID']);
                break;
            default:
                $output .= self::buildInput($field, $object);
                break;
        }

        if (isset($field['help']) && $field['help']) {
            $output .= self::addHelp($field);
        }

        $output .= '</div>';

        return $output;
    }

    private static function applyFieldSettings(&$field, $objectID) {
        if (isset($field['disabledInsert']) && $field['disabledInsert'] == "true" && isnull($objectID)) {
            $field['disabled'] = "true";
        }

        if (isset($field['disabledUpdate']) && $field['disabledUpdate'] == "true" && !isnull($objectID)) {
            $field['readonly'] = "true";
        }
    }

    private static function addLabel($field) {
        if ($field['type'] != "idno" || (isset($field['managedBy']) && strtolower($field['managedBy']) != "system") || !isnull($objectID)) {
            return sprintf('<label for="%s" class="formLabel %s">%s:</label>',
                htmlSanitize($field['id']),
                (strtolower($field['required']) == "true") ? "requiredField" : "",
                htmlSanitize($field['label'])
            );
        }
        return "";
    }

    private static function buildTextarea($field, $object) {
        $output = sprintf('<textarea name="%s" placeholder="%s" id="%s" class="%s %s" %s %s %s %s>%s</textarea>',
            htmlSanitize($field['name']),
            htmlSanitize($field['placeholder']),
            htmlSanitize($field['id']),
            htmlSanitize($field['class']),
            ($field['type'] == "wysiwyg" ? "wysiwyg" : ""),
            (!isempty($field['style'])) ? 'style="' . htmlSanitize($field['style']) . '"' : "",
            (strtoupper($field['required']) == "TRUE") ? "required" : "",
            (strtoupper($field['readonly']) == "TRUE") ? "readonly" : "",
            (strtoupper($field['disabled']) == "TRUE") ? "disabled" : "",
            self::getFieldValue($field, $object)
        );

        if ($field['type'] == "wysiwyg") {
            $output .= self::addWysiwygScripts($field);
        }

        return $output;
    }

    private static function addWysiwygScripts($field) {
        return sprintf('<script type="text/javascript">window.CKEDITOR_BASEPATH="%sincludes/js/CKEditor/"</script>', localvars::get("siteRoot")) .
            sprintf('<script type="text/javascript" src="%sincludes/js/CKEditor/ckeditor.js"></script>', localvars::get("siteRoot")) .
            '<script type="text/javascript">$(function(){' .
            sprintf('if (CKEDITOR.instances["%s"]){ CKEDITOR.remove(CKEDITOR.instances["%s"]); }', htmlSanitize($field['id']), htmlSanitize($field['id'])) .
            sprintf(' CKEDITOR.replace("%s"); ', htmlSanitize($field['id'])) .
            '});</script>';
    }

    private static function buildCheckboxRadio($field) {
        $output = "";
        if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
            return FALSE;
        }

        $output .= sprintf('<div data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
            $field['type'],
            $formID,
            htmlSanitize($field['name']),
            (isset($field['choicesForm']) && !isempty($field['choicesForm'])) ? 'data-choicesForm="' . $field['choicesForm'] . '"' : ""
        );

        $output .= forms::drawFieldChoices($field, $fieldChoices);
        $output .= '</div>';

        return $output;
    }

    private static function buildSelect($field, $object) {
        if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
            return FALSE;
        }

        $output = sprintf('<select name="%s" id="%s" class="%s" %s %s %s>',
            htmlSanitize($field['name']),
            htmlSanitize($field['id']),
            htmlSanitize($field['class']),
            (!isempty($field['style'])) ? 'style="' . htmlSanitize($field['style']) . '"' : "",
            (strtoupper($field['required']) == "TRUE") ? "required" : "",
            (strtoupper($field['disabled']) == "TRUE") ? "disabled" : ""
        );

        $output .= forms::drawFieldChoices($field, $fieldChoices);
        $output .= '</select>';

        return $output;
    }

    private static function buildMultiSelect($field, $object) {
        // echo "<pre>";
        // var_dump($field);
        // echo "</pre>";
        // die();

        $output = '<div class="multiSelectContainer">';
        
        // Generate the main select element
        $output .= sprintf(
            '<select name="%s[]" id="%s" size="5" multiple="multiple">',
            htmlSanitize($field['name']),
            htmlSanitize(str_replace("/", "_", $field['name']))
        );
    
        // Populate the select element with the selected items
        if (isset($object['data'][$field['name']]) && is_array($object['data'][$field['name']])) {
            foreach ($object['data'][$field['name']] as $selectedItem) {
                $tmpObj = objects::get($selectedItem, true);
                $output .= sprintf(
                    '<option value="%s">%s</option>',
                    htmlSanitize($selectedItem),
                    htmlSanitize($tmpObj['data'][$field['choicesField']])
                );
            }
        }
    
        $output .= '</select><br />';
    
        // Handle the available choices
        if (isset($field['choicesType']) && !isempty($field['choicesType']) && $field['choicesType'] == "manual") {
            if (($fieldChoices = forms::getFieldChoices($field)) === FALSE) {
                return FALSE;
            }
    
            $output .= sprintf(
                '<select name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s onchange="addItemToID(\'%s\', this.options[this.selectedIndex]);">%s</select>',
                htmlSanitize(str_replace("/", "_", $field['name'])),
                htmlSanitize($field['name']),
                $field['type'],
                self::getFormID($field),
                htmlSanitize($field['name']),
                (isset($field['choicesForm']) && !isempty($field['choicesForm'])) ? 'data-choicesForm="' . $field['choicesForm'] . '"' : "",
                htmlSanitize(str_replace("/", "_", $field['name'])),
                forms::drawFieldChoices($field, $fieldChoices)
            );
        } else {
            $output .= sprintf(
                '<input type="hidden" name="%s_available" id="%s_available" data-type="%s" data-formid="%s" data-fieldname="%s" %s>',
                htmlSanitize($field['name']),
                htmlSanitize(str_replace("/", "_", $field['name'])),
                $field['type'],
                self::getFormID($field),
                htmlSanitize($field['name']),
                (isset($field['choicesForm']) && !isempty($field['choicesForm'])) ? 'data-choicesForm="' . $field['choicesForm'] . '"' : ""
            );
    
            $output .= sprintf(
                "<script charset=\"utf-8\">
                    $(function() {
                        $('#%s_available').select2({
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
                                }
                            }
                        }).on('select2-selecting', function(e) {
                            addToID('%s', e.val, e.choice.text);
                        });
                    });
                </script>",
                htmlSanitize(str_replace("/", "_", $field['name'])),
                htmlSanitize($field['choicesForm']),
                htmlSanitize($field['choicesField']),
                htmlSanitize(str_replace("/", "_", $field['name']))
            );
        }
    
        $output .= "<br />";
        $output .= sprintf(
            '<button type="button" onclick="removeFromList(\'%s\')" class="btn">Remove Selected</button>',
            htmlSanitize(str_replace("/", "_", $field['name']))
        );
    
        $output .= "</div>";
        return $output;
    }

    private static function getFormID($field) {
        // Check if the $field has the formID set
        if (isset($field['formID'])) {
            return htmlSanitize($field['formID']); // Sanitize if necessary
        } else {
            // Otherwise, get it from the URL parameter
            return isset($_GET['formID']) ? htmlSanitize($_GET['formID']) : null;
        }
    }

    private static function buildFileInput($field, $objectID) {
        $output = '<div style="display: inline-block;">';
        if (!is_null($objectID)) {
            $output .= empty($object['data'][$field['name']])
                ? '<span style="color: #666;font-style: italic;">No file uploaded</span><br>'
                : '<a href="javascript:;" onclick="$(\'#filesTab\').click();">Click to view files tab</a><br>';
        }
        $uploadID = md5($field['name'] . mt_rand());
        $output .= sprintf('<div class="fineUploader" data-multiple="%s" data-upload_id="%s" data-allowed_extensions="%s" style="display: inline-block;"></div><input type="hidden" name="%s" value="%s">',
            htmlSanitize($field['multipleFiles']),
            $uploadID,
            htmlSanitize(implode(',', $field['allowedExtensions'])),
            htmlSanitize($field['name']),
            $uploadID
        );
        $output .= '</div>';
        return $output;
    }

    private static function generateFileList($formID, $objectID, $field) {
        // Placeholder function to simulate file list generation
        // Replace this with your actual logic for generating the file list
        return sprintf('<p>Files for form %s and object %s.</p>', $formID, $objectID);
    }

    private static function buildInput($field, $object) {
        return sprintf('<input type="%s" name="%s" placeholder="%s" id="%s" class="%s" %s %s %s %s %s value="%s">',
            htmlSanitize($field['type']),
            htmlSanitize($field['name']),
            htmlSanitize($field['placeholder']),
            htmlSanitize($field['id']),
            htmlSanitize($field['class']),
            (!isempty($field['style'])) ? 'style="' . htmlSanitize($field['style']) . '"' : "",
            (strtoupper($field['required']) == "TRUE") ? "required" : "",
            (strtoupper($field['readonly']) == "TRUE") ? "readonly" : "",
            (strtoupper($field['disabled']) == "TRUE") ? "disabled" : "",
            (strtoupper($field['disabled']) == "TRUE") ? "disabled" : "",
            self::getFieldValue($field, $object)
        );
    }

    private static function addHelp($field) {
        $output = "";
    
        if (isset($field['help']) && $field['help']) {
            list($helpType, $helpValue) = explode('|', $field['help'], 2);
            $helpType = trim($helpType);
    
            switch ($helpType) {
                case 'text':
                    $output .= sprintf(
                        ' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-placement="right" data-content="%s"> <i class="fa fa-question-circle"></i> </a>',
                        htmlSanitize($helpValue)
                    );
                    break;
                case 'html':
                    $output .= sprintf(
                        ' <a class="creatorFormHelp" href="javascript:;" rel="popover" data-html="true" data-placement="right" data-trigger="hover" data-content="%s"><i class="fa fa-question-circle"></i></a>',
                        htmlSanitize($helpValue)
                    );
                    break;
                case 'web':
                    $output .= sprintf(
                        ' <a class="creatorFormHelp" href="%s" target="_blank" style="target-new: tab;"> <i class="fa fa-question-circle"></i> </a>',
                        htmlSanitize($helpValue)
                    );
                    break;
            }
        }
    
        return $output;
    }

    private static function getFieldValue($field, $object) {
        // Check if the object has data for the field
        if (isset($object['data'][$field['name']])) {
            // If the field's value is present in the object data, use it
            $value = $object['data'][$field['name']];
        } else {
            // If not, use the field's default value
            $value = isset($field['default']) ? $field['default'] : "";
        }
    
        return htmlSanitize($value);
    }  

    private static function addFooter($form, $formID, $objectID) {
        $output = sprintf(
            '<input type="submit" value="%s" name="%s" id="objectSubmitBtn" class="btn" />',
            (isnull($objectID)) ? htmlSanitize($form["submitButton"]) : htmlSanitize($form["updateButton"]),
            $objectID ? "updateForm" : "submitForm"
        );

        // Display a delete link on updates to metadata forms
        if (!isnull($objectID) && forms::isMetadataForm($formID)) {
            $output .= sprintf(
                '<a href="%sdata/metadata/edit/delete/?objectID=%s&formID=%s" id="delete_metadata_link"><i class="fa fa-trash"></i>Delete</a>',
                localvars::get('siteRoot'),
                $objectID,
                $formID
            );
        }

        if (isset($formHasFiles) && $formHasFiles) {
            $output .= '<div class="alert alert-info" id="objectSubmitProcessing">';
            $output .= '<strong>Processing Files</strong>';
            $output .= '<br>Please Wait... <i class="fa fa-refresh fa-spin fa-2x"></i>';
            $output .= '</div>';
        }

        return $output;
    }

    private static function closeForm() {
        return '<div><input type="submit" value="Save"></div></form>';
    }
}
?>
