<?php
include("../header.php");

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

	if (isset($engine->cleanGet['MYSQL']['objectID']) 
		&& (is_empty($engine->cleanGet['MYSQL']['objectID']) 
			|| !validate::integer($engine->cleanGet['MYSQL']['objectID']))
		) {

		errorHandle::newError(__METHOD__."() - ObjectID Provided is invalid", errorHandle::DEBUG);
		errorHandle::errorMsg("ObjectID Provided is invalid..");
		throw new Exception('Error');
	}
	else if (!isset($engine->cleanGet['MYSQL']['objectID'])) {
		$engine->cleanGet['MYSQL']['objectID'] = NULL;
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

	// if an object ID is provided make sure the object is from this form
	if (isset($engine->cleanGet['MYSQL']['objectID']) 
		&& !checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		errorHandle::errorMsg("Object not from this form");
		throw new Exception('Error');
	}

	// Get the project
	$project = getProject($engine->cleanGet['MYSQL']['id']);
	if ($project === FALSE) {
		errorHandle::errorMsg("Error retrieving project.");
		throw new Exception('Error');
	}

	localvars::add("projectName",$project['projectName']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		$return = submitForm($project,$engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			errorHandle::errorMsg("Error Submitting Form.");
			throw new Exception('Error');
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		$return = submitForm($project,$engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			errorHandle::errorMsg("Error Updating Form.");
			throw new Exception('Error');
		}
	}

	// build the form for displaying

	$builtForm = buildForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['id'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		errorHandle::errorMsg("Error building form.");
		throw new Exception('Error');
	}

	localvars::add("form",$builtForm);

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

	{local var="leftnav"}

</div>
<div id="right">

	{local var="form"}

</div>


</section>


<?php
$engine->eTemplate("include","footer");
?>
