<?php
include("header.php");
?>

<!-- Page Content Goes Above This Line -->

<ul>
<?php
$sql = sprintf("SELECT * FROM %s WHERE userName='%s'",
	$engine->openDB->escape($engine->dbTables("forms")),
	$engine->openDB->escape(sessionGet("username"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		print '<li><a href="editForm.php?name='.$row['formName'].'">'.$row['formName'].'</a></li>';
	}
}
?>
</ul>

<!-- Page Content Goes Above This Line -->

<?php
$engine->eTemplate("include","footer");
?>

