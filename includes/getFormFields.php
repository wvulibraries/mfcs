<?php
include("../header.php");

$fields = array();

$sql = sprintf("SELECT ID,fields FROM `forms`");
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$tmp = decodeFields($row['fields']);
		if (isset($tmp) && is_array($tmp)) {
			$field = array();
			foreach ($tmp as $f) {
				$field[] = array(
					"name"   => $f['name'],
					"label"  => $f['label'],
					);
			}
			$fields[$row['ID']] = $field;
		}
	}
}

print json_encode($fields);
?>
