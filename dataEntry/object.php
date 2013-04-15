<?php
include("../header.php");
recurseInsert("acl.php","php");

$selectedProjects = NULL;

try {

	$error = FALSE; 

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	// if an object ID is provided make sure the object is from this form
	if (!isnull($engine->cleanGet['MYSQL']['objectID']) && !objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form.");
	}

	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID'])) {
		throw new Exception("Metadata form provided (Object forms only).");
	}

	// check for edit permissions on the project
	// if (projects::checkPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) {
	// 	throw new Exception("Permissions denied for working on this project");
	// }

	// check that this form is part of the project
	// // TODO need forms from User
	// if (!forms::checkFormInProject($engine->cleanGet['MYSQL']['id'],$engine->cleanGet['MYSQL']['formID'])) {
	// 	throw new Exception("Form is not part of project.");
	// }

	// Get the project
	// $project = NULL; // TODO: Needs to be gotten from the user info
	// if ($project === FALSE) {
	// 	throw new Exception("Error retrieving project.");
	// }


	localvars::add("formName",$form['title']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
		$engine->cleanGet['MYSQL']['objectID'] = localvars::get("newObjectID");
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['projectForm'])) {

		$result = $engine->openDB->transBegin("objectProjects");
		if ($result !== TRUE) {
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
		$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'],$error);
		if ($builtForm === FALSE) {
			throw new Exception("Error building form.");
		}
		localvars::add("form",$builtForm);
		localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));

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

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Edit Object</h1>
	</header>

	<div class="container-fluid">
		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<div class="row-fluid">
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<li><a data-toggle="tab" href="#project">Project</a></li>
					<li><a data-toggle="tab" href="#children">Children</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}
					</div>

					<div class="tab-pane" id="project">
						<h2>Change Project Membership</h2>

						<form action="{phpself query="true"}" method="post">
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
						</form>
					</div>

					<div class="tab-pane" id="children">
						Children content
						<?php
						print "<pre>";
						// print_r($children);
						print "</pre>";
						?>
					</div>
				</div>
			</div>
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
