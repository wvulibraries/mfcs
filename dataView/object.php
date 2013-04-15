<?php
include("../header.php");
recurseInsert("acl.php","php");

try {

	// Object ID Validation
	if (objects::validID(TRUE) === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (!objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		errorHandle::newError("Object not from this form.", errorHandle::DEBUG);
		throw new Exception("Object not from this form");
	}

	//////////
	// Metadata Tab Stuff
	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	localvars::add("formName",$form['title']);

	// build the form for displaying
	$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building form.");
	}

	localvars::add("form",$builtForm);
	// Metadata Tab Stuff
	//////////

	//////////
	// Project Tab Stuff
	$allProjects      = projects::getProjects();
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);

	// Submissions
	if (isset($engine->cleanPost['MYSQL']['projectForm'])) {
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
	// Submissions

	$tmp = NULL;
	foreach ($allProjects as $project) {
		$tmp .= sprintf('<label class="checkbox" for="%s"><input type="checkbox" id="%s" name="projects[%s]"%s> %s</label>',
			htmlSanitize("project_".$project['ID']),                           // for=
			htmlSanitize("project_".$project['ID']),                           // id=
			htmlSanitize($project['ID']),                                      // name=projects[]
			(in_array($project['ID'], $selectedProjects)) ? " checked" : NULL, // checked or not
			htmlSanitize($project['projectName'])                              // label text
			);
	}
	localVars::add("projectOptions",$tmp);
	unset($tmp);
	// Project Tab Stuff
	//////////

	//////////
	// Children Tab Stuff
	$children = objects::getChildren($engine->cleanGet['MYSQL']['objectID']);
	// Children Tab Stuff
	//////////

}
catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));
localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>View Object</h1>
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
						print_r($children);
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

		// Disable form input fields
		$(":input").prop("disabled",true);

		// Remove form submits
		$(":input[type=submit]").remove();

		// Remove form actions
		$("form").removeAttr("action");
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>