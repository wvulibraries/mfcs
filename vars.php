<?php
global $engine;

// Get project ID and form name -- Priority: Post, Get, Session
$projID = isset($engine->cleanPost['MYSQL']['projectID'])?$engine->cleanPost['MYSQL']['projectID']:(isset($engine->cleanGet['MYSQL']['proj'])?$engine->cleanGet['MYSQL']['proj']:sessionGet("mfcsProjectID"));
$formName = isset($engine->cleanPost['MYSQL']['form'])?$engine->cleanPost['MYSQL']['form']:(isset($engine->cleanGet['MYSQL']['form'])?$engine->cleanGet['MYSQL']['form']:sessionGet("mfcsFormName"));

// Set session variables
if (!is_empty($projID)) {
	sessionSet("mfcsProjectID",$projID);
}
if (!is_empty($formName)) {
	sessionSet("mfcsFormName",$formName);
}

// Set local variables
$engine->localVars("siteRoot","/scott/mfcs/");
$engine->localVars("dbName","mfcs");
$engine->localVars("dbPrefix","mfcs_");
$engine->localVars("queryString",is_empty($_SERVER['QUERY_STRING'])?'':'?'.$_SERVER['QUERY_STRING']);
$engine->localVars("projectID",sessionGet("mfcsProjectID"));
$engine->localVars("formName",sessionGet("mfcsFormName"));

// Set DB variables
$engine->localVars("dbUsername","mfcs");
$engine->localVars("dbPassword","oc61631");
$engine->localVars("dbServer",$engineVars['mysql']['server']);
$engine->localVars("dbPort",$engineVars['mysql']['port']);

// Initialize database connection
recurseInsert("dbTableList.php","php");
$engine->openDB = new engineDB($engine->localVars("dbUsername"),$engine->localVars("dbPassword"),$engine->localVars("dbServer"),$engine->localVars("dbPort"),$engine->localVars("dbName"));
recurseInsert("dbTableListDynamic.php","php");


// Get more info about project and form, assign to local variables
if (!is_empty($engine->localVars("projectID"))) {
	$sql = sprintf("SELECT name FROM %s WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("projects")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
	$engine->localVars("projectName",$row['name']);
}
if (!is_empty($engine->localVars("formName"))) {
	$sql = sprintf("SELECT ID,label FROM %s WHERE formName='%s' AND projectID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("forms")),
		$engine->openDB->escape($engine->localVars("formName")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
	$engine->localVars("formID",$row['ID']);
	$engine->localVars("formLabel",$row['label']);
}
?>
