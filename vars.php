<?php
global $engine;

// ----------
// User defined local variables
// ----------

// Path to site
$engine->localVars("siteRoot",$engineVars['WEBROOT']."/scott/mfcs/");

// The name of the system database
$engine->localVars("dbName","mfcs");

// Prefix for project databases
$engine->localVars("dbPrefix","mfcs_");


// ----------
// User defined database variables
// ----------

// DB User with "Select|Insert|Update|Delete|Create|Drop|Alter|Create temp" permissions
$engine->localVars("dbUsername","mfcs");

// Password for user
$engine->localVars("dbPassword","oc61631");

// Database server (usually localhost)
$engine->localVars("dbServer",$engineVars['mysql']['server']);

// Database port (default is 3306)
$engine->localVars("dbPort",$engineVars['mysql']['port']);


// ----------
// System defined variables -- No need to modify further
// ----------

// Get project ID and form name -- Priority: Post, then Get
$projID = isset($engine->cleanPost['MYSQL']['projectID'])?$engine->cleanPost['MYSQL']['projectID']:(isset($engine->cleanGet['MYSQL']['proj'])?$engine->cleanGet['MYSQL']['proj']:sessionGet("mfcsProjectID"));
$formName = isset($engine->cleanPost['MYSQL']['form'])?$engine->cleanPost['MYSQL']['form']:(isset($engine->cleanGet['MYSQL']['form'])?$engine->cleanGet['MYSQL']['form']:NULL);

// Set session variable
// if (!is_empty($projID)) {
	sessionSet("mfcsProjectID",$projID);  // Project ID in session
// }

$engine->localVars("queryString",is_empty($_SERVER['QUERY_STRING'])?'':'?'.$_SERVER['QUERY_STRING']);
$engine->localVars("projectID",sessionGet("mfcsProjectID"));
$engine->localVars("formName",$formName);

// System database info
recurseInsert("dbTableList.php","php");

// Manually connect with elevated permissions
$engine->openDB = new engineDB($engine->localVars("dbUsername"),$engine->localVars("dbPassword"),$engine->localVars("dbServer"),$engine->localVars("dbPort"),$engine->localVars("dbName"));

// Dynamic list of tables in the system
recurseInsert("dbTableListDynamic.php","php");


// Get more info about project and form, assign to local variables
if (!is_empty($engine->localVars("projectID"))) {
	
	$sql = sprintf("SELECT name, label FROM %s WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("projects")),
		$engine->openDB->escape($engine->localVars("projectID"))
		);
	$engine->openDB->sanitize = FALSE;
	$sqlResult                = $engine->openDB->query($sql);
	$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
	
	$engine->localVars("projectName",$row['name']);
	$engine->localVars("projectLabel",$row['label']);


	if (!is_empty($engine->localVars("formName"))) {
		
		$sql = sprintf("SELECT ID,label FROM %s WHERE formName='%s' AND projectID='%s' LIMIT 1",
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($engine->localVars("formName")),
			$engine->openDB->escape($engine->localVars("projectID"))
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
		
		if ($sqlResult['affectedRows'] == 1) {

			$engine->localVars("formID",$row['ID']);
			$engine->localVars("formLabel",$row['label']);

		}
		else {
			
			// Display error if project/form doesn't exist			
			$engine->eTemplate("include","header");
			print webHelper_errorMsg("Invalid Project and Form combination.");
			$engine->eTemplate("include","footer");
			exit;

		}

	}
}
?>
