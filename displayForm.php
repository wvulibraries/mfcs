<?php
include("header.php");

$error    = FALSE;
$errorMsg = NULL;
$fields   = array();
$engine->localVars("display",isset($engine->cleanGet['MYSQL']['display'])?$engine->cleanGet['MYSQL']['display']:NULL);

// Include file with listObj field declaration function: listFields()
recurseInsert("includes/displayForm_fields.php","php");


// Populate fields array to pass into listFields() function
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
		
		if ($sqlResult2['result']) {
			while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {

				$row[$row2['option']] = $row2['value'];

			}
		}		
		
		$fields[] = $row;

	}
}


$listObj = listFields($fields,$engine->localVars("display"));

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->localVars("formName").'_submit'])) {
	
	foreach ($fields as $field) {
		
		if ($engine->localVars("display") != 'updateinsert' && $field['type'] == 'identifier' && $field['managedBy'] == 'system') {
			
			$newID       = NULL;
			$formatParts = array();

			$formatParts[0] = substr($field['format'],0,strpos($field['format'],'#'));
			$formatParts[1] = substr($field['format'],strpos($field['format'],'#'),strrpos($field['format'],'#')-strpos($field['format'],'#')+1);
			$formatParts[2] = substr($field['format'],strrpos($field['format'],'#')+1);

			$oldFormats = explode("|",$field['prevFormats']);
			$padLength = strlen($formatParts[1]);

			if ($field['reuseids'] == 0) {

				$sql = sprintf("SELECT SUBSTRING(%s,%s,%s) AS identifier FROM %s HAVING identifier>='%s' ORDER BY identifier DESC",
					$engine->openDB->escape($field['fieldName']),
					$engine->openDB->escape(strlen($formatParts[0])+1),
					$engine->openDB->escape($padLength),
					$engine->openDB->escape($engine->localVars("formName")),
					$engine->openDB->escape(leftPad($field['autoinc'],$padLength))
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);

				if ($sqlResult['result']) {
					while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM)) {
						if (!is_numeric($row[0])) {
							continue;
						}
						if ($row[0] >= $field['autoincCurrent']) {
							$newID = leftPad(($row[0]+1),$padLength);
							break;
						}
					}
				}

			}
			else if ($field['reuseids'] == 1) {

				$lastID    = 0;
				$formatStr = NULL;

				if (!is_empty($formatParts[0])) {
					$formatStr .= $field['fieldName']." LIKE '".$formatParts[0]."%'";
				}
				if (!is_empty($formatParts[2])) {
					$formatStr .= (isnull($formatStr)?'':' AND ').$field['fieldName']." LIKE '%".$formatParts[2]."'";
				}
				if (!isnull($formatStr)) {
					$formatStr = "WHERE ".$formatStr;
				}
				
				$sql = sprintf("SELECT SUBSTRING(%s,%s,%s) AS identifier FROM %s %s HAVING identifier>='%s' ORDER BY identifier ASC",
					$engine->openDB->escape($field['fieldName']),
					$engine->openDB->escape(strlen($formatParts[0])+1),
					$engine->openDB->escape($padLength),
					$engine->openDB->escape($engine->localVars("formName")),
					$formatStr,
					$engine->openDB->escape(leftPad($field['autoinc'],$padLength))
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);

				if ($sqlResult['result']) {
					while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM)) {
						if (!is_numeric($row[0])) {
							continue;
						}
						if ($row[0] - $lastID > 1) {
							break;
						}
						$lastID = $row[0];
					}
				}
				
				$newID = leftPad(($lastID+1),$padLength);

			}

			if (isnull($newID)) {
				// very first entry in table
				$newID = leftPad($field['autoincCurrent'],$padLength);
			}
			

			if ($newID == $formatParts[1]) {
				$errorMsg .= webHelper_errorMsg("Error generating a new ID.");
				$error = TRUE;
			}
			else if (strlen($newID) > $padLength) {
				$errorMsg .= webHelper_errorMsg("New ".$field['fieldLabel']." is too long.");
				$error = TRUE;
			}
			else {
				$formatParts[1] = $newID;
				$engine->cleanPost['MYSQL'][$field['fieldName'].'_insert'] = implode($formatParts);
				
				$newIDmsg = webHelper_succesSMsg("New ".$field['fieldLabel'].": ".$engine->cleanPost['MYSQL'][$field['fieldName'].'_insert']);
			}

		}
	}

	if (!$error) {
		$errorMsg .= $listObj->insert();

		// Update changelog on successful insert
		if (!is_empty($engine->localVars("listObjInsertID")) && $engine->localVars("display") != 'updateinsert') {

			// Record an insert action into the changelog
			$sql = sprintf("INSERT INTO %s_changelog (dataID,action,time) VALUES ('%s','insert','%s')",
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($engine->localVars("listObjInsertID")),
				$engine->openDB->escape(time())
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// increment identifierCurrent
			if ($field['type'] == 'identifier' && $field['managedBy'] == 'system') {

				// Switch to system database
				$engine->openDB->select_db($engine->localVars("dbName"));

				$sql = sprintf("UPDATE %s SET value='%s' WHERE fieldID='%s' AND `option`='autoincCurrent' LIMIT 1",
					$engine->openDB->escape($engine->dbTables("formFieldProperties")),
					$engine->openDB->escape(++$newID),
					$engine->openDB->escape($field['ID'])
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);

				// Switch to project database
				$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			}

			if (isset($newIDmsg)) {
				$errorMsg .= $newIDmsg;
			}

		}
	}

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("formName").'_update'])) {
	
	// Update changelog on delete
	$deletions = $listObj->haveDeletes();
	if ($deletions !== FALSE) {
		foreach ($deletions as $val) {
			$sql = sprintf("INSERT INTO %s_changelog (dataID,action,time) VALUES ('%s','delete','%s')",
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($val),
				$engine->openDB->escape(time())
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
		}
	}

	$return = $listObj->update(TRUE);
	$errorMsg .= $return['string'];

	// Successfull update
	if (!$return['error']) {
		// Update changelog on successful updates
		$lastID = 0;
		foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
			
			$keyParts = explode("_",$key);
			if ($keyParts[0] != 'original') {
				continue;
			}

			// if original and post are unchanged
			if ($engine->cleanPost['MYSQL'][$keyParts[1].'_'.$keyParts[2]] == $engine->cleanPost['MYSQL'][$key]) {
				continue;
			}

			// don't duplicate changelogs for the same record w/ multiple updates
			if ($keyParts[2] == $lastID) {
				continue;
			}

			$sql = sprintf("INSERT INTO %s_changelog (dataID,action,time) VALUES ('%s','update','%s')",
				$engine->openDB->escape($engine->localVars("formName")),
				$engine->openDB->escape($keyParts[2]),
				$engine->openDB->escape(time())
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			$lastID = $keyParts[2];

		}
	}	

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("formName").'_delete'])) {

	// Update changelog on delete
	$sql = sprintf("INSERT INTO %s_changelog (dataID,action,time) VALUES ('%s','delete','%s')",
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($engine->cleanPost['MYSQL']['id']),
		$engine->openDB->escape(time())
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	// delete data record
	$sql = sprintf("DELETE FROM %s WHERE mfcs_ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($engine->cleanPost['MYSQL']['id'])
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));


}
// Form Submission


$engine->eTemplate("include","header");

print '<h2>'.htmlSanitize($engine->localVars("formLabel")).'</h2>';

if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}

if (is_empty($fields)) {
	print webHelper_errorMsg("No Form Fields defined.");
}
else {
	if ($engine->localVars("display") == 'both') {
		
		$listObj = listFields($fields,'insert');
		
		print '<h3>New '.htmlSanitize($engine->localVars("formLabel")).'</h3>';
		print $listObj->displayInsertForm();
		
		print '<br />';
		
		$listObj = listFields($fields,'update');
		
		print '<h3>Edit '.htmlSanitize($engine->localVars("formLabel")).'</h3>';
		print $listObj->displayEditTable();

	}
	else {

		$listObj = listFields($fields,$engine->localVars("display"));
		
		if ($engine->localVars("display") == 'insert') {
			
			print '<h3>New '.htmlSanitize($engine->localVars("formLabel")).'</h3>';
			print $listObj->displayInsertForm();

		}
		else if ($engine->localVars("display") == 'updateinsert') {
			
			print '<h3>Edit '.htmlSanitize($engine->localVars("formLabel")).'</h3>';
			
			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

			// If this has a subform, display a link here
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND parentForm='%s'",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("projectID")),
				$engine->openDB->escape($engine->localVars("formID"))
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['result']) {
				while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
					print '<p><a href="'.$engine->localVars("siteRoot").'displayForm.php?proj='.$engine->localVars("projectID").'&form='.htmlSanitize($row['formName']).'&display=both&mainid='.htmlSanitize($engine->localVars("formID")).'">'.htmlSanitize($row['label']).'</a></p>';
				}
			}

			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			print $listObj->displayInsertForm();

			// Delete button
			print '<form action="'.$_SERVER['PHP_SELF'].'?form='.htmlSanitize($engine->localVars("formName")).'&display=update" method="post">';
			print '<input type="hidden" name="id" value="'.$engine->cleanGet['HTML']['id'].'" />';
			print sessionInsertCSRF();
			print '<input type="submit" name="'.htmlSanitize($engine->localVars("formName")).'_delete" value="Delete" />';
			print '</form>';

		}
		else if ($engine->localVars("display") == 'update') {
			
			print '<h3>Edit '.htmlSanitize($engine->localVars("formLabel")).'</h3>';
			print $listObj->displayEditTable();

		}

	}
}

$engine->eTemplate("include","footer");
?>
