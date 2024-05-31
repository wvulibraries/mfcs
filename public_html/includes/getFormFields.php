<?php
include("../header.php");

$fields = array();

$sql = sprintf("SELECT ID,fields FROM `forms`");
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysqli_fetch_array($sqlResult['result'], MYSQLI_ASSOC)) {
		$tmp = decodeFields($row['fields']);
		if (isset($tmp) && is_array($tmp)) {
			$field = array();
			foreach ($tmp as $f) {
				$field[] = array(
					"name"   => isset($f['name'])  ? $f['name']  : '[no name]',
					"label"  => isset($f['label']) ? $f['label'] : '[no label]',
					);
			}
			$fields[$row['ID']] = $field;
		}
	}
}

print json_encode($fields);
?>
