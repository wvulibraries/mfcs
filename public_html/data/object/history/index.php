<?php
include("../../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

// Setup revision control
$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');

try {

	$error = FALSE;

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	localvars::add("objectID",$engine->cleanGet['MYSQL']['objectID']);

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	// anyone that can view the object can see the history
	if (mfcsPerms::isViewer($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	log::insert("Data View: Object History",$engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID']);

	// $revision_history
	revisions::history_created($engine->cleanGet['MYSQL']['objectID']);
	revisions::history_last_modified($engine->cleanGet['MYSQL']['objectID']);

	$history = revisions::history_revision_history($engine->cleanGet['MYSQL']['objectID']);


	$revision_history = "<ul>";
	foreach ($history as $edit) {
		$revision_history .= sprintf("<li><p>Username: %s</p><p>Date: %s</p></li>", $edit[0], $edit[1]);
	}
	$revision_history .= "</ul>";
	localvars::add("revision_history",$revision_history);

	$view_history = "<ul>";
	foreach (log::pull_actions(array("Data View: Object","Data Entry: Object: View Page"),$engine->cleanGet['MYSQL']['objectID']) as $view) {
		$view_history .= sprintf("<li><p>Username: %s</p><p>Date: %s</p></li>", $view[0], $view[1]);
	}
	$view_history .= "</ul>";
	localvars::add("view_history",$view_history);

}
catch(Exception $e) {
	log::insert("Data View: Object History: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
	$error = TRUE;
}

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Object History -- </h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/object.php?objectID={local var="objectID"}">Object Edit Form</a></li>
		<!-- FLoat Right -->
		<?php if (!isnull($engine->cleanGet['MYSQL']['objectID']) and $revisions->hasRevisions($engine->cleanGet['MYSQL']['objectID'])) { ?>
			<li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/revisions/index.php?objectID={local var="objectID"}">Revisions</a></li>
			<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Object-History"> <i class="fa fa-book"></i> Documentation</a></li>
		<?php } ?>
	</ul>

	<div id="history_current">

		<h2>Current Information</h2>

		<p><b>Created by:</b> <em>{local var="createdByUsername"} on {local var="createdOnDate"}</em></p>
							<p><b>Modified by:</b> <em>{local var="modifiedByUsername"} on {local var="modifiedOnDate"}</em></p>

	</div>

	<div id="history_revision_history">
		<h2>Revision History</h2>
		{local var="revision_history"}
	</div>

	<div id="history_views">
		<h2>View History</h2>
		{local var="view_history"}
	</div>
</section>

<?php
$engine->eTemplate("include","footer");
?>