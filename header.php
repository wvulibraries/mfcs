<?php
include("newEngine.php");

$engine->localVars('pageTitle',"Metadata Form Creation System");

recurseInsert("vars.php","php");

// $engine->eTemplate("load","systems");
$engine->eTemplate("include","header");

recurseInsert("acl.php","php");


// Redirect if no project ID set
$pages = array("forms");
foreach ($pages as $page) {
	if (strpos($_SERVER['PHP_SELF'],$page) !== FALSE && is_empty($engine->localVars("projectID"))) {
		header("Location: ".$engine->localVars("siteRoot")."selectProject.php?refer=".$_SERVER['PHP_SELF']);
	}
}
?>
