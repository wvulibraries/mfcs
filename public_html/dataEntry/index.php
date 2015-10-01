<?php

// @TODO is this file ever used? 
// The form select should probably be the index to this directory

include("../header.php");

try {

	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		throw new Exception("No Project ID Provided.");
	}

	// check for edit permissions on the project
	if (projects::checkPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) {
		throw new Exception("Permissions denied for working on this project");
	}

	// Get the project
	$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s'",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		throw new Exception("Error retrieving project.");
	}

	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	localvars::add("projectName",$row['projectName']);
	localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>{local var="projectName"}</h1>
	</header>

	{local var="results"}

	<div class="row-fluid">
		<div class="span3" id="left">
			<p>{local var="leftnav"}</p>
		</div>

		<div class="span9" id="right">
			<p>Other information can go here.</p>
		</div>
	</div>
</section>


<?php
$engine->eTemplate("include","footer");
?>
