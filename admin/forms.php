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
	$options['label']    = "Project ID";
	$options['type']     = "hidden";
	$options['value']    = $engine->localVars("projectID");
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "formName";
	$options['label']    = "Name";
	$options['dupes']    = TRUE;
	$options['size']     = "20";
	$options['validate'] = "alphaNumericNoSpaces";
	$options['original'] = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']    = "label";
	$options['label']    = "Label";
	$options['dupes']    = TRUE;
	$options['size']     = "20";
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
	$options['field']    = "groupName";
	$options['label']    = "Group";
	$options['dupes']    = TRUE;
	$options['blank']    = TRUE;
	$options['size']     = "20";
	$options['validate'] = "alphaNumeric";
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "parentForm";
	$options['label']     = "Parent Form";
	$options['dupes']     = TRUE;
	$options['blank']     = TRUE;
	$options['type']      = "select";
	$options['options'][] = array("value"=>"0","label"=>"None");

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='record' AND parentForm='0'",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$options['options'][] = array("value"=>$row['ID'],"label"=>$row['label']);
		}
	}
	
	$options['original']  = TRUE;
	$listObj->addField($options);
	unset($options);

	$options = array();
	$options['field']     = "insertLocation";
	$options['label']     = "Insert Location";
	$options['dupes']     = TRUE;
	$options['blank']     = TRUE;
	$options['type']      = "select";
	$options['options'][] = array("value"=>"above","label"=>"Above Edit");
	$options['options'][] = array("value"=>"below","label"=>"Below Edit");
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
	$options['field']    = '<a href="'.$engine->localVars("siteRoot").'admin/editForm.php?form={formName}">Edit</a>';
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
			$sql = sprintf("CREATE TABLE %s (mfcs_ID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, parentFormID int(10) UNSIGNED NULL, PRIMARY KEY (mfcs_ID))",
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

			// Add settings for this form in the settings table
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

			// Insert table name into dbTables table if it's not already there
			$sql = sprintf("INSERT INTO %s (name) SELECT '%s' FROM dual WHERE NOT EXISTS(SELECT * FROM %s WHERE name='%s' LIMIT 1)",
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->cleanPost['MYSQL']['formName_insert'],
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

		}
	}

	if (isnull($errorMsg)) {
		$errorMsg .= $listObj->insert();

		// check to see if the insert failed, if so, undo db changes
		if (is_empty($engine->localVars("listObjInsertID"))) {

			//Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			// Remove data table
			$sql = sprintf("DROP TABLE IF EXISTS %s",
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Remove changelog table
			$sql = sprintf("DROP TABLE IF EXISTS %s_changelog",
				$engine->cleanPost['MYSQL']['formName_insert']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			// Remove settings for this form in the settings table
			foreach ($settingsTblElements as $element) {
				$sql = sprintf("DELETE FROM %s WHERE formName='%s' AND setting='%s' AND value='%s'",
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
			
		}
	}

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_update'])) {
	
	$error = FALSE;

	$submitFields = array();
	foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
		$vals = explode("_",$key);

		if (count($vals) == 3 && $vals[0] == "original" && isint($vals[2])) {
			$submitFields[$vals[2]][$vals[1]]['orig'] = $value;
		}
		else if (count($vals) == 2 && isint($vals[1])) {
			$submitFields[$vals[1]][$vals[0]]['new'] = $value;
		}
	}

	foreach ($submitFields as $formID => $submit) {
		// If the submitted type or parent value is different from the original
		if ($submit['formType']['orig'] != $submit['formType']['new'] || $submit['parentForm']['orig'] != $submit['parentForm']['new']) {
			
			// If the new type is record, and it is not a subform
			if ($submit['formType']['new'] == 'record' && $submit['parentForm']['new'] == '0') {
				
				// Check to verify this is the only top level record
				foreach ($submitFields as $ID => $value) {
					if ($formID != $ID && $value['formType']['new'] == 'record' && $value['parentForm']['new'] == 0) {
						// Set type back to original
						$engine->cleanPost['RAW']['formType_'.$formID]   = $engine->cleanPost['RAW']['original_formType_'.$formID];
						$engine->cleanPost['HTML']['formType_'.$formID]  = $engine->cleanPost['HTML']['original_formType_'.$formID];
						$engine->cleanPost['MYSQL']['formType_'.$formID] = $engine->cleanPost['MYSQL']['original_formType_'.$formID];

						$errorMsg .= webHelper_errorMsg("Another top-level Record already exists. ".$engine->cleanPost['HTML']['formName_'.$formID]." could not be set as a Record. Other records may be updated still.");
					}
				}

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

		// If the submitted value is different from the original
		if ($submit['formName']['orig'] != $submit['formName']['new']) {
			
			//Switch to project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

			// Rename data table
			$sql = sprintf("RENAME TABLE %s TO %s",
				$engine->openDB->escape($submit['formName']['orig']),
				$engine->openDB->escape($submit['formName']['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				$error = TRUE;
			}

			// Rename changelog table
			$sql = sprintf("RENAME TABLE %s_changelog TO %s_changelog",
				$engine->openDB->escape($submit['formName']['orig']),
				$engine->openDB->escape($submit['formName']['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				$error = TRUE;
			}

			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

			// Insert table name into dbTables table if it's not already there
			$sql = sprintf("INSERT INTO %s (name) SELECT '%s' FROM dual WHERE NOT EXISTS(SELECT * FROM %s WHERE name='%s' LIMIT 1)",
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->openDB->escape($submit['formName']['new']),
				$engine->openDB->escape($engine->dbTables("dbTables")),
				$engine->openDB->escape($submit['formName']['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

		}

		// Update settings table with new form settings
		foreach ($settingsTblElements as $element) {
			$sql = sprintf("UPDATE %s SET formName='%s',setting='%s',value='%s' WHERE formName='%s' AND setting='%s'",
				$engine->openDB->escape($engine->localVars("dbPrefix").'settings'),
				$engine->openDB->escape($submit['formName']['new']),
				$engine->openDB->escape($element),
				$engine->cleanPost['MYSQL'][$element.'_'.$formID],
				$engine->openDB->escape($submit['formName']['orig']),
				$engine->openDB->escape($element)
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
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


	if ($error === FALSE) {
		$errorMsg .= $listObj->update();		
	}
	
}
// Form Submission

$listObj = listFields();


$engine->eTemplate("include","header");
?>

<script type="text/javascript" src="{local var="siteRoot"}includes/forms_functions.js"></script>

<h2>Edit Form List</h2>

<?php
if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}
?>

<h3>New Form</h3>
<?php print $listObj->displayInsertForm(); ?>

<hr />

<h3>Edit Forms</h3>
<?php print $listObj->displayEditTable(); ?>

<script type="text/javascript">
	$(document).ready(init);
</script>

<?php
$engine->eTemplate("include","footer");
?>
