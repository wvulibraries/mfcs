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
		$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
		$fields = forms::get_file_fields($object['formID']);

		// start transactions
		if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
			throw new Exception("unable to start database transactions");
		}

		foreach ($fields as $field) {
			if (files::insert_into_processing_table($engine->cleanGet['MYSQL']['objectID'],$field['name']) === FALSE) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();
				throw new Exception("Error inserting for reprocessing.");
			}
		}

		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		$confirmed = TRUE;
	}

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
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/object.php?objectID={local var="objectID"}">Object</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Reprocessing" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
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

		<a class="delete_metadata_confirm" href="{local var="php_self"}?objectID={local var="objectID"}&confirm={local var="objectID"}"><i class="fa fa-arrow-circle-o-right"></i>Confirm Reprocessing</a> &nbsp;

		<?php } else { ?>

		<p>Object inserted for Reprocessing.</p>

		<a href="{local var="siteRoot"}dataEntry/object.php?objectID={local var="objectID"}">Return to Object Editing page</a>

		<?php } ?>
	</div>


</section>


<?php
	$engine->eTemplate("include","footer");
?>
