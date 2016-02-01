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

	if (($sanity = checks::is_ok("uniqueIDCheck")) === FALSE) {
		print "<h1>ERROR!</h1>";
		print "<p>MFCS Failed idno sanity check. Please contact systems Immediately.</p>";
		print "<p>Please jot down the steps you took getting to this point. Be as specific as possible.</p>";
		print "<p>Aborting.</p>";

		// notify admins via email

		exit;
	}
	else if (isnull($sanity)) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);

		print "<p>Error checking MFCS sanity. Aborting.</p>";

		exit;
	}

}

// End Checks

$mfcsSearch = new mfcsSearch();

// Load the user's current projects

sessionSet('currentProject',users::loadProjects());

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",mfcs::config("siteRoot"));
localVars::add('pageTitle',mfcs::config("pageTitle"));
localVars::add('pageHeader',mfcs::config("pageHeader"));

$notificationEmails = array("mrbond@mail.wvu.edu"=>"Michael Bond");
?>