<?php
global $engine;
?>

<ul>

<li class="bold">Current Project
	<ul>
		<li>
			<form method="post" action="<?= $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] ?>">

				<select name="projectID">
					<option value="">-- Select a Project --</option>
					<?php
					// Switch to system database
					$engine->openDB->select_db($engine->localVars("dbName"));

					$projIDs = allowedProjects();

					$sql = sprintf("SELECT * FROM %s",
						$engine->openDB->escape($engine->dbTables("projects"))
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult                = $engine->openDB->query($sql);

					if ($sqlResult['result']) {
						while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

							// Do not display if the user does not have permissions
							if (!in_array($row['ID'],$projIDs)) {
								continue;
							}

							if ($engine->localVars("projectID") == $row['ID']) {
								print '<option value="'.$row['ID'].'" selected>'.htmlSanitize(substr($row['label'],0,31)).'</option>';
							}
							else {
								print '<option value="'.$row['ID'].'">'.htmlSanitize(substr($row['label'],0,31)).'</option>';
							}
						}
					}
					?>
				</select>

				{engine name="insertCSRF"}
				<input type="submit" name="selectProjectSubmit" value="Select Project" />

			</form>
		</li>
	</ul>
</li>
<li class="noBorder">&nbsp;</li>

<?php
if (checkGroup("libraryWeb_mfcs_admin")) {
	print '<li>MFCS Admin';
		print '<ul>';
		print '<li><a href="{local var="siteRoot"}admin/projects.php">Projects</a></li>';
		print '<li><a href="{local var="siteRoot"}admin/copyProject.php">Copy a Project</a></li>';
		print '<li class="noBorder">&nbsp;</li>';
		print '</ul>';
	print '</li>';
}

if (!is_empty($engine->localVars("projectID"))) {

	print '<li><a href="{local var="siteRoot"}admin/forms.php">Forms</a>';

	$sql = sprintf("SELECT DISTINCT groupName FROM %s WHERE projectID='%s' AND parentForm='0' ORDER BY groupName",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);

	if ($sqlResult['affectedRows'] > 0) {
		print '<ul>';
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

			if (!is_empty($row['groupName'])) {
				print '<li>'.htmlSanitize($row['groupName']);
				print '<ul>';
			}

			// top level forms
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND groupName='%s' AND parentForm='0' ORDER BY label",
				$engine->openDB->escape($engine->dbTables("forms")),
				$engine->openDB->escape($engine->localVars("projectID")),
				$engine->openDB->escape($row['groupName'])
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult2               = $engine->openDB->query($sql);

			if ($sqlResult2['result']) {
				while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
					print '<li>';
					print '<a href="editForm.php?form='.$row2['formName'].'">';
					print htmlSanitize($row2['label']);
					print '</a>';

					// List sub forms
					$sql = sprintf("SELECT * FROM %s WHERE parentForm='%s'",
						$engine->openDB->escape($engine->dbTables("forms")),
						$engine->openDB->escape($row2['ID'])
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult3               = $engine->openDB->query($sql);

					if ($sqlResult3['affectedRows'] > 0) {
						print '<ul>';
						while ($row3 = mysql_fetch_array($sqlResult3['result'], MYSQL_ASSOC)) {
							print '<li>';
							print '<a href="editForm.php?form='.$row3['formName'].'">';
							print htmlSanitize($row3['label']);
							print '</a>';
							print '</li>';
						}
						print '</ul>';
					}


					print '</li>';
				}
			}

			if (!is_empty($row['groupName'])) {
				print '</ul>';
				print '</li>';
			}

		}
		print '</ul>';
	}

	print '</li>';

}

print '<li class="noBorder">&nbsp;</li>';
print '<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>';

print '</ul>';
?>
