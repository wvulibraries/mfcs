<?php
global $engine;

print '<ul>';

if (!is_empty($engine->localVars("projectName"))) {
	print '<li>Current Project: '.$engine->localVars("projectName").'</li>';
}

print '<li><a href="{local var="siteRoot"}selectProject.php">Select Project</a></li>';

if (!is_empty($engine->localVars("projectID"))) {
	
	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));

	print '<li class="noBorder">&nbsp;</li>';
	print '<li>Records';

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='record' ORDER BY label",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
		
	print '<ul>';
	if ($sqlResult['affectedRows'] > 0) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			print '<li><a href="displayForm.php?form='.$row['formName'].'&display=insert">'.$row['label'].' Insert</a></li>';
			print '<li><a href="displayForm.php?form='.$row['formName'].'&display=update">'.$row['label'].' Update</a></li>';
		}
	}
	else {
		print 'No Forms Created';
	}
	print '</ul>';

	print '</li>';

	print '<li class="noBorder">&nbsp;</li>';
	print '<li>Metadata';

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='metadata' ORDER BY label",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	print '<ul>';
	if ($sqlResult['affectedRows'] > 0) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			print '<li><a href="displayForm.php?form='.$row['formName'].'&display=both">'.$row['label'].'</a></li>';
		}
	}
	else {
		print 'No Forms Created';
	}
	print '</ul>';

	print '</li>';

	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

}

print '<li class="noBorder">&nbsp;</li>';
print '<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>';

print '</ul>';
?>
