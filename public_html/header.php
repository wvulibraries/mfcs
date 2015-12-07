<?php
require("engineInclude.php");

if(!isCLI()) recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/index.php";
mfcs::singleton();

// Quick and dirty Checks check
// @TODO this needs to be more formalized in a class to easily include other checks as well
if (!isCLI()) {
	$sql_check       = sprintf("SELECT `value` FROM `checks` WHERE `name`='uniqueIDCheck'");
	$sqlResult_check = mfcs::$engine->openDB->query($sql_check);

	if (!$sqlResult_check['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);

		print "<p>Error checking MFCS sanity. Aborting.</p>";

		exit;
	}

	$row_check = mysql_fetch_array($sqlResult_check['result'],  MYSQL_ASSOC);

	if ($row_check['value'] == "0") {

	// notify systems via email
		print "<h1>ERROR!</h1>";
		print "<p>MFCS Failed idno sanity check. Please contact systems Immediately.</p>";
		print "<p>Please jot down the steps you took getting to this point. Be as specific as possible.</p>";
		print "<p>Aborting.</p>";
		exit;
	}
}

// End Checks

$mfcsSearch = new mfcsSearch();

// Load the user's current projects

sessionSet('currentProject',users::loadProjects());
recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","mfcsTemplate");

localVars::add("siteRoot",mfcs::config("siteRoot"));
localVars::add('pageTitle',mfcs::config("pageTitle"));
localVars::add('pageHeader',mfcs::config("pageHeader"));

// JSON Object for Projects
localVars::add('userCurrentProjectsJSON', json_encode(users::loadProjects()));
?>