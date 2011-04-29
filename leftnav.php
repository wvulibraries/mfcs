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

<!-- <li><a href="{local var="siteRoot"}selectProject.php">Select Project</a></li> -->
<li><a href="{local var="siteRoot"}search.php">Search</a></li>

<?php
if (!is_empty($engine->localVars("projectID"))) {
	
	// Switch to system database
	$engine->openDB->select_db($engine->localVars("dbName"));

	print '<li class="noBorder">&nbsp;</li>';

	$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='record' AND parentForm='0' ORDER BY label",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['affectedRows'] > 0) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

			print '<li class="bold">'.$row['label'];
			print '<ul>';
			print '<li><a href="displayForm.php?form='.$row['formName'].'&display=insert">Insert</a></li>';
			print '<li><a href="displayForm.php?form='.$row['formName'].'&display=update">Update</a></li>';
			print '</ul>';
			print '</li>';

		}
	}
	else {
		print '<li>No Forms Created</li>';
	}

	print '<li class="noBorder">&nbsp;</li>';

	$sql = sprintf("SELECT DISTINCT groupName FROM %s WHERE projectID='%s' AND formType='metadata' AND parentForm='0' ORDER BY groupName",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if ($sqlResult['affectedRows'] > 0) {
		print '<li class="bold">';
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			
			if (!is_empty($row['groupName'])) {
				print $row['groupName'];
			}
			else {
				print "Metadata Forms";
			}

			print '<ul>';
			
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

			print '</ul>';

		}
		print '</li>';
	}
	// else {
	// 	print '<li>No Forms Created</li>';
	// }
	// print '</ul>';

	// Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

}

print '<li class="noBorder">&nbsp;</li>';
print '<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>';

print '</ul>';
?>
