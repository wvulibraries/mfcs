<?php
include("../header.php");

recurseInsert("acl.php","php");

try {

	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}

	// check for edit permissions on the project
	if (checkProjectPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) {
		errorHandle::errorMsg("Permissions denied for working on this project");
		throw new Exception('Error');
	}

	// Get the project
	$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s'",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving project.");
		throw new Exception('Error');
	}

	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	localvars::add("projectName",$row['projectName']);
	localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>{local var="projectName"}</h1>
	</header>

	{local var="results"}

	<div id="left">
		<p>{local var="leftnav"}</p>
	</div>

	<div id="right">
		<p>Other information can go here.</p>
	</div>
</section>


<?php
$engine->eTemplate("include","footer");
?>
