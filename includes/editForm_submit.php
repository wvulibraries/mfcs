<?php
global $engine;
global $errorMsg;

$submitFields          = array();
$storedFields          = array();
$organizedSubmitFields = array();
$settingsTblElements   = array("releasePublic","searchable","sortable");


$i = 0;
foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
	// ID_type_name
	$post = explode("_",$key);
	
	if (!isset($post[2])) {
		// skip the 'display' field
		continue;
	}
	if (!isset($lastID)) {
		$lastID = $post[0];
	}

	if ($lastID != $post[0]) {
		$submitFields[] = $tmp;
		unset($tmp);
	}

	$tmp['type'] = $post[1];
	if (!isset($tmp['position'])) {
		$tmp['position'] = $i++;
	}
	$tmp[$post[2]] = $value;
	$lastID = $post[0];
}
$submitFields[] = $tmp;

foreach ($submitFields as $I => $field) {
	if ($field['type'] == 'identifier') {
	
		if (isset($field['format'])) {
			
			// Adjust maxlength if neccessary
			if ($field['maxlength'] < strlen($field['format'])) {
				$field['maxlength'] = strlen($field['format']);
			}

			// Create array of previous formats
			$formats = array();
			if (!isset($field['restartNumbering']) || $field['restartNumbering'] == 0) {
				$sql = sprintf("SELECT value FROM %s AS properties LEFT JOIN %s AS fields ON fields.ID=properties.fieldID WHERE formID='%s' AND fieldName='%s' AND `option`='prevFormats' LIMIT 1",
					$engine->openDB->escape($engine->dbTables("formFieldProperties")),
					$engine->openDB->escape($engine->dbTables("formFields")),
					$engine->openDB->escape($engine->localVars("formID")),
					$engine->openDB->escape($field['fieldName'])
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				
				if ($sqlResult['result']) {
					while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM)) {
						$formats = explode("|",$row[0]);
					}
				}
			}

			if (count($formats) == 0 || $formats[count($formats)-1] != $field['format']) {
				$formats[] = $field['format'];
			}

			// Create string of previous formats from array
			$submitFields[$I]['prevFormats'] = $field['prevFormats'] = implode("|",$formats);
		}

		// Switch to project database
		$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

		// Adjust maxlength to accomodate longest stored string
		$sql = sprintf("SELECT MAX(LENGTH(%s)) AS maxLength FROM %s",
			$engine->openDB->escape($field['fieldName']),
			$engine->openDB->escape($engine->localVars("formName"))
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

			if ($field['maxlength'] < $row['maxLength']) {
				$field['maxlength'] = $row['maxLength'];
			}
		}

		// Switch to system database
		$engine->openDB->select_db($engine->localVars("dbName"));

	}
	else if ($field['type'] == 'link') {
		$submitFields[$I]['fieldName'] = $field['fieldName'] = "<a href='".$field['linkURL']."'>".$field['linkLabel']."</a>";
	}

	$organizedSubmitFields[$I]['fieldName'] = $field['fieldName'];
	$organizedSubmitFields[$I]['type']      = $field['type'];
	$organizedSubmitFields[$I]['position']  = $field['position'];
	$organizedSubmitFields[$I]['original']  = $field['original'];
	unset($field['fieldName'],$field['type'],$field['position'],$field['original']);

	$organizedSubmitFields[$I]['properties'] = $field;
}


