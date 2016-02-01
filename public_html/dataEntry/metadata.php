<?php
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

if (isset($engine->cleanGet['HTML']['ajax']) && strtolower($engine->cleanGet['HTML']['ajax']) == "true") {
	$ajax = TRUE;
}
else {
	$ajax = FALSE;
}

$permissions      = TRUE;

try {

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (forms::isProductionReady($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Form is not production ready.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		throw new Exception("Object form provided (Metadata forms only).");
	}

	// if an object ID is provided make sure the object is from this form
	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& !checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form");
	}

	localvars::add("formName",$form['title']);

	// handle submission
	$return = NULL;
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {

		log::insert("Data Entry: Metadata: Submit",0,$engine->cleanGet['MYSQL']['formID']);

		$return = forms::submit($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {

		log::insert("Data Entry: Metadata: Update",0,$engine->cleanGet['MYSQL']['formID']);

		$return = forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateEdit'])) {

		log::insert("Data Entry: Metadata: Update Edit",0,$engine->cleanGet['MYSQL']['formID']);

		$return = forms::submitEditTable($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}

	if (!isnull($return) && $ajax === TRUE) {
		die(displayMessages());
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
	log::insert("Data Entry: Metadata: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

log::insert("Data Entry: Metadata: View Page");

localVars::add("results",displayMessages());

if (!$ajax) {
	// Display warning if form is not part of current project
	forms::checkFormInCurrentProjects($engine->cleanGet['MYSQL']['formID']);
	$engine->eTemplate("include","header");
}
?>

{local var="projectWarning"}

<section>
	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	<?php if (!$ajax) { ?>
	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li class="pull-right"><a href="{local var="siteRoot"}formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
	</ul>
	<?php } ?>

	{local var="results"}

	<?php if ($permissions === TRUE) { ?>

	<div class="row-fluid">
		{local var="form"}
		<hr>
		{local var="metadataEditTable"}
	</div>

	<?php } ?>
</section>


<?php
if (!$ajax) {
	$engine->eTemplate("include","footer");
}
?>
