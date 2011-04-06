<?php
$engineDir = "/home/library/phpincludes/engineAPI/engine";
include($engineDir ."/engine.php");
$engine = new EngineCMS();

recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

recurseInsert("vars.php","php");

recurseInsert("showField.php","php");
recurseInsert("phpFunctions.php","php");


$formName = isset($engine->cleanGet['MYSQL']['formName']) ? $engine->cleanGet['MYSQL']['formName'] : NULL;
$val = isset($engine->cleanGet['MYSQL']['value']) ? $engine->cleanGet['MYSQL']['value'] : NULL;


$sql = sprintf("SELECT fieldName FROM %s WHERE ID='%s' LIMIT 1",
	$engine->openDB->escape($engine->dbTables("formFields")),
	$engine->openDB->escape($val)
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
	
	$engine->openDB->select_db($engine->localVars("dbPrefix").$engine->localVars("projectName"));

	$sql = sprintf("SELECT mfcs_ID, %s AS name FROM %s ORDER BY name",
		$engine->openDB->escape($row['fieldName']),
		$engine->openDB->escape($formName)
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult2               = $engine->openDB->query($sql);
	
	if ($sqlResult2['result']) {
		while ($row2 = mysql_fetch_array($sqlResult2['result'], MYSQL_ASSOC)) {
			print '<option value="'.$row2['mfcs_ID'].'">'.$row2['name'].'</option>';
		}
	}

}
?>
