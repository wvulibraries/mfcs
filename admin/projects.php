<?php
include("header.php");

$engine->localVars("listTable",$engine->dbTables("projects"));
$ident = "projects";
$errorMsg = NULL;

function listFields() {
	global $engine;

	$listObj = new listManagement($engine,$engine->localVars("listTable"));

	$options = array();
	$options['field']    = "name";
	$options['label']    = "Project Name";
	$options['size']     = "20";
	$options['validate'] = "alphaNumericNoSpaces";
	$options['original'] = TRUE;
	$listObj->addField($options);
	unset($options);

	return $listObj;
}


$listObj = listFields();

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_submit'])) {
	
	$errorMsg .= $listObj->insert();

/*
	// add to permissions table with ident of 'projects'
	$permissions = new mfcsPermissions("permissions",$engine,$ident,"tempPermissions");

	$insert = $permissions->insert($engine->cleanPost['MYSQL']['name_insert']);
*/
	if (!is_empty($engine->cleanPost['MYSQL']['name_insert'])) {
		$sql = sprintf("CREATE DATABASE %s%s",
			$engine->openDB->escape($engine->localVars("dbPrefix")),
			$engine->cleanPost['MYSQL']['name_insert']
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		
		if ($sqlResult['affectedRows'] < 0) {
			$errorMsg .= webHelper_errorMsg("Error creating new Project".(($sqlResult['errorNumber']=='1007')?" (already exists)":""));
		}
	}

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_update'])) {
	
	$deletions = $listObj->haveDeletes();
	if ($deletions !== FALSE) {
		foreach ($deletions as $val) {

			$sql = sprintf("DROP DATABASE IF EXISTS `%s%s`",
				$engine->openDB->escape($engine->localVars("dbPrefix")),
				$engine->cleanPost['MYSQL']['name_'.$val]
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying projects.");
			}
/*
			$sql = sprintf("DELETE FROM %s WHERE ident='%s' AND name='%s'",
				$engine->openDB->escape($engine->dbTables("permissions")),
				$engine->openDB->escape($ident),
				$engine->cleanPost['MYSQL']['name_'.$val]
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if (!$sqlResult['result']) {
				$errorMsg .= webHelper_errorMsg("Error modifying permissions.");
			}
*/
		}
	}

	foreach ($engine->cleanPost['MYSQL'] as $key => $value) {
		$id = substr($key,strrpos($key,"_")+1);
		
		if (strpos($key,"delete") !== FALSE) {
			$vals[$value[0]]['delete'] = TRUE;
			continue;
		}

		if (strpos($key,"name") !== FALSE) {
			
			if (strpos($key,"original") !== FALSE) {
				$vals[$id]['orig'] = $value;
				continue;
			}
			
			$vals[$id]['new'] = $value;
			continue;
		
		}
	}
	
	foreach ($vals as $val) {
		
		// skip delete -- they've already been handled
		if (isset($val['delete']) && $val['delete'] === TRUE) {
			continue;
		}

		// Changed the name of the project
		if ($val['orig'] !== $val['new']) {
			
			// Create new database
			$sql = sprintf("CREATE DATABASE IF NOT EXISTS %s%s",
				$engine->openDB->escape($engine->localVars("dbPrefix")),
				$engine->openDB->escape($val['new'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				break;
			}

			// Move all data from old database to new one using rename table command
			$sql = sprintf("SELECT CONCAT('RENAME TABLE ',table_schema,'.',table_name, ' TO ','%s.',table_name,';') FROM information_schema.TABLES WHERE table_schema LIKE '%s'",
				$engine->openDB->escape($val['new']),
				$engine->openDB->escape($val['orig'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				break;
			}

			// Delete old database
			$sql = sprintf("DROP DATABASE IF EXISTS `%s%s`",
				$engine->openDB->escape($engine->localVars("dbPrefix")),
				$engine->openDB->escape($val['orig'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error modifying forms.");
				break;
			}
/*
			// Edit name in permissions table
			$sql = sprintf("UPDATE %s SET name='%s' WHERE ident='%s' AND name='%s' LIMIT 1",
				$engine->openDB->escape($engine->dbTables("permissions")),
				$engine->openDB->escape($val['new']),
				$engine->openDB->escape($ident),
				$engine->openDB->escape($val['orig'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult                = $engine->openDB->query($sql);
			
			if (!$sqlResult['result']) {
				$errorMsg .= webHelper_errorMsg("Error modifying permissions.");
			}
*/
		}
	}

	if (isnull($errorMsg)) {
		$errorMsg .= $listObj->update();
	}
	
}
// Form Submission

$listObj = listFields();


print "<h2>Edit Projects</h2>";

if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}

print "<h3>New Project</h3>";
print $listObj->displayInsertForm();

print "<hr />";

print "<h3>Edit Projects</h3>";
print $listObj->displayEditTable();


$engine->eTemplate("include","footer");
?>