// Gather stored info
$sql = sprintf("SELECT ID, fieldName, type FROM %s WHERE formID='%s' ORDER BY position",
	$engine->openDB->escape($engine->dbTables("formFields")),
	$engine->openDB->escape($engine->localVars("formID"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$sql = sprintf("SELECT * FROM %s WHERE fieldID='%s'",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($row['ID'])
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult2               = $engine->openDB->query($sql);

		while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
			$storedFields[$row['fieldName']]['fieldID']       = $row['ID'];
			$storedFields[$row['fieldName']]['type']          = $row['type'];
			$storedFields[$row['fieldName']][$row2['option']] = $row2['value'];
		}
	}
}


foreach ($storedFields as $storedFieldName => $storedFieldProperties) {
	foreach ($organizedSubmitFields as $I => $submit) {
		
		if ($storedFieldName == $submit['original']) {
			
			// Update formFields table
			$sql = sprintf("UPDATE %s SET fieldName='%s', type='%s', position='%s' WHERE formID='%s' AND fieldName='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($submit['fieldName']),
				$engine->openDB->escape($submit['type']),
				$engine->openDB->escape($submit['position']),
				$engine->openDB->escape($engine->localVars("formID")),
				$engine->openDB->escape($storedFieldName)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Rename link table if needed, assuming this is a multiselect field
			if ($submit['type'] == 'multiselect' && $submit['fieldName'] != $submit['original']) {
				
				// $foo['optionValues'] is in the form of "formName_fieldID"
				list($storedOptionFormName,$storedOptionFieldID) = explode("_",$storedFieldProperties['optionValues']);
				list($submitOptionFormName,$submitOptionFieldID) = explode("_",$submit['properties']['optionValues']);

				// Pull the field name from the stored ID
				$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
					$engine->openDB->escape($engine->dbTables("formFields")),
					$engine->openDB->escape($storedOptionFieldID)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
				$storedOptionFieldName = $row['fieldName'];

				// Pull the field name from the submitted ID
				$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
					$engine->openDB->escape($engine->dbTables("formFields")),
					$engine->openDB->escape($submitOptionFieldID)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
				$submitOptionFieldName = $row['fieldName'];

				// Switch to project database
				$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

				$sql = sprintf("RENAME TABLE %s_link_%s_%s TO %s_link_%s_%s",
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($storedOptionFormName),
					$engine->openDB->escape($storedOptionFieldName),
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($submitOptionFormName),
					$engine->openDB->escape($submitOptionFieldName)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				
				// Change field in new link table to match
				$sql = sprintf("ALTER TABLE %s_link_%s_%s CHANGE %sID %sID int(10) UNSIGNED NOT NULL",
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($submitOptionFormName),
					$engine->openDB->escape($submitOptionFieldName),
					$engine->openDB->escape($storedOptionFormName),
					$engine->openDB->escape($submitOptionFormName)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
				
				// Switch to system database
				$engine->openDB->select_db($engine->localVars("dbName"));

			}

			// Update settings
			foreach ($submit['properties'] as $key => $value) {
				switch ($key) {

					// List of settings to place into settings table
					case 'releasePublic':
					case 'searchable':
					case 'sortable':

						// Switch to project database
						$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

						$sql = sprintf("UPDATE %s SET formName='%s', fieldName='%s', setting='%s', value='%s' WHERE formName='%s' AND fieldName='%s' AND setting='%s' LIMIT 1",
							$engine->openDB->escape($engine->dbTables($engine->localVars("dbPrefix")."settings")),
							$engine->openDB->escape($engine->localVars("formName")),
							$engine->openDB->escape($submit['fieldName']),
							$engine->openDB->escape($key),
							$engine->openDB->escape($value),
							$engine->openDB->escape($engine->localVars("formName")),
							$engine->openDB->escape($submit['original']),
							$engine->openDB->escape($key)
							);
						$engine->openDB->sanitize = FALSE;
						$sqlResult                = $engine->openDB->query($sql);

						// Switch to system database
						$engine->openDB->select_db($engine->localVars("dbName"));

						break;

					default:
						break;
				}
			}

			// Update each of the properties for this field
			foreach ($storedFieldProperties as $storedOption => $storedValue) {
				
				// Skip this option, it's only in the array for reference
				if ($storedOption == 'fieldID') {
					continue;
				}

				foreach ($submit['properties'] as $submitOption => $submitValue) {
					if ($storedOption == $submitOption) {
						// Update property
						if ($submitOption != 'autoincCurrent') {
							$sql = sprintf("UPDATE %s SET value='%s' WHERE fieldID='%s' AND `option`='%s'",
								$engine->openDB->escape($engine->dbTables("formFieldProperties")),
								$engine->openDB->escape($submitValue),
								$engine->openDB->escape($storedFieldProperties['fieldID']),
								$engine->openDB->escape($submitOption)
								);
							$engine->openDB->sanitize = FALSE;
							$sqlResult                = $engine->openDB->query($sql);
						}

						continue(2);
					}
				}

				// Delete property if stored, but not submitted
				if ($storedOption != 'prevFormats') {
					$sql = sprintf("DELETE FROM %s WHERE fieldID='%s' AND `option`='%s'",
						$engine->openDB->escape($engine->dbTables("formFieldProperties")),
						$engine->openDB->escape($storedFieldProperties['fieldID']),
						$engine->openDB->escape($storedOption)
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult                = $engine->openDB->query($sql);
				}
			}

			foreach ($submit['properties'] as $submitOption => $submitValue) {
				foreach ($storedFieldProperties as $storedOption => $storedValue) {
				
					// Skip this option, it's only in the array for reference
					if ($storedOption == 'fieldID') {
						continue;
					}

					// No need to update property again
					if ($storedOption == $submitOption) {
						continue(2);
					}
				}

				// Add property if submitted, but not stored
				$sql = sprintf("INSERT INTO %s (fieldID,`option`,value) VALUES ('%s','%s','%s')",
					$engine->openDB->escape($engine->dbTables("formFieldProperties")),
					$engine->openDB->escape($storedFieldProperties['fieldID']),
					$engine->openDB->escape($submitOption),
					$engine->openDB->escape($submitValue)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);

				// Switch to project database
				$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

				// Add to settings table
				$sql = sprintf("INSERT INTO %s (formName,fieldName,setting,value) VALUES ('%s','%s','%s','%s')",
					$engine->openDB->escape($engine->dbTables($engine->localVars("dbPrefix")."settings")),
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($submit['fieldName']),
					$engine->openDB->escape($submitOption),
					$engine->openDB->escape($submitValue)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);

				// Switch to system database
				$engine->openDB->select_db($engine->localVars("dbName"));

			}

			continue(2);
		}
	}

	// Delete stored field info that was not submitted
	$sql = sprintf("DELETE FROM %s WHERE ID='%s'",
		$engine->openDB->escape($engine->dbTables("formFields")),
		$engine->openDB->escape($storedFieldProperties['fieldID'])
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	// Delete stored field properties
	$sql = sprintf("DELETE FROM %s WHERE formName='%s' AND fieldName='%s'",
		$engine->openDB->escape($engine->dbTables($engine->localVars("dbPrefix")."settings")),
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($storedFieldName)
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));

	foreach ($storedFieldProperties as $option => $value) {

		// Skip this option, it's only in the array for reference
		if ($option == 'fieldID') {
			continue;
		}

		// Delete stored field properties
		$sql = sprintf("DELETE FROM %s WHERE fieldID='%s' AND `option`='%s'",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($storedFieldProperties['fieldID']),
			$engine->openDB->escape($option)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);

		if ($storedFieldProperties['type'] == 'multiselect' && $option == 'optionValues') {
			
			// $value is in the form of "formName_fieldID"
			list($storedOptionFormName,$storedOptionFieldID) = explode("_",$value);

			// Pull the field name from the ID portion of the stored optionValues property
			$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($storedOptionFieldID)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			$sql = sprintf("DROP TABLE IF EXISTS %s_link_%s_%s",
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($storedOptionFormName),
					$engine->openDB->escape($row['fieldName'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));
		}
	}

}

foreach ($organizedSubmitFields as $I => $submit) {
	foreach ($storedFields as $storedFieldName => $storedFieldProperties) {
		if ($storedFieldName == $submit['original']) {
			// no need to update again
			continue(2);
		}
	}

	// Insert submitted info that was not stored
	$sql = sprintf("INSERT INTO %s (formID,fieldName,type,position) VALUES ('%s','%s','%s','%s')",
		$engine->openDB->escape($engine->dbTables("formFields")),
		$engine->openDB->escape($engine->localVars("formID")),
		$engine->openDB->escape($submit['fieldName']),
		$engine->openDB->escape($submit['type']),
		$engine->openDB->escape($submit['position'])
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	$newFieldID = $sqlResult['id'];

	// Insert each property for this field
	foreach ($submit['properties'] as $k => $v) {
		$sql = sprintf("INSERT INTO %s (fieldID,`option`,value) VALUES ('%s','%s','%s')",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($newFieldID),
			$engine->openDB->escape($k),
			$engine->openDB->escape($v)
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);			

		// Switch to project database
		$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

		// Add to settings table
		foreach ($settingsTblElements as $element) {
			if ($element == $k) {
				$sql = sprintf("INSERT INTO %s (formName,fieldName,setting,value) VALUES ('%s','%s','%s','%s')",
					$engine->openDB->escape($engine->dbTables($engine->localVars("dbPrefix")."settings")),
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape($submit['fieldName']),
					$engine->openDB->escape($k),
					$engine->openDB->escape($v)
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
			}
		}

		// Switch to system database
		$engine->openDB->select_db($engine->localVars("dbName"));

		if ($submit['type'] == 'multiselect' && $k == 'optionValues') {

			// $value is in the form of "formName_fieldID"
			list($submitOptionFormName,$submitOptionFieldID) = explode("_",$v);

			// Pull the field name from the ID portion of the submitted optionValues property
			$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($submitOptionFieldID)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
			$submitFieldName = $row['fieldName'];

			$linkTableName = $engine->localVars("formName")."_link_".$submitOptionFormName."_".$submitFieldName;

			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			// Create the basic data table with a primary key defined
			$sql = sprintf("CREATE TABLE %s (mfcs_ID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (mfcs_ID), %sID int(10) UNSIGNED NOT NULL, %sID int(10) UNSIGNED NOT NULL)",
				$engine->openDB->escape($linkTableName),
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($submitOptionFormName)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

			// Insert table name into dbTables table if it's not already there
			$sql = sprintf("INSERT INTO %s (name) SELECT '%s' FROM dual WHERE NOT EXISTS(SELECT * FROM %s WHERE name='%s' LIMIT 1)",
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->openDB->escape($linkTableName),
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->openDB->escape($linkTableName)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

		}
	}

}


// Switch to project database
$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

// Need to use schema here in case the table is empty
$sql = sprintf("SHOW COLUMNS FROM %s",
	$engine->openDB->escape($engine->localVars("formName"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

$keys = array();
while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
	$keys[] = $row['Field'];
}

// Manipulate the data table
foreach ($submitFields as $field) {
	
	if ($field['type'] == 'multiselect') {
		continue;
	}

	foreach ($keys as $key) {
		
		if ($key == $field['original']) {
			
			// Update columns
			$sql = sprintf("ALTER TABLE %s CHANGE `%s` `%s` varchar(%s) %s %s",
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($field['original']),
				$engine->openDB->escape($field['fieldName']),
				$engine->openDB->escape(isset($field['maxlength'])?$field['maxlength']:'255'),
				$engine->openDB->escape(isset($field['nulls'])?'NULL':'NOT NULL'),
				isset($field['placeHolder'])?"DEFAULT '".$field['placeHolder']."'":""
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			continue(2);
		}

	}

	// Add columns that are in the field definition, but not in the schema
	$sql = sprintf("ALTER TABLE %s ADD COLUMN `%s` varchar(%s) %s %s",
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($field['fieldName']),
		$engine->openDB->escape(isset($field['maxlength'])?$field['maxlength']:'255'),
		$engine->openDB->escape(isset($field['nulls'])?'NULL':'NOT NULL'),
		isset($field['placeHolder'])?"DEFAULT '".$field['placeHolder']."'":""
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

}


foreach ($keys as $key) {

	// Skip ID field and parentID field
	if ($key == 'mfcs_ID' || $key == 'parentFormID') {
		continue;
	}

	// Also skip any matches, as they've already been updated
	foreach ($submitFields as $field) {
		if ($key == $field['original'] || $field['type'] == 'multiselect') {
			// No need to update again
			continue(2);
		}
	}

	// Drop columns in the schema, but not in the field definition anymore
	$sql = sprintf("ALTER TABLE %s DROP COLUMN `%s`",
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($key)
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

}

// Switch to system database
$engine->openDB->select_db($engine->localVars("dbName"));

$errorMsg .= webHelper_successMsg("Form submitted successfully.");
?>
