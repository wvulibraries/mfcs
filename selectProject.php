<?php
include("header.php");

// Form submission is handled in header.php

$action  = isset($engine->cleanGet['MYSQL']['refer'])?$engine->cleanGet['MYSQL']['refer']:$_SERVER['PHP_SELF'];
$projIDs = allowedProjects();


$engine->eTemplate("include","header");

if (is_empty($projIDs)) {
	print webHelper_errorMsg("You do not have access to any projects.");
}
else {
	
	?>
	<form method="post" action="<?= $action ?>">

		<select name="projectID">
			<option value="">-- Select a Project --</option>
			<?php
			// Switch to system database
			$engine->openDB->select_db($engine->localVars("dbName"));

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
	<?php

}

$engine->eTemplate("include","footer");
?>

