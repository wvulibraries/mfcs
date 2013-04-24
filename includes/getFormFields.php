<?php
include("../header.php");

// recurseInsert("acl.php","php");

$formID = isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : NULL;
$fields = array();

$sql = sprintf("SELECT fields FROM `forms` WHERE ID='%s' LIMIT 1",
	$engine->openDB->escape($formID)
	);
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

	$tmp = decodeFields($row['fields']);
	if (isset($tmp) && is_array($tmp)) {
		foreach ($tmp as $field) {
			$fields[] = array(
				"name"   => $field['name'],
				"label"  => $field['label'],
				);
		}
	}
}

print json_encode($fields);
?>
