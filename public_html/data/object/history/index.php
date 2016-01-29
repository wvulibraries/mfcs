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

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
			<!-- FLoat Right -->
			<?php if(mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID'])){ ?>
			<li class="pull-right noDivider"><a href="{local var="siteRoot"}formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
			<?php
			}
			if (!isnull($engine->cleanGet['MYSQL']['objectID']) and $revisions->hasRevisions($engine->cleanGet['MYSQL']['objectID'])) { ?>
				<li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/revisions/index.php?objectID={local var="objectID"}">Revisions</a></li>
			<?php } ?>
			<li class="pull-right noDivider"><a href="{phpself query="true"}&unlock=cancel">Cancel Edit &amp; Unlock object</a></li>
			<li class="pull-right noDivider"><a href="/data/object/history/">History</a></li>
		</ul>
	</nav>

	<div>

		<h2>Current Information</h2>

		<p>Created by:  {local var="createdByUsername"}  on {local var="createdOnDate"}</p>

		<p>Last Modified by: {local var="modifiedByUsername"} on {local var="modifiedOnDate"}</p>

	</div>

	<div id="history_revision_history">
		<h2>Revision History</h2>
		{local var="revision_history"}
	</div>
</section>

<?php
$engine->eTemplate("include","footer");
?>