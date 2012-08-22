<?php
include("header.php");

$errorMsg = NULL;

// Form Submission
if(isset($engine->cleanPost['MYSQL']['copyProject_submit'])) {

	if (is_empty($engine->cleanPost['MYSQL']['oldProject'])) {
		$errorMsg .= webHelper_errorMsg("Old Project Name required.");
	}
	if (is_empty($engine->cleanPost['MYSQL']['newProjectName'])) {
		$errorMsg .= webHelper_errorMsg("New Project Name required.");
	}

	$sql = sprintf("SELECT * FROM `%s`",
		$engine->openDB->escape($engine->dbTables("projects"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			if ($row['name'] == $engine->cleanPost['MYSQL']['newProjectName']) {
				$errorMsg .= webHelper_errorMsg("New Project Name must be unique.");
				break;
			}
		}
	}

	$sql = sprintf("SELECT name FROM `%s` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("projects")),
		$engine->cleanPost['MYSQL']['oldProject']
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		$row = mysql_fetch_array($sqlResult['result'], MYSQL_NUM);
		$oldProjName = $row[0];
	}


	if (isnull($errorMsg)) {

		// Insert new project
		$sql = sprintf("INSERT INTO `%s` (name,label) VALUES ('%s','%s')",
			$engine->openDB->escape($engine->dbTables("projects")),
			$engine->cleanPost['MYSQL']['newProjectName'],
			$engine->cleanPost['MYSQL']['newProjectLabel']
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			$newProjID = $sqlResult['id'];
		}

		// Update permissions
		$sql = sprintf("INSERT INTO `%s` (name,type,projectID) SELECT name,type,'%s' FROM `%s` WHERE projectID='%s'",
			$engine->openDB->escape($engine->dbTables("permissions")),
			$engine->openDB->escape($newProjID),
			$engine->openDB->escape($engine->dbTables("permissions")),
			$engine->cleanPost['MYSQL']['oldProject']
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$errorMsg .= webHelper_errorMsg("Error adding permissions.");
		}

		// Update forms
		$sql = sprintf("SELECT * FROM `%s` WHERE projectID='%s'",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->cleanPost['MYSQL']['oldProject']
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

				$sql = sprintf("INSERT INTO `%s` (projectID,formName,label,deletions,formType,releasePublic,parentForm,groupName,insertLocation) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s')",
					$engine->openDB->escape($engine->dbTables("forms")),
					$engine->openDB->escape($newProjID),
					$engine->openDB->escape($row['formName']),
					$engine->openDB->escape($row['label']),
					$engine->openDB->escape($row['deletions']),
					$engine->openDB->escape($row['formType']),
					$engine->openDB->escape($row['releasePublic']),
					$engine->openDB->escape($row['parentForm']),
					$engine->openDB->escape($row['groupName']),
					$engine->openDB->escape($row['insertLocation'])
					);
				$engine->openDB->sanitize = FALSE;
				$sqlResult2 = $engine->openDB->query($sql);

				if (!$sqlResult2['result']) {
					$errorMsg .= webHelper_errorMsg("Failed to copy forms.");
				}
				else {
					$newFormID = $sqlResult2['id'];

					// Update fields
					$sql = sprintf("SELECT * FROM `%s` WHERE formID='%s'",
						$engine->openDB->escape($engine->dbTables("formFields")),
						$engine->openDB->escape($row['ID'])
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult3 = $engine->openDB->query($sql);

					if ($sqlResult3['result']) {
						while ($row3 = mysql_fetch_array($sqlResult3['result'], MYSQL_ASSOC)) {

							$sql = sprintf("INSERT INTO `%s` (formID,fieldName,type,position) VALUES ('%s','%s','%s','%s')",
								$engine->openDB->escape($engine->dbTables("formFields")),
								$engine->openDB->escape($newFormID),
								$engine->openDB->escape($row3['fieldName']),
								$engine->openDB->escape($row3['type']),
								$engine->openDB->escape($row3['position'])
								);
							$engine->openDB->sanitize = FALSE;
							$sqlResult4 = $engine->openDB->query($sql);

							if (!$sqlResult4['result']) {
								$errorMsg .= webHelper_errorMsg("Failed to copy fields.");
							}
							else {
								$newFieldID = $sqlResult4['id'];

								// Update field properties
								$sql = sprintf("SELECT * FROM `%s` WHERE fieldID='%s'",
									$engine->openDB->escape($engine->dbTables("formFieldProperties")),
									$engine->openDB->escape($row3['ID'])
									);
								$engine->openDB->sanitize = FALSE;
								$sqlResult5 = $engine->openDB->query($sql);

								if ($sqlResult5['result']) {
									while ($row5 = mysql_fetch_array($sqlResult5['result'], MYSQL_ASSOC)) {

										$sql = sprintf("INSERT INTO `%s` (fieldID,`option`,value) VALUES ('%s','%s','%s')",
											$engine->openDB->escape($engine->dbTables("formFieldProperties")),
											$engine->openDB->escape($newFieldID),
											$engine->openDB->escape($row5['option']),
											$engine->openDB->escape($row5['value'])
											);
										$engine->openDB->sanitize = FALSE;
										$sqlResult6 = $engine->openDB->query($sql);

										if (!$sqlResult6['result']) {
											$errorMsg .= webHelper_errorMsg("Failed to copy field properties.");
										}
									}
								}
							}
						}
					}
				}
			}
		}


		if (isnull($errorMsg)) {

			$sql = sprintf("CREATE DATABASE %s%s",
				$engine->openDB->escape($engine->localVars("dbPrefix")),
				$engine->cleanPost['MYSQL']['newProjectName']
				);
			$engine->openDB->sanitize = FALSE;
			$sqlResult = $engine->openDB->query($sql);

			if ($sqlResult['affectedRows'] < 0) {
				$errorMsg .= webHelper_errorMsg("Error creating new Project".(($sqlResult['errorNumber']=='1007')?" (already exists)":""));
			}

			//Switch to old project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$oldProjName);

			$sql = sprintf("SHOW TABLES");
			$engine->openDB->sanitize = FALSE;
			$sqlResult = $engine->openDB->query($sql);

			//Switch to new project database
			$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->cleanPost['MYSQL']['newProjectName']);

			if ($sqlResult['result']) {
				while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

					$sql = sprintf("CREATE TABLE `%s` LIKE `%s`.`%s`",
						$row['Tables_in_'.$engine->localVars("dbPrefix").$oldProjName],
						$engine->localVars("dbPrefix").$oldProjName,
						$row['Tables_in_'.$engine->localVars("dbPrefix").$oldProjName]
						);
					$engine->openDB->sanitize = FALSE;
					$sqlResult2 = $engine->openDB->query($sql);

					if (!$sqlResult2['result']) {
						$errorMsg .= webHelper_errorMsg("Error creating project tables.");
						break;
					}

					if ($engine->cleanPost['MYSQL']['copyWhat'] == 'structureData') {
						$sql = sprintf("INSERT INTO `%s`.`%s` (SELECT * FROM `%s`.`%s`)",
							$engine->localVars("dbPrefix").$engine->cleanPost['MYSQL']['newProjectName'],
							$row['Tables_in_'.$engine->localVars("dbPrefix").$oldProjName],
							$engine->localVars("dbPrefix").$oldProjName,
							$row['Tables_in_'.$engine->localVars("dbPrefix").$oldProjName]
							);
						$engine->openDB->sanitize = FALSE;
						$sqlResult2 = $engine->openDB->query($sql);

						if (!$sqlResult2['result']) {
							$errorMsg .= webHelper_errorMsg("Error entering data.");
							break;
						}

					}

				}
			}
		}
	}


	if (!isnull($errorMsg)) {

		// Switch to system database
		$engine->openDB->select_db($engine->localVars("dbName"));

		$sql = sprintf("DELETE FROM `%s` WHERE ID='%s'",
			$engine->openDB->escape($engine->dbTables("projects")),
			$engine->openDB->escape($newProjID)
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);
print "<pre>";
print_r($sqlResult);
print "</pre>";

		$sql = sprintf("DELETE FROM `%s` WHERE projectID='%s'",
			$engine->openDB->escape($engine->dbTables("permissions")),
			$engine->openDB->escape($newProjID)
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		$sql = sprintf("DELETE FROM `%s` WHERE projectID='%s'",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($newProjID)
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		$sql = sprintf("DELETE FROM `%s` WHERE formID='%s'",
			$engine->openDB->escape($engine->dbTables("formFields")),
			$engine->openDB->escape($newFormID)
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		$sql = sprintf("DELETE FROM `%s` WHERE fieldID='%s",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($newFieldID)
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

		$sql = sprintf("DROP DATABASE %s IF EXISTS",
			$engine->openDB->escape($engine->localVars("dbPrefix")).$engine->cleanPost['MYSQL']['newProjectName']
		);
		$engine->openDB->sanitize = FALSE;
		$sqlResult = $engine->openDB->query($sql);

	}

	if (isnull($errorMsg)) {
		$errorMsg .= webHelper_successMsg("Project '".$oldProjName."' copied successfully.");
	}

}
// Form Submission

// Switch to system database
$engine->openDB->select_db($engine->localVars("dbName"));

$sql = sprintf("SELECT * FROM `%s`",
	$engine->openDB->escape($engine->dbTables("projects"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$tmp  = '<select name="oldProject">';
	$tmp .= '<option value="">Old Project Name</option>';
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$tmp .= '<option value="'.$row['ID'].'">'.$row['name'].'</option>';
	}
	$tmp .= '</select>';
	$engine->localVars("projs",$tmp);
}



$engine->eTemplate("include","header");
?>

<h2>Copy a Project</h2>

<?php
if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}
?>

<p>
	<form method="post">
		Original {local var="projs"}<br />
		Project Name: <input type="text" id="newProjectName" name="newProjectName" placeHolder="New Project Name" /><br />
		Project Label: <input type="text" id="newProjectLabel" name="newProjectLabel" placeHolder="New Project Label" /><br />
		<input type="radio" id="structure" name="copyWhat" value="structure" checked /> <label for="structure">Copy Structure</label><br />
		<input type="radio" id="structureData" name="copyWhat" value="structureData" /> <label for="structureData">Copy Structure and Data</label><br />
		{engine name="insertCSRF"}
		<input type="submit" name="copyProject_submit" value="Copy" />
	</form>
</p>

<?php
$engine->eTemplate("include","footer");
?>
