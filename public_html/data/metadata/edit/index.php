<?php
include("../../../header.php");

$permissions      = TRUE;

try {

	if (objects::validID(TRUE) === FALSE) {
		throw new Exception("ObjectID Provided is invalid or missing.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		throw new Exception("Object form provided (Metadata forms only).");
	}

	if (mfcsPerms::isEditor($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (forms::isProductionReady($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Form is not production ready.");
	}

	// if an object ID is provided make sure the object is from this form
	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& !objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form");
	}

	localvars::add("formName",forms::title($engine->cleanGet['MYSQL']['formID']));

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

	// build the form for displaying
	$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building form.");
	}

	localvars::add("form",$builtForm);
	localvars::add("formID",$engine->cleanGet['MYSQL']['formID']);

	// localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

}
catch(Exception $e) {
	log::insert("Data Entry: Metadata: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

log::insert("Data Entry: Metadata: Edit Page",$engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID']);

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
			<li><a href="{local var="siteRoot"}dataEntry/metadata.php?formID={local var="formID"}">{local var="formName"}</a></li>
		</ul>
	</nav>

	{local var="results"}

	<?php if ($permissions === TRUE) { ?>

	<div class="row-fluid">
		{local var="form"}
	</div>

	<?php } ?>
</section>


<?php
	$engine->eTemplate("include","footer");
?>
