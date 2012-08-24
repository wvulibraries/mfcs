<?php
include("newEngine.php");

$engine->localVars('pageTitle',"Metadata Form Creation System");

// $engine->eTemplate("load","systems");

recurseInsert("includes/phpFunctions.php","php");
recurseInsert("includes/showField.php","php");
recurseInsert("vars.php","php");
recurseInsert("acl.php","php");


$projIDs = allowedProjects();

// Excluded pages
if (strpos($_SERVER['PHP_SELF'],"projects") === FALSE
 && strpos($_SERVER['PHP_SELF'],"permissions") === FALSE) {
	
	if (is_empty($engine->localVars("projectID"))) {
		$engine->eTemplate("include","header");
		print webHelper_errorMsg("No project selected.");
		$engine->eTemplate("include","footer");
		exit;
	}
	else if (!in_array($engine->localVars("projectID"),$projIDs)) {
		$engine->eTemplate("include","header");
		print webHelper_errorMsg("You do not have access to this project.");
		$engine->eTemplate("include","footer");
		exit;
	}

}
?>
