<?php
include("newEngine.php");

$engine->localVars('pageTitle',"Metadata Form Creation System");

// $engine->eTemplate("load","systems");

recurseInsert("vars.php","php");
recurseInsert("includes/phpFunctions.php","php");
recurseInsert("includes/showField.php","php");

recurseInsert("acl.php","php");


// Redirect if no permission to project ID, except for pages listed
$projIDs = allowedProjects();
if (!in_array($engine->localVars("projectID"),$projIDs)) {
	if (strpos($_SERVER['PHP_SELF'],"selectProject") === FALSE
	 && strpos($_SERVER['PHP_SELF'],"permissions") === FALSE
	 && strpos($_SERVER['PHP_SELF'],"projects") === FALSE) {
		header("Location: selectProject.php?refer=".$_SERVER['PHP_SELF']);
	}
}

// Redirect from these pages if no project ID set
foreach (array("forms","editForm","displayForm") as $page) {
	if (strpos($_SERVER['PHP_SELF'],$page) !== FALSE && is_empty($engine->localVars("projectID"))) {
		header("Location: selectProject.php?refer=".$_SERVER['PHP_SELF']);
	}
}
?>
