<?php
include("../header.php");

recurseInsert("acl.php","php");

try {

	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}

	if (!isset($engine->cleanGet['MYSQL']['formID']) || is_empty($engine->cleanGet['MYSQL']['formID']) || !validate::integer($engine->cleanGet['MYSQL']['formID'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Form ID Provided.");
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
	
	$project       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	localvars::add("projectName",$row['projectName']);

	$builtForm = buildForm($engine->cleanGet['MYSQL']['formID']);
	if ($builtForm === FALSE) {
		throw new Exception('Error');
	}

	localvars::add("form",$builtForm);

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

	<p>Built up left nav will go here</p>

</div>
<div id="right">

	{local var="form"}

</div>


</section>


<?php
$engine->eTemplate("include","footer");
?>
