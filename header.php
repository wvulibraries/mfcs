<?php
require("engineInclude.php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

recurseInsert("includes/functions.php","php");

$engine->eTemplate("load","distribution");

$engine->localVars("siteRoot",$engineVars['WEBROOT']."/mfcs/");
$engine->localVars('pageTitle',"Metadata Form Creation System");
$engine->localVars('pageHeader',"Metadata Form Creation System");
?>
