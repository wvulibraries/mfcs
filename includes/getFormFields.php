<?php
include("../header.php");

// recurseInsert("acl.php","php");

$formID = isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : NULL;
$fields = array();

$sql = sprintf("SELECT fields FROM `%s` WHERE ID='%s' LIMIT 1",
	$engine->openDB->escape($engine->dbTables("forms")),
	$engine->openDB->escape($formID)
	);
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

	$tmp = decodeFields($row['fields']));
	foreach ($tmp as $field) {
		$fields[$field['name']] = $field['label'];
	}
}

print json_encode($fields);
?>
