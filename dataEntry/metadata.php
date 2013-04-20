<?php
include("../header.php");

if (isset($engine->cleanGet['HTML']['ajax']) && strtolower($engine->cleanGet['HTML']['ajax']) == "true") {
	$ajax = TRUE;
}
else {
	$ajax = FALSE;
}

try {

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		throw new Exception("Obejct form provided (Metadata forms only).");
	}

	// if an object ID is provided make sure the object is from this form
	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& !checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form");
	}

	localvars::add("formName",$form['title']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateEdit'])) {
		$return = forms::submitEditTable($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}

	// build the form for displaying
	$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building form.");
	}

	$builtEditTable = forms::buildEditTable($engine->cleanGet['MYSQL']['formID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building edit table.");
	}

	localvars::add("form",$builtForm);
	localvars::add("metadataEditTable",$builtEditTable);
	localvars::add("formID",$form['ID']);

	// localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

if ($ajax) {
	die(displayMessages());
}

localVars::add("results",displayMessages());

if (!$ajax) {
	$engine->eTemplate("include","header");
}
?>

<section>
	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a> <span class="divider">/</span></li>
			<li><a href="{local var="siteRoot"}/dataEntry/selectForm.php">Select a Form</a> <span class="divider">/</span></li>
			<li class="pull-right"><a href="{local var="siteRoot"}/formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
		</ul>
	</nav>

	{local var="results"}

	<div class="row-fluid">
		<div class="span9" id="right">
			{local var="form"}
		</div>

		<div class="span9">
			{local var="metadataEditTable"}
		</div>

	</div>
</section>


<?php
if (!$ajax) {
	$engine->eTemplate("include","footer");
}
?>
