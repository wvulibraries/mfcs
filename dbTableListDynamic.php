<?php
global $engine;

// All system generated tables
$sql = sprintf("SELECT name FROM %s",
	$engine->openDB->escape($engine->dbTables("dbTables"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$engine->dbTables($row['name'],"prod",$row['name']);
	}
}
?>
