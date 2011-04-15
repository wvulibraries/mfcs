<?php
global $engine;

print "<ul>";

if (checkGroup("libraryDept_dlc_systems")) {
	print '<li>Systems Office';
		print '<ul>';
		print '<li><a href="{local var="siteRoot"}admin/projects.php">Projects</a></li>';
		print '<li><a href="{local var="siteRoot"}admin/permissions.php">Permissions</a></li>';
		print '<li><a href="{local var="siteRoot"}admin/export.php">Export Data</a></li>';
		print '<li class="noBorder">&nbsp;</li>';
		print '</ul>';
	print '</li>';
}

if (!is_empty($engine->localVars("projectName"))) {
	print '<li>Current Project: '.$engine->localVars("projectName").'</li>';
}

print '<li><a href="{local var="siteRoot"}admin/selectProject.php">Select Project</a></li>';
print '<li class="noBorder">&nbsp;</li>';

if (!is_empty($engine->localVars("projectID"))) {

	print '<li><a href="{local var="siteRoot"}admin/forms.php">Forms</a>';

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' ORDER BY label",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['affectedRows'] > 0) {
		print '<ul>';
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			print '<li>';
			print '<a href="editForm.php?form='.$row['formName'].'">';
			print $row['label'];
			print '</a>';
			print '</li>';
		}
		print '</ul>';
	}

	print '</li>';

}

print '<li class="noBorder">&nbsp;</li>';
print '<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>';

print '</ul>';
?>
