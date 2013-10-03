<?php
require("engineInclude.php");

recurseInsert("acl.php","php");

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
mfcs::singleton();

$mfcsSearch = new mfcsSearch();

// Load the user's current projects

sessionSet('currentProject',users::loadProjects());

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",$engineVars['WEBROOT']."/mfcs/");
localVars::add('pageTitle',"Metadata Form Creation System");
localVars::add('pageHeader',"Metadata Form Creation System");
?>