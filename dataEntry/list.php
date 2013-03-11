<?php

include("../header.php");

// $idno = dumpStuff("7","1",TRUE);
// print "<pre>";
// var_dump($idno);
// print "</pre>";

recurseInsert("acl.php","php");

try {
	if (!isset($engine->cleanGet['MYSQL']['id'])   
		|| is_empty($engine->cleanGet['MYSQL']['id']) 
		|| !validate::integer($engine->cleanGet['MYSQL']['id'])) {

		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}

	if (!isset($engine->cleanGet['MYSQL']['formID']) 
		|| is_empty($engine->cleanGet['MYSQL']['formID']) 
		|| !validate::integer($engine->cleanGet['MYSQL']['formID'])) {

		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Form ID Provided.");
		throw new Exception('Error');
	}

	// check for edit permissions on the project
	if (checkProjectPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) {
		errorHandle::errorMsg("Permissions denied for working on this project");
		throw new Exception('Error');
	}

	// check that this form is part of the project
	if (!checkFormInProject($engine->cleanGet['MYSQL']['id'],$engine->cleanGet['MYSQL']['formID'])) {
		errorHandle::errorMsg("Form is not part of project.");
		throw new Exception('Error');
	}

	$objects = getAllObjectsForForm($engine->cleanGet['MYSQL']['formID']);

	if ($objects === FALSE) {
		errorHandle::errorMsg("Error retrieving objects.");
		throw new Exception('Error');
	}

	$form = getForm($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		errorHandle::errorMsg("Error retrieving Form.");
		throw new Exception('error');
	}

	localvars::add("listTable",buildListTable($objects,$form,$engine->cleanGet['MYSQL']['id']));

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

	{local var="listTable"}

</div>


</section>


<?php
$engine->eTemplate("include","footer");
?>