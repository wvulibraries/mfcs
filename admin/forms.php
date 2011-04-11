<?php
include("header.php");

$engine->localVars("listTable",$engine->dbTables("forms"));
$ident = "proj".$engine->localVars("projectID")."forms";
$settingsTblElements = array("releasePublic","formType","deletions");
$errorMsg = NULL;

function listFields() {
	global $engine;

	$listObj = new listManagement($engine,$engine->localVars("listTable"));
	$listObj->whereClause = "WHERE projectID='".$engine->localVars("projectID")."'";
	// $listObj->debug = TRUE;

	$options = array();
	$options['field']    = "projectID";
	$options['label']    = "Project";
	$options['type']     = "hidden";
	$options['dupes']    = TRUE;
	$options['value']    = $engine->localVars("projectID");
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "formName";
	$options['label']    = "Name";
	$options['size']     = "20";
	$options['validate'] = "alphaNumericNoSpaces";
	$options['original'] = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "label";
	$options['label']    = "Label";
	$options['dupes']     = TRUE;
	$options['size']     = "20";
	$options['validate'] = "alphaNumeric";
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "formType";
	$options['label']     = "Type";
	$options['dupes']     = TRUE;
	$options['type']      = "select";
	$options['options'][] = array("value"=>"metadata","label"=>"Metadata");
	$options['options'][] = array("value"=>"record","label"=>"Record");
	$options['original']  = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "deletions";
	$options['label']     = "Allow Deletions";
	$options['dupes']     = TRUE;
	$options['type']      = "select";
	$options['options'][] = array("value"=>"1","label"=>"Yes");
	$options['options'][] = array("value"=>"0","label"=>"No");
	$options['original']  = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "releasePublic";
	$options['label']     = "Release to Public";
	$options['dupes']     = TRUE;
	$options['type']      = "select";
	$options['options'][] = array("value"=>"1","label"=>"Yes");
	$options['options'][] = array("value"=>"0","label"=>"No");
	$options['original']  = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = '<a href="'.$engine->localVars("siteRoot").'admin/editForm.php?proj={projectID}&form={formName}">Edit</a>';
	$options['label']    = "Edit";
	$options['type']     = "plainText";
	$listObj->addField($options);
	unset($options);

	return $listObj;
}


$listObj = listFields();

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_submit'])) {

	if (is_empty($engine->cleanPost['MYSQL']['formName_insert'])) {
		$errorMsg .= webHelper_errorMsg("Blank entries not allowed in Form Name.");
	}
	else {

		if (isnull($errorMsg)) {

			//Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			// Create data table
			$sql = sprintf("CREATE TABLE %s (mfcs_ID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (mfcs_ID))",
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error creating new Form".(($sqlResult['errorNumber']=='1007')?" (already exists)":""));
			}

			// Create changelog table
			$sql = sprintf("CREATE TABLE %s_changelog (ID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, dataID int(10) UNSIGNED NOT NULL, action varchar(255) NOT NULL DEFAULT '', time int(10) UNSIGNED NOT NULL, PRIMARY KEY (ID))",
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Add setting for this form in the settings table
			foreach ($settingsTblElements as $element) {
				$sql = sprintf("INSERT INTO %s (formName,setting,value) VALUES ('%s','%s','%s')",
					$engine->openDB->escape($engine->localVars("dbPrefix").'settings'),
					$engine->cleanPost['MYSQL']['formName_insert'],
					$engine->openDB->escape($element),
					$engine->cleanPost['MYSQL'][$element.'_insert']
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult                = $engine->openDB->query($sql);
			}

			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

			// Insert new table into dbTables if necessary
			$sql = sprintf("SELECT ID FROM %s WHERE name='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['affectedRows'] < 1) {
				$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

				$sql = sprintf("INSERT INTO %s (name) VALUES ('%s')",
					$engine->openDB->escape($engine->dbTables("dbTables")),
					$engine->cleanPost['MYSQL']['formName_insert']
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult2               = $engine->openDB->query($sql);
			}
			
		}
	}

	if (isnull($errorMsg)) {
		$errorMsg .= $listObj->insert();
	}

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_update'])) {
	
	$error = FALSE;

	// Verify record form types have an identifier
	$formTypes = array();
	foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
		if (strpos($key,"formType") !== FALSE) {
			$id = substr($key,strrpos($key,"_")+1);
			
			if (strpos($key,"original") !== FALSE) {
				$formTypes[$id]['orig'] = $value;
				continue;
			}
			
			$formTypes[$id]['new'] = $value;
		}
	}

	foreach ($formTypes as $formID => $formType) {
		// If the submitted value is different from the original and the new type is record
		if ($formType['orig'] != $formType['new'] && $formType['new'] == 'record') {

			// Check fields to see if an identifier exists
			$sql = sprintf("SELECT * FROM %s WHERE formID='%s' AND type='identifier'",
				$engine->openDB->escape($engine->dbTables("formFields")),
				$engine->openDB->escape($formID)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['affectedRows'] == 0) {
				// Set type back to original
				$engine->cleanPost['RAW']['formType_'.$formID]   = $engine->cleanPost['RAW']['original_formType_'.$formID];
				$engine->cleanPost['HTML']['formType_'.$formID]  = $engine->cleanPost['HTML']['original_formType_'.$formID];
				$engine->cleanPost['MYSQL']['formType_'.$formID] = $engine->cleanPost['MYSQL']['original_formType_'.$formID];

				$errorMsg .= webHelper_errorMsg($engine->cleanPost['HTML']['formName_'.$formID]." must have an Identifier before changing to a Record type. Other records may be updated still.");
			}

		}
	}


	$deletions = $listObj->haveDeletes();
	if ($deletions !== FALSE) {
		foreach ($deletions as $val) {

			// Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			// Remove data table
			$sql = sprintf("DROP TABLE IF EXISTS %s",
				$engine->cleanPost['MYSQL']['formName_'.$val]
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
	
			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
			}

			// Remove changelog table
			$sql = sprintf("DROP TABLE IF EXISTS %s_changelog",
				$engine->cleanPost['MYSQL']['formName_'.$val]
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Remove this form from the settings table
			$sql = sprintf("DELETE FROM %s WHERE formName='%s'",
				$engine->openDB->escape($engine->localVars("dbPrefix").'settings'),
				$engine->cleanPost['MYSQL']['formName_'.$val]
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

		}
	}


	// Loop through Post variables looking for the form names and the original form names
	$formNames = array();
	foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
		if (strpos($key,"formName") !== FALSE) {
			$id = substr($key,strrpos($key,"_")+1);
			
			if (strpos($key,"original") !== FALSE) {
				$formNames[$id]['orig'] = $value;
				continue;
			}
			
			$formNames[$id]['new'] = $value;
		}
	}


	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	foreach ($formNames as $id => $formName) {
		// If the submitted value is different from the original
		if ($formName['orig'] != $formName['new']) {
			
			// Rename data table
			$sql = sprintf("RENAME TABLE %s TO %s",
				$engine->openDB->escape($formName['orig']),
				$engine->openDB->escape($formName['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				$error = TRUE;
			}

			// Rename changelog table
			$sql = sprintf("RENAME TABLE %s_changelog TO %s_changelog",
				$engine->openDB->escape($formName['orig']),
				$engine->openDB->escape($formName['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				$error = TRUE;
			}

		}

		// Update settings table with new form settings
		foreach ($settingsTblElements as $element) {
			$sql = sprintf("UPDATE %s SET formName='%s',setting='%s',value='%s' WHERE formName='%s' AND setting='%s'",
				$engine->openDB->escape($engine->localVars("dbPrefix").'settings'),
				$engine->openDB->escape($formName['new']),
				$engine->openDB->escape($element),
				$engine->cleanPost['MYSQL'][$element.'_'.$id],
				$engine->openDB->escape($formName['orig']),
				$engine->openDB->escape($element)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
		}

	}

	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));

	if ($error === FALSE) {
		$errorMsg .= $listObj->update();		
	}
	
}
// Form Submission

$listObj = listFields();


print "<h2>Edit Form List</h2>";

if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}

print "<h3>New Form</h3>";
print $listObj->displayInsertForm();

print "<hr />";

print "<h3>Edit Forms</h3>";
print $listObj->displayEditTable();


$engine->eTemplate("include","footer");
?>
