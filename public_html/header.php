<?php
require("engineInclude.php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/index.php";
mfcs::singleton();

if(!isCLI()) recurseInsert("acl.php","php");

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

$engine->eTemplate("load","mfcsTemplate");

// setup localvars for meta tags
localVars::add("siteRoot",mfcs::config("siteRoot"));
localVars::add('pageTitle',mfcs::config("pageTitle"));
localVars::add('pageTitleMobile',mfcs::config("pageTitleMobile"));
localVars::add('pageHeader',mfcs::config("pageHeader"));

localVars::add("meta_authors", 'WVU Libraries Systems Office');
localVars::add('meta_description', 'MFCS is a system built to store and archive digital projects, finding aids, and historical material entrusted to the library. This system allows us to keep secure data, achive the original, and make modifications for other technologies to use. The material ultimately ends up in a repository system such as Hydra, Islandora, or DLXS.');
localVars::add('meta_keywords', 'Metadata, Storage, Solution, Form, Creation, System, WVU, West Virginia, Libraries, Finding Aids ');


// JSON Object for Projects
localVars::add('userCurrentProjectsJSON', json_encode(users::loadProjects()));

$notificationEmails = array(
	"jetapia@mail.wvu.edu" => "Jessica Tapia",
	"steve.giessler@mail.wvu.edu" => "Steve Giessler", 
	"tam0013@mail.wvu.edu" => "Tracy McCormick"
);
?>
