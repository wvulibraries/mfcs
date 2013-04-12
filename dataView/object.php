<?php
include("../header.php");
recurseInsert("acl.php","php");

try {
	
	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& (is_empty($engine->cleanGet['MYSQL']['objectID'])
			|| !validate::integer($engine->cleanGet['MYSQL']['objectID']))
		) {

		errorHandle::newError(__METHOD__."() - ObjectID Provided is invalid", errorHandle::DEBUG);
		throw new Exception("ObjectID Provided is invalid.");
	}

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
			errorHandle::errorMsg("No Form ID Provided.");
			throw new Exception('Error');
		}
	}

	$object = objects::get($engine->cleanGet['MYSQL']['objectID']);

	if ($sqlResult['affectedRows'] == 0 || !$sqlResult['result']) {
		throw new Exception("Invalid Object ID.");
	}

}
catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

// Get projects and all joined projects
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
			errorHandle::errorMsg("Failed to remove projects.");
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
			errorHandle::errorMsg("Failed to add projects.");
		}
	}

	$selectedProjects = $engine->cleanPost['MYSQL']['projects'];
}
// Submissions

// Projects
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
// Projects

// Children
$children = objects::getChildren($engine->cleanGet['MYSQL']['objectID']);
// Children


$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Object Display</h1>
	</header>

	<div class="container-fluid">
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
					Metadata content
					<?php
					print "<pre>";
					print_r($object['data']);
					print "</pre>";
					?>
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
</section>

<?php
$engine->eTemplate("include","footer");
?>