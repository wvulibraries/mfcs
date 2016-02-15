<?php
include("../../../header.php");

$permissions = TRUE;
$confirmed   = FALSE;

try {

	if (($validate_return = valid::validate(array("objectID_required"=>true, "metedata"=>false,"authtype"=>"editor","productionReady"=>true))) !== TRUE) {
		$permissions = FALSE;
		throw new Exception($validate_return);
	}

	// handle submission
	if (isset($engine->cleanGet['MYSQL']['confirm']) &&
		$engine->cleanGet['MYSQL']['confirm'] == $engine->cleanGet['MYSQL']['objectID']) {

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
		<h1>Reprocess Object</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
	</ul>


	{local var="results"}


	<div class="row-fluid">
		<?php if ($permissions === TRUE && $confirmed === FALSE) { ?>

		<span class="delete_warning">
			<p>You most likely DO NOT want to do this.</p>

			<p>
				There are very rare circumstances when you will want to reprocess 
				an individual object. Most likely you will want to reprocess ALL 
				objects for a form or project, or reprocess all objects for a 
				specific date range. 
			</p>

			<p>
				If you just uploaded a new version of this file it is already in the processing table to be reprocessed. 
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
