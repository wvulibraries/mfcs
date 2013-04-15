<?php
include("../header.php");
recurseInsert("acl.php","php");

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
		if (!isset($engine->cleanPost['MYSQL']['projects'])) {
			$engine->cleanPost['MYSQL']['projects'] = array();
		}
		else {
			$engine->cleanPost['MYSQL']['projects'] = array_keys($engine->cleanPost['MYSQL']['projects']);
		}

		foreach ($engine->cleanPost['MYSQL']['projects'] as $postProjectID) {
			if (in_array($postProjectID, $selectedProjects)) {
				continue;
			}

			// Add to additions
			$addedProjects[] = $postProjectID;
		}

		foreach ($selectedProjects as $selectProjectID) {
			if (in_array($selectProjectID, $engine->cleanPost['MYSQL']['projects'])) {
				continue;
			}

			// Add to deletions
			$deletedProjects[] = $selectProjectID;
		}

		// Perform deletions
		if (isset($deletedProjects) && !is_empty($deletedProjects)) {
			$sql = sprintf("DELETE FROM `%s` WHERE `objectID`='%s' AND `projectID` IN ('%s')",
				$engine->openDB->escape($engine->dbTables("objectProjects")),
				$engine->openDB->escape($engine->cleanGet['MYSQL']['objectID']),
				implode("','", $deletedProjects)
				);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError("Failed to perform deletions - ".$sqlResult['error'],errorHandle::DEBUG);
				throw new Exception("Failed to remove projects.");
			}
		}

		// Perform additions
		if (isset($addedProjects) && !is_empty($addedProjects)) {
			foreach ($addedProjects as $projectID) {
				$additions[] = sprintf("('%s','%s')",
					$engine->openDB->escape($engine->cleanGet['MYSQL']['objectID']),
					$engine->openDB->escape($projectID)
					);
			}
			$sql = sprintf("INSERT INTO `%s` (`objectID`,`projectID`) VALUES %s",
				$engine->openDB->escape($engine->dbTables("objectProjects")),
				implode(",", $additions)
				);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError("Failed to perform additions - ".$sqlResult['error'],errorHandle::DEBUG);
				throw new Exception("Failed to add projects.");
			}
		}

		$selectedProjects = $engine->cleanPost['MYSQL']['projects'];
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
