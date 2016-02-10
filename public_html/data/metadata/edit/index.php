<?php
include("../../../header.php");

$permissions      = TRUE;

try {

	if (($validate_return = valid::validate(array("objectID_required"=>true, "metedata"=>true,"authtype"=>"editor","productionReady"=>true))) !== TRUE) {
		$permissions = FALSE;
		throw new Exception($validate_return);
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

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/metadata.php?formID={local var="formID"}">{local var="formName"}</a></li>
	</ul>


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
