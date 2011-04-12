<?php
function defaultValues() {
	$v['fieldName']     = "";
	$v['fieldLabel']    = "[Label]";
	$v['placeHolder']   = "";
	$v['size']          = "40";
	$v['mssize']        = "4";
	$v['width']         = "30";
	$v['height']        = "3";
	$v['dupes']         = "0";
	$v['nulls']         = "0";
	$v['disable' ]      = "0";
	$v['readonly']      = "0";
	$v['plugins']       = "";
	$v['validation']    = "";
	$v['maxlength']     = "255";
	$v['format']        = "abc####";
	$v['reuseids']      = "0";
	$v['autoinc']       = "1";
	$v['managedBy']     = "system";
	$v['optionValues']  = "";
	$v['releasePublic'] = "1";
	$v['searchable']    = "0";
	$v['sortable']      = "0";

	return $v;
}

function showField($id,$type,$fieldID=NULL) {
	global $engine;

	$type = strtolower($type);
	$prefix = $id.'_'.$type;

	// Set Default values
	$values = defaultValues();

	if (!isnull($fieldID)) {
		$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("formFields")),
			$engine->openDB->escape($fieldID)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		
		if ($sqlResult['result']) {
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				$values['originalFieldName'] = $row['fieldName'];
			}
		}
		

		$sql = sprintf("SELECT * FROM %s WHERE fieldID='%s'",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($fieldID)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		
		if ($sqlResult['result']) {
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				// Overwrite defaults with stored values
				$values[$row['option']] = $row['value'];
			}
		}
	}

	$out  = '<span class="labelContainer"><span class="label">'.$values['fieldLabel'].'</span>: </span>';
	$out .= '<input type="hidden" name="'.$prefix.'_original" value="'.(isset($values['originalFieldName'])?$values['originalFieldName']:'').'" />';

	switch($type) {

		case "identifier":
			$out .= '<input type="text" name="'.$prefix.'" id="'.$prefix.'" size="'.$values['size'].'" readonly />';
			return $out;

		case "text":
		case "date":
			$out .= '<input type="text" name="'.$prefix.'" id="'.$prefix.'" size="'.$values['size'].'" placeholder="'.$values['placeHolder'].'"'.(($values['readonly'])?' readonly':'').(($values['disable'])?' disabled':'').' />';
			return $out;

		case "select":
			$out .= '<select name="'.$prefix.'" id="'.$prefix.'"></select>';
			return $out;

		case "multiselect":
			$out .= '<select multiple name="'.$prefix.'_ms" id="'.$prefix.'_ms"></select>';
			$out .= '<br />';
			$out .= '<span class="labelContainer"></span>'; // to maintain padding via js
			$out .= '<select name="'.$prefix.'" id="'.$prefix.'"></select>';
			return $out;

		case "textarea":
			$out .= '<textarea name="'.$prefix.'" id="'.$prefix.'" rows="'.$values['height'].'" cols="'.$values['width'].'" placeholder="'.$values['placeHolder'].'"></textarea>';
			return $out;

		// case "date":
		// 	$out .= '<input type="date" name="'.$prefix.'" id="'.$prefix.'" size="'.$values['size'].'" placeholder="'.$values['placeHolder'].'"'.(($values['readonly'])?' readonly':'').(($values['disable'])?' disabled':'').' />';
		// 	return $out;

		case "wysiwyg":
			$out .= '<textarea name="'.$prefix.'" id="'.$prefix.'" rows="'.$values['height'].'" cols="'.$values['width'].'" class="engineWYSIWYG"></textarea>';
			return $out;

		default: 

	}
}

function fieldList($type) {
	global $engine;

	$type = strtolower($type);
	$fields = array("hidden"=>array(), "required"=>array(), "other"=>array());

	$defaults = defaultValues();

	switch ($type) {
		case "identifier":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			$fields['display']['size']          = $defaults['size'];
			$fields['display']['managedBy']     = $defaults['managedBy'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['reuseids']      = $defaults['reuseids'];
			$fields['display']['format']        = $defaults['format'];
			$fields['display']['autoinc']       = $defaults['autoinc'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			$fields['hidden']['maxlength']      = "10";
			$fields['hidden']['readonly']       = "1";
			$fields['hidden']['autoincCurrent'] = $defaults['autoinc'];
			break;

		case "text":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			// $fields['display']['placeHolder']   = $defaults['placeHolder'];
			$fields['display']['size']          = $defaults['size'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['readonly']      = $defaults['readonly'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['maxlength']     = $defaults['maxlength'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;

		case "date":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			// $fields['display']['placeHolder']   = $defaults['placeHolder'];
			$fields['display']['size']          = $defaults['size'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['readonly']      = $defaults['readonly'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;

		case "select":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			$fields['display']['optionValues']  = $defaults['optionValues'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;

		case "multiselect":
			$fields['hidden']['fieldName']      = "";
			$fields['display']['fieldLabel']    = "";
			$fields['display']['optionValues']  = $defaults['optionValues'];
			$fields['display']['mssize']        = $defaults['mssize'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;

		case "textarea":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			// $fields['display']['placeHolder']   = $defaults['placeHolder'];
			$fields['display']['width']         = $defaults['width'];
			$fields['display']['height']        = $defaults['height'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['readonly']      = $defaults['readonly'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;

		case "wysiwyg":
			$fields['display']['fieldName']     = "";
			$fields['display']['fieldLabel']    = "";
			$fields['display']['plugins']       = $defaults['plugins'];
			$fields['display']['dupes']         = $defaults['dupes'];
			$fields['display']['nulls']         = $defaults['nulls'];
			$fields['display']['readonly']      = $defaults['readonly'];
			$fields['display']['disable']       = $defaults['disable'];
			$fields['display']['validation']    = $defaults['validation'];
			$fields['display']['releasePublic'] = $defaults['releasePublic'];
			$fields['display']['searchable']    = $defaults['searchable'];
			$fields['display']['sortable']      = $defaults['sortable'];
			break;
	}

	return $fields;
}

function fieldProperties($id,$type,$name,$default,$fieldID=NULL,$hidden=FALSE) {
	global $engine;

	$type = strtolower($type);
	$prefix = $id.'_'.$type.'_';
	
	// Set Default values
	$values = defaultValues();
	$values[$name] = $default;

	if (!isnull($fieldID)) {
		$sql = sprintf("SELECT fieldName, type FROM %s WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("formFields")),
			$engine->openDB->escape($fieldID)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

		// Overwrite defaults
		$values['fieldName'] = $row['fieldName'];
		$values['type']      = $row['type'];


		$sql = sprintf("SELECT * FROM %s WHERE fieldID='%s' AND `option`='%s'",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($fieldID),
			$engine->openDB->escape($name)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		
		if ($sqlResult['result']) {
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				// Overwrite defaults with stored values
				$values[$row['option']] = $row['value'];
			}
		}
	}

	if ($hidden) {
		return '<input type="hidden" name="'.$prefix.$name.'" value="'.$values[$name].'" />';
	}

	switch($name) {
		case 'fieldName':
			return array('Field Name','<input type="text" name="'.$prefix.$name.'" placeholder="Unique name for this field." value="'.$values[$name].'" />');
		
		case 'fieldLabel':
			return array('Field Label','<input type="text" name="'.$prefix.$name.'" placeholder="Label to be displayed." value="'.$values[$name].'" />');
		
		// case 'placeHolder': // Not implemented in listManagement
		// 	return array('Placeholder Text','<input type="text" name="'.$prefix.$name.'" value="'.$values[$name].'" />');
		
		case 'size':
			return array('Size','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'" min="0" />');
		
		case 'mssize':
			return array('Multiselect Size','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'" min="4" />');
		
		case 'width':
			return array('Width','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'" />');
		
		case 'height':
			return array('Height','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'" />');
		
		case 'plugins':
			return array('Plugins','<input type="text" name="'.$prefix.$name.'" value="'.$values[$name].'" />');
		
		case 'dupes':
			return array('Allow Duplicates','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');
		
		case 'nulls':
			return array('Allow Empty Values','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');
		
		case 'readonly':
			return array('Read Only','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');
		
		case 'disable':
			return array('Disable','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');
		
		case 'validation':
			return array('Validation','<select name="'.$prefix.$name.'"><option value="">None</option>'.validationOptions($fieldID).'<option value="other">Other</option></select>');

		case 'maxlength':
			return array('Max Length','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'"  min="0" max="255" />');

		case 'format':
			return array('Format','<input type="text" name="'.$prefix.$name.'" value="'.$values[$name].'" /> # = padded number');
		
		case 'reuseids':
			return array('Re-use Deleted IDs','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');

		case 'autoinc':
			return array('Auto Increment Start','<input type="number" name="'.$prefix.$name.'" value="'.$values[$name].'" min="1" />');
		
		case 'managedBy':
			return array('Managed By','<select name="'.$prefix.$name.'"><option value="user">User</option><option value="system"'.(($values[$name]=='system')?' selected':'').'>System</option></select>');
		
		case 'optionValues':
			$sql = sprintf("SELECT value FROM %s AS properties LEFT JOIN %s AS fields ON fields.ID=properties.fieldID WHERE fields.formID='%s' AND fields.position='%s' AND properties.`option`='optionValues' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("formFieldProperties")),
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($engine->localVars("formID")),
				$engine->openDB->escape($id)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

			$selected = explode("_",$row['value']);


			$sql = sprintf("SELECT ID FROM %s WHERE formName='%s' AND projectID='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($engine->localVars("projectID"))
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
			$formID = $row['ID'];


			$sql = sprintf("SELECT props.value FROM %s AS props LEFT JOIN %s AS fields ON props.fieldID=fields.ID WHERE props.option='optionValues' AND fields.formID='%s' AND fields.type='multiselect'",
				$engine->openDB->escape($engine->dbTables("formFieldProperties")),
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($engine->localVars("formID"))
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			$ignored = array();
			if ($sqlResult['result']) {
				while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
					$ignored[] = substr($row['value'],strpos($row['value'],"_")+1);
				}
			}


			$sql = sprintf("SELECT ID, formName FROM %s WHERE projectID='%s'",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("projectID"))
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
						
			$options = NULL;
			if ($sqlResult['result']) {
				while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

					if ($engine->localVars("formID") == $row['ID']) {
						continue;
					}

					$sql = sprintf("SELECT fields.ID, fields.fieldName, value AS fieldLabel FROM %s AS fields LEFT JOIN %s AS properties ON fields.ID=properties.fieldID WHERE formID='%s' AND `option`='fieldLabel'",
						$engine->openDB->escape($engine->dbTables("formFields")),
						$engine->openDB->escape($engine->dbTables("formFieldProperties")),
						$engine->openDB->escape($row['ID'])
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult2               = $engine->openDB->query($sql);

					if ($sqlResult2['result']) {
						$opt = NULL;
						while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {

							if (isset($selected[1]) && $row2['ID'] == $selected[1]) {
								$opt .= '<option value="'.$row['formName'].'_'.$row2['ID'].'" selected="selected">'.$row2['fieldLabel'].'</option>';
							}
							else if (!in_array($row2['ID'],$ignored)) {
								$opt .= '<option value="'.$row['formName'].'_'.$row2['ID'].'">'.$row2['fieldLabel'].'</option>';
							}

						}
					}
					
					if (!isnull($opt)) {
						$options .= '<optgroup label="'.$row['formName'].'">';
						$options .= $opt;
						$options .= '</optgroup>';
					}

				}
			}

			return array('Option Values','<select name="'.$prefix.$name.'"><option value="null">None</option>'.$options.'</select>');

		case 'releasePublic':
			return array('Release to Public','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');

		case 'searchable':
			return array('Searchable','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');

		case 'sortable':
			return array('Sortable','<select name="'.$prefix.$name.'"><option value="0">No</option><option value="1"'.(($values[$name]=='1')?' selected':'').'>Yes</option></select>');
		
	}

	return FALSE;

}

function validationOptions($fieldID=NULL) {
	global $engine;

	$value = NULL;
	if (!isnull($fieldID)) {
		$sql = sprintf("SELECT value FROM %s WHERE fieldID='%s' AND `option`='validation' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($fieldID)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

		$value = $row['value'];
	}


	$listObj = new listManagement($engine,$engine->localVars("formName"));
	
	$options = NULL;
	foreach ($listObj->validateTypes as $type) {
		$options .= '<option value="'.$type.'"'.(($type==$value)?' selected':'').'>'.$type.'</option>';
	}

	return $options;
}
?>
