<?php
global $engine;
?>

<ul>
	<li><a href="{local var="siteRoot"}selectProject.php">Select Project</a></li>
	
	<?php
	if (!is_empty($engine->localVars("projectID"))) {
		?>
		<li class="noBorder">&nbsp;</li>
		<li>Records
			<?php
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='record'",
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
			?>
		</li>

		<li class="noBorder">&nbsp;</li>
		<li>Metadata
			<?php
			$sql = sprintf("SELECT * FROM %s WHERE projectID='%s' AND formType='metadata'",
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
			?>
		</li>
		<?php
	}
	?>
</ul>
