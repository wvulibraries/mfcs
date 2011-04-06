<?php
function listFields($fields,$display) {
	global $engine;

	$idFieldName = "mfcs_ID";
	$complexForm = FALSE;

	$listObj = new listManagement($engine,$engine->localVars("formName"));
	$listObj->primaryKey = $idFieldName;
	// $listObj->debug = TRUE;

	if ($display == 'updateinsert') {
		$listObj->updateInsert   = TRUE;
		$listObj->updateInsertID = $idFieldName;
	}

	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));
	
	$sql = sprintf("SELECT deletions FROM %s WHERE projectID='%s' AND formName='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID")),
		$engine->openDB->escape($engine->localVars("formName"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['result']) {
		$row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM);
		$listObj->deleteBox = (bool)$row[0];
	}
	

	$options = array();
	$options['field'] = $idFieldName;
	$options['label'] = $idFieldName;
	$options['type']  = "hidden";
	if ($display != 'insert') {
		
		if (isset($engine->cleanGet['MYSQL']['id'])) {
			
			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));
			
			$sql = sprintf("SELECT %s FROM %s WHERE %s='%s' LIMIT 1",
				$engine->openDB->escape($options['field']),
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($listObj->updateInsertID),
				$engine->cleanGet['MYSQL']['id']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['result']) {
				$row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM);
				$options['value'] = $row[0];
			}
			
			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

		}
		
	}
	$listObj->addField($options);
	unset($options);

	foreach ($fields as $field) {

		if ($display == 'update') {
			switch ($field['type']) {
				case 'multiselect':
				case 'wysiwyg':
					$complexForm = TRUE;
					break;
			}
		}

		if ($field['type'] == 'identifier') {
			$field['type'] = 'text';
			$listObj->orderBy = "ORDER BY ".$field['fieldName'];
		}

		$options = array();
		$options['type']     = $field['type'];
		$options['field']    = $field['fieldName'];
		$options['label']    = $field['fieldLabel'];
		$options['size']     = isset($field['size'])?$field['size']:NULL;
		$options['width']    = isset($field['width'])?$field['width']:NULL;
		$options['height']   = isset($field['height'])?$field['height']:NULL;
		$options['dupes']    = isset($field['dupes'])?(bool)$field['dupes']:FALSE;
		$options['blank']    = isset($field['nulls'])?(bool)$field['nulls']:FALSE;
		$options['readonly'] = isset($field['readonly'])?(bool)$field['readonly']:FALSE;
		$options['disabled'] = isset($field['disable'])?(bool)$field['disable']:FALSE;
		$options['validate'] = (isset($field['validation']) && $field['validation']!='')?$field['validation']:NULL;
		$options['original'] = TRUE;

		if ($display == 'updateinsert') {
			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));
			
			$sql = sprintf("SELECT %s FROM %s WHERE %s='%s' LIMIT 1",
				$engine->openDB->escape($field['fieldName']),
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($listObj->updateInsertID),
				$engine->cleanGet['MYSQL']['id']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['result']) {
				$row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM);
				$options['value'] = $row[0];
			}
			
			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));
		}

		if ($options['type'] == 'select' || $options['type'] == 'multiselect') {
			$value = explode("_",$field['optionValues']);

			if (isset($value[1])) {

				$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
					$engine->openDB->escape($engine->dbTables("formFields")),
					$engine->openDB->escape($value[1])
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

				$fieldName = $row['fieldName'];

				// Switch to project database
				$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));
				
				if ($options['type'] == 'select') {
					
					$sql = sprintf("SELECT %s, %s FROM %s ORDER BY %s",
						$engine->openDB->escape($idFieldName),
						$engine->openDB->escape($fieldName),
						$engine->openDB->escape($value[0]),
						$engine->openDB->escape($fieldName)
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult                = $engine->openDB->query($sql);
					
					if ($sqlResult['result']) {
						while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
							$options['options'][] = array("value" => $row[$idFieldName], "label" => $row[$fieldName]);
						}
					}

				}
				
				// Switch to system database
				$engine->openDB->select_db($engine->localVars("dbName"));

				if ($options['type'] == 'multiselect') {

					$options['options']['valueTable']        = $value[0];
					$options['options']['valueDisplayID']    = $idFieldName;
					$options['options']['valueDisplayField'] = $fieldName;
					$options['options']['orderBy']           = $fieldName;
					
					$options['options']['linkTable']         = $engine->localVars("formName")."_link_".$value[0]."_".$fieldName;
					$options['options']['linkValueField']    = $value[0]."ID";
					$options['options']['linkObjectField']   = $engine->localVars("formName")."ID";

					if ($display == 'updateinsert') {
						// Switch to project database
						$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

						$sql = sprintf("SELECT %s FROM %s WHERE %s='%s'",
							$engine->openDB->escape($options['options']['linkValueField']),
							$engine->openDB->escape($options['options']['linkTable']),
							$engine->openDB->escape($options['options']['linkObjectField']),
							$engine->cleanGet['MYSQL']['id']
						);
						$engine->openDB->sanitize = FALSE;
						$sqlResult                = $engine->openDB->query($sql);
						
						if ($sqlResult['result']) {
							$tempList = array();
							while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM)) {
								$tempList[] = $row[0];
							}
							
							$options['options']['select'] = implode(",",$tempList);
						}
						
						// Switch to system database
						$engine->openDB->select_db($engine->localVars("dbName"));
					}

				}

			}
		}

		$listObj->addField($options);
		unset($options);
	}

	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	if ($complexForm == TRUE) {
		// $listObj->deleteBox = FALSE;
		// $listObj->noSubmit  = TRUE;

		$options = array();
		$options['field']    = '<a href="'.$engine->localVars("siteRoot").'displayForm.php?proj='.$engine->localVars("projectID").'&form='.$engine->localVars("formName").'&display=updateinsert&id={'.$idFieldName.'}">Edit</a>';
		$options['label']    = "Edit";
		$options['type']     = "plainText";
		$listObj->addField($options);
		unset($options);

		foreach ($fields as $field) {
			switch($field['type']) {
				case 'multiselect':
				case 'wysiwyg':
					$listObj->removeField($field['fieldName']);
					break;
			}
		}

		$listObj->disableAllFields();
	}

	return $listObj;
}
?>
