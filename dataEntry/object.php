<?php
include("../header.php");
recurseInsert("acl.php","php");

// Setup revision control
$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');

$selectedProjects = NULL;
$parentObject     = NULL;
$permissions      = TRUE;

try {

	$error = FALSE;

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (mfcsPerms::isEditor($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (isset($engine->cleanGet['MYSQL']['parentID']) && objects::validID(TRUE,$engine->cleanGet['MYSQL']['parentID']) === FALSE) {
		throw new Exception("ParentID Provided is invalid.");
	}

	// if an object ID is provided make sure the object is from this form
	if (!isnull($engine->cleanGet['MYSQL']['objectID']) && !objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form.");
	}

	if (($form = forms::get($engine->cleanGet['MYSQL']['formID'])) === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID'])) {
		throw new Exception("Metadata form provided (Object forms only).");
	}

	/* Parent Object 'Stuff' */
	if (isset($engine->cleanGet['MYSQL']['parentID']) && ($parentObject = objects::get($engine->cleanGet['MYSQL']['parentID'])) === FALSE ) {
		throw new Exception("Unable to retrieve parent object");
	}
	/* End Parent Object 'Stuff' */

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
	// Project Tab Stuff
	//////////

	//////////
	// Children Tab Stuff
	if (($formList = listGenerator::generateFormSelectList($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
		errorHandle::errorMsg("Error getting Forms Listing");
		throw new Exception('Error');
	}
	else {
		localvars::add("formList",$formList);
	}
	localVars::add("childrenList",listGenerator::generateChildList($engine->cleanGet['MYSQL']['objectID']));
	// Children Tab Stuff
	//////////

	localvars::add("formName",$form['title']);
	localvars::add("formID",$form['ID']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID']) === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
		// Redirect the user to the 'edit' form. (RAW is used so the URL is built correctly)
		http::redirect(sprintf("?formID=%s&objectID=%s", $engine->cleanGet['RAW']['formID'], localvars::get("newObjectID")), 301);
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']) === FALSE) {
			throw new Exception("Error Updating Form.");
		}
		http::redirect($_SERVER['REQUEST_URI'], 301);
	}
	else if (isset($engine->cleanPost['MYSQL']['projectForm'])) {

		if ($engine->openDB->transBegin("objectProjects") !== TRUE) {
			errorHandle::errorMsg("Database transactions could not begin.");
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		if (!isset($engine->cleanPost['MYSQL']['projects'])) {
			// If no projects are set, we are deleting all the projects
			if (objects::deleteAllProjects($engine->cleanGet['MYSQL']['objectID']) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				throw new Exception("Error removing all projects from Object.");
			}
		}
		else {
			// There are changes.
			// Delete all the old ones
			if (objects::deleteAllProjects($engine->cleanGet['MYSQL']['objectID']) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				throw new Exception("Error removing all projects from Object.");
			}

			// Add All the new ones
			if (objects::addProjects($engine->cleanGet['MYSQL']['objectID'],$engine->cleanPost['MYSQL']['projects']) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				throw new Exception("Error adding projects to Object.");
			}

			$engine->openDB->transCommit();
			$engine->openDB->transEnd();
		}

	}

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
	$error = TRUE;
}

// build the form for displaying
if (forms::validID()) {
	try {
		if (($builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'],$error)) === FALSE) {
			throw new Exception("Error building form.");
		}
		localvars::add("form",$builtForm);
		localvars::add("leftnav",navigation::buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));

		localvars::add("objectID",$engine->cleanGet['MYSQL']['objectID']);

		//////////
		// Project Tab Stuff
		$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
		localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
		// Project Tab Stuff
		//////////
	}
	catch (Exception $e) {
		errorHandle::errorMsg($e->getMessage());
	}
}

localVars::add("results",displayMessages());

// Display warning if form is not part of current project
forms::checkFormInCurrentProjects($engine->cleanGet['MYSQL']['formID']);

localvars::add("actionHeader",(isnull($engine->cleanGet['MYSQL']['objectID']))?"Add":"Edit");
localvars::add("parentHeader",(isnull($parentObject))?"":"<h2>Adding Child to Parent '".$parentObject['data'][$form['objectTitleField']]."'</h2>");

$engine->eTemplate("include","header");
?>

{local var="projectWarning"}

<section>
	<header class="page-header">
		<h1>{local var="actionHeader"} Object - {local var="formName"}</h1>
		{local var="parentHeader"}
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/dataEntry/selectForm.php">Select a Form</a></li>
			<!-- FLoat Right -->
			<?php if(mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID'])){ ?>
			<li class="pull-right noDivider"><a href="{local var="siteRoot"}formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
			<?php
			}
			if (!isnull($engine->cleanGet['MYSQL']['objectID']) and $revisions->hasRevisions($engine->cleanGet['MYSQL']['objectID'])) { ?>
				<li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/revisions.php?objectID={local var="objectID"}">Revisions</a></li>
			<?php } ?>
		</ul>
	</nav>

	<div class="container-fluid">
		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<?php if ($permissions === TRUE) { ?>

			<div class="row-fluid">
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
						<li><a data-toggle="tab" href="#project">Project</a></li>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<li><a data-toggle="tab" href="#children">Children</a></li>
						<?php } ?>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}
					</div>

					<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
						<div class="tab-pane" id="project">
							<h2>Change Project Membership</h2>

							<form action="{phpself query="true"}" method="post">
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
							</form>
						</div>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<div class="tab-pane" id="children">

								<div class="accordion" id="accordion2">
									<div class="accordion-group">
										<div class="accordion-heading">
											<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
												Add a Child Object
											</a>
										</div>
										<div id="collapseOne" class="accordion-body collapse">
											<div class="accordion-inner">
												Select a Form:

												{local var="formList"}
											</div>
										</div>
									</div>
								</div>

								<section>
									<header>
										<h1>Children</h1>
									</header>

									{local var="childrenList"}
								</section>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
			<?php } // permissions ?>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(function() {
		// Show first tab on page load
		$(".nav-tabs a:first").tab("show");
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>
