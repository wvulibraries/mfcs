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

	$sql = sprintf("SELECT DISTINCT groupName FROM %s WHERE projectID='%s' AND formType='record' AND parentForm='0' ORDER BY groupName",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	print '<ul>';
	if ($sqlResult['affectedRows'] > 0) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			
			if (!is_empty($row['groupName'])) {
				print '<li>'.$row['groupName'];
				print '<ul>';
			}
			
			// top level forms
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='record' AND groupName='%s' AND parentForm='0' ORDER BY label",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("projectID")),
				$engine->openDB->escape($row['groupName'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult2               = $engine->openDB->query($sql);
			
			if ($sqlResult2['result']) {
				while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
					print '<li><a href="displayForm.php?form='.$row2['formName'].'&display=insert">'.htmlSanitize($row2['label']).' Insert</a></li>';
					print '<li><a href="displayForm.php?form='.$row2['formName'].'&display=update">'.htmlSanitize($row2['label']).' Update</a></li>';
				}
			}

			if (!is_empty($row['groupName'])) {
				print '</ul>';
				print '</li>';
			}

		}
	}
	else {
		print 'No Forms Created';
	}
	print '</ul>';
	print '</li>';

	print '<li class="noBorder">&nbsp;</li>';
	print '<li>Metadata';

	$sql = sprintf("SELECT DISTINCT groupName FROM %s WHERE projectID='%s' AND formType='metadata' AND parentForm='0' ORDER BY groupName",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	print '<ul>';
	if ($sqlResult['affectedRows'] > 0) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			
			if (!is_empty($row['groupName'])) {
				print '<li>'.$row['groupName'];
				print '<ul>';
			}
			
			// top level forms
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='metadata' AND groupName='%s' AND parentForm='0' ORDER BY label",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("projectID")),
				$engine->openDB->escape($row['groupName'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult2               = $engine->openDB->query($sql);
			
			if ($sqlResult2['result']) {
				while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
					print '<li><a href="displayForm.php?form='.$row2['formName'].'&display=both">'.htmlSanitize($row2['label']).'</a></li>';
				}
			}

			if (!is_empty($row['groupName'])) {
				print '</ul>';
				print '</li>';
			}

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
