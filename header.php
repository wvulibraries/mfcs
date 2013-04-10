<?php
require("engineInclude.php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/mfcs.php";
mfcs::singleton();

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",$engineVars['WEBROOT']."/mfcs/");
localVars::add('pageTitle',"Metadata Form Creation System");
localVars::add('pageHeader',"Metadata Form Creation System");
?>
