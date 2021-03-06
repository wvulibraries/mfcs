<?php
include("../../../../header.php");

$permissions = TRUE;
$deleted     = FALSE;

try {

	if (($validate_return = valid::validate(array("objectID_required"=>true, "metedata"=>true,"authtype"=>"editor","productionReady"=>true))) !== TRUE) {
		$permissions = FALSE;
		throw new Exception($validate_return);
	}

	// handle submission
	if (isset($engine->cleanGet['MYSQL']['confirm']) &&
		$engine->cleanGet['MYSQL']['confirm'] == $engine->cleanGet['MYSQL']['objectID']) {

		$deleted = objects::delete($engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID']);

	}
	else {
		// @todo: This should really be in the object class, but since i'm back porting to production
		// i'm trying to avoid modifying the classes as much as possible.
		$object_title_field = forms::getObjectTitleField($engine->cleanGet['MYSQL']['formID']);
		$object             = objects::get($engine->cleanGet['MYSQL']['objectID']);

		localvars::add("metadata_title",$object['data'][$object_title_field]);

	}

	localvars::add("form_title", forms::title($engine->cleanGet['MYSQL']['formID']));
	localvars::add("formID",$engine->cleanGet['MYSQL']['formID']);
	localvars::add("objectID",$engine->cleanGet['MYSQL']['objectID']);
	localvars::add("php_self",$_SERVER['PHP_SELF']); // i should back-port the php_Self module to engine 3 (or upgrade MFCS to engine 4)

}
catch(Exception $e) {
	log::insert("Data Entry: Metadata: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

log::insert("Data Entry: Metadata: Delete Page",$engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID']);

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Delete from {local var="form_title"}</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/metadata.php?formID={local var="formID"}">{local var="form_title"}</a></li>
		<?php if ($deleted === FALSE) { ?>
			<li><a href="{local var="siteRoot"}data/metadata/edit/?objectID={local var="objectID"}">Edit Object</a></li>
		<?php } ?>
	</ul>


	{local var="results"}


	<div class="row-fluid">
		<?php if ($permissions === TRUE && $deleted === FALSE) { ?>

		<span class="delete_warning">
			<p>Do you really want to delete the Metadata Object: <strong>{local var="metadata_title"}</strong></p>

			<p>
				Have you confirmed that this metadata item is not linked to an object?
				<a href="{local var="siteRoot"}dataView/list.php?listType=metadataObjects&formID={local var="formID"}&objectID={local var="objectID"}">Linked Objects</a>
			</p>
		</span>

		<a class="delete_metadata_confirm" href="{local var="php_self"}?objectID={local var="objectID"}&confirm={local var="objectID"}"><i class="fa fa-trash"></i>Confirm Delete</a> &nbsp;
		<a class="delete_metadata_cancel" href="{local var="siteRoot"}/data/metadata/edit/?objectID={local var="objectID"}"><i class="fa fa-times"></i>Cancel</a>

		<?php } else { ?>

		<a href="{local var="siteRoot"}dataEntry/metadata.php?formID={local var="formID"}">Return to {local var="form_title"} Form page</a>

		<?php } ?>
	</div>


</section>


<?php
	$engine->eTemplate("include","footer");
?>
