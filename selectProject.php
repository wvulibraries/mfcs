<?php
include("header.php");

// Form submission is handled in header.php

$action  = isset($engine->cleanGet['MYSQL']['refer'])?$engine->cleanGet['MYSQL']['refer']:$_SERVER['PHP_SELF'];
$projIDs = allowedProjects();
?>

<!-- Page Content Goes Above This Line -->

<form method="post" action="<?= $action ?>">

	<select name="projectID">
		<option value="">-- Select a Project --</option>
		<?php
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
					print '<option value="'.$row['ID'].'" selected>'.$row['name'].'</option>';
				}
				else {
					print '<option value="'.$row['ID'].'">'.$row['name'].'</option>';
				}
			}
		}
		?>
	</select>

	{engine name="insertCSRF"}
	<input type="submit" name="selectProjectSubmit" value="Select Project" />

</form>

<!-- Page Content Goes Above This Line -->

<?php
$engine->eTemplate("include","footer");
?>

