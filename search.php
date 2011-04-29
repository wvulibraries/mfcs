<?php
include("header.php");

$errorMsg = NULL;
$fields   = array();
$options  = '<option value="null">-- All Fields --</option>';

$sql = sprintf("SELECT fields.*, forms.formName FROM %s AS fields LEFT JOIN %s AS forms ON forms.ID=fields.formID WHERE forms.projectID='%s' AND forms.formType='record' AND forms.parentForm='0' AND fields.type!='select' AND fields.type!='multiselect' ORDER BY fields.position",
	$engine->openDB->escape($engine->dbTables("formFields")),
	$engine->openDB->escape($engine->dbTables("forms")),
	$engine->openDB->escape($engine->localVars("projectID"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		
		$formName = $row['formName'];

		$fields[$row['position']]['ID']   = $row['ID'];
		$fields[$row['position']]['type'] = $row['type'];
		$fields[$row['position']]['name'] = $row['fieldName'];
		
		$sql = sprintf("SELECT value AS fieldLabel FROM %s WHERE fieldID='%s' AND `option`='fieldLabel' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("formFieldProperties")),
			$engine->openDB->escape($row['ID'])
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult2               = $engine->openDB->query($sql);
		
		if ($sqlResult2['result']) {
			$row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC);

			$fields[$row['position']]['label'] = $row2['fieldLabel'];
			$options .= '<option value="'.htmlSanitize($row['fieldName']).'">'.htmlSanitize($row2['fieldLabel']).'</option>';

		}
		

	}
}


$engine->eTemplate("include","header");

// Form Submission
if(isset($engine->cleanPost['MYSQL']['searchSubmit'])) {

	$where = NULL;
	if (isnull($engine->cleanPost['MYSQL']['searchField'])) {
		// query all fields
		foreach ($fields as $field) {
			$where .= (isnull($where)?"WHERE ":" OR ")."`".$field['name']."` LIKE '%".$engine->cleanPost['MYSQL']['searchText']."%'";
		}
	}
	else {
		// query selected field
		$where = "WHERE `".$engine->cleanPost['MYSQL']['searchField']."` LIKE '%".$engine->cleanPost['MYSQL']['searchText']."%'";
	}

	//Switch to project database
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	$sql = sprintf("SELECT * FROM %s %s",
		$engine->openDB->escape($formName),
		$where
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	
	if (!$sqlResult['result'] || $sqlResult['affectedRows'] == 0) {
		print webHelper_errorMsg("No Search Results");
	}
	else {

		print '<link rel="stylesheet" type="text/css" media="screen" href="{local var="siteRoot"}includes/tables.css" />';

		print '<h2>Search Results</h2>';
		print '<table id="searchResults">';

		if (isnull($engine->cleanPost['MYSQL']['searchField'])) {

			print '<tr>';
			foreach ($fields as $position => $field) {
				print '<th>'.$field['label'].'</th>';
			}
			print '</tr>';

			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				
				print '<tr>';
				foreach ($fields as $position => $field) {
					print '<td>';
					if ($position == 0) {
						print '<a href="{local var="siteRoot"}displayForm.php?proj={local var="projectID"}&form='.$formName.'&display=updateinsert&id='.$row['mfcs_ID'].'">';
					}
					print $row[$field['name']];
					if ($position == 0) {
						print '</a>';
					}
					print '</td>';
				}
				print '</tr>';
				
			}

		}
		else {

			print '<tr>';
			foreach ($fields as $position => $field) {
				if ($field['type'] == 'identifier') {
					print '<th>'.$field['label'].'</th>';
					break;
				}
			}
			foreach ($fields as $position => $field) {
				if ($field['name'] == $engine->cleanPost['MYSQL']['searchField']) {
					print '<th>'.$field['label'].'</th>';
				}
			}
			print '</tr>';

			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
				print '<tr>';
				foreach ($fields as $position => $field) {
					if ($field['type'] == 'identifier') {
						print '<td>';
						print '<a href="{local var="siteRoot"}displayForm.php?proj={local var="projectID"}&form='.$formName.'&display=updateinsert&id='.$row['mfcs_ID'].'">';
						print $row[$field['name']];
						print '</a>';
						print '</td>';
						break;
					}
				}
				print '<td>'.$row[$engine->cleanPost['MYSQL']['searchField']].'</td>';
				print '</tr>';
			}

		}

		print '</table>';

	}
	
}
else {

	print "<h2>Search</h2>";

	if (!is_empty($errorMsg)) {
		print $errorMsg."<hr />";
	}
	?>

	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
		<input type="text" name="searchText" />
		<select name="searchField">
			<?= $options; ?>
		</select>
		<input type="submit" name="searchSubmit" value="Search" />
		{engine name="insertCSRF"}
	</form>

	<?php
}

$engine->eTemplate("include","footer");
?>
