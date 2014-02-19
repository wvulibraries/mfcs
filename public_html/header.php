<?php
require("engineInclude.php");

if(!isCLI()) recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/mfcs.php";
require_once "includes/forms.php";
require_once "includes/list.php";
require_once "includes/navigation.php";
require_once "includes/objects.php";
require_once "includes/permissions.php";
require_once "includes/projects.php";
require_once "includes/search.php";
require_once "includes/stats.php"; 
require_once "includes/users.php";
require_once "includes/files.php";
require_once "includes/duplicates.php";
require_once "includes/revisions.php";
require_once "includes/exporting.php";
mfcs::singleton();

$mfcsSearch = new mfcsSearch();

// Load the user's current projects

sessionSet('currentProject',users::loadProjects());

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",mfcs::config("siteRoot"));
localVars::add('pageTitle',mfcs::config("pageTitle"));
localVars::add('pageHeader',mfcs::config("pageHeader"));
?>