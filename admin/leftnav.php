<?php
global $engine;

/*if (strpos($_SERVER['PHP_SELF'],'editForm.php')) {
	?>
	<ul id="draggableFormElements">
		<li>AutoID</li>
		<li>Text</li>
		<li>Select</li>
		<li>Multiselect</li>
		<li>Textarea</li>
		<li>Date</li>
		<li>WYSIWYG</li>
	</ul>
	<?php
}
*/?>

<ul>
	<li><a href="{local var="siteRoot"}admin/selectProject.php">Select Project</a></li>
	<li class="noBorder">&nbsp;</li>
	<li><a href="{local var="siteRoot"}admin/projects.php">Projects</a></li>
	<?php
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
	?>
</ul>