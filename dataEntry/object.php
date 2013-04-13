<?php
include("../header.php");
recurseInsert("acl.php","php");

try {

	if (!isset($engine->cleanGet['MYSQL']['formID'])
		|| is_empty($engine->cleanGet['MYSQL']['formID'])
		|| !validate::integer($engine->cleanGet['MYSQL']['formID'])) {

		if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {
			$object = objects::get($engine->cleanGet['MYSQL']['objectID']);

			if ($object === FALSE) {
				errorHandle::newError(__METHOD__."() - No Form ID Provided, error getting Object", errorHandle::DEBUG);
				throw new Exception("No Form ID Provided, error getting Object.");
			}

			http::setGet('formID',$object['formID']);

		}
		else {
			errorHandle::newError(__METHOD__."() - No Form ID Provided.", errorHandle::DEBUG);
			throw new Exception("No Form ID Provided.");
		}
	}

	if (isset($engine->cleanGet['MYSQL']['objectID'])) {
		if (is_empty($engine->cleanGet['MYSQL']['objectID'])
			|| !validate::integer($engine->cleanGet['MYSQL']['objectID'])) {

			errorHandle::newError("ObjectID Provided is invalid", errorHandle::DEBUG);
			throw new Exception("ObjectID Provided is invalid.");
		}

		// if an object ID is provided make sure the object is from this form
		if (!objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
			errorHandle::newError("Object not from this form.", errorHandle::DEBUG);
			throw new Exception("Object not from this form.");
		}
	}
	else if (!isset($engine->cleanGet['MYSQL']['objectID'])) {
		$engine->cleanGet['MYSQL']['objectID'] = NULL;
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
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			throw new Exception("Error Updating Form.");
		}
	}

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

// build the form for displaying
$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
if ($builtForm === FALSE) {
	throw new Exception("Error building form.");
}
localvars::add("form",$builtForm);
localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));
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
