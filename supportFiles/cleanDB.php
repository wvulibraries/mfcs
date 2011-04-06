<?php
$engineDir = "/home/library/phpincludes/engineAPI/engine";
include($engineDir ."/engine.php");
$engine = new EngineCMS();

$engine->localVars("dbUsername","mfcs");
$engine->localVars("dbPassword","oc61631");
$engine->localVars("dbServer",$engineVars['mysql']['server']);
$engine->localVars("dbPort",$engineVars['mysql']['port']);

recurseInsert("dbTableList.php","php");
$engine->openDB = new engineDB($engine->localVars("dbUsername"),$engine->localVars("dbPassword"),$engine->localVars("dbServer"),$engine->localVars("dbPort"),"mfcs");


$sqlResult = $engine->openDB->query("SELECT * FROM projects");

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$sql = sprintf("DROP DATABASE IF EXISTS `mfcs_%s`",
			$engine->openDB->escape($row['name'])
			);
		$sqlResult2 = $engine->openDB->query($sql);
	}
}

$sqlResult = $engine->openDB->query("TRUNCATE TABLE dbTables");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE formFieldProperties");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE formFields");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE forms");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE permissions");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE projects");
$sqlResult = $engine->openDB->query("TRUNCATE TABLE userPermissions");

?>
