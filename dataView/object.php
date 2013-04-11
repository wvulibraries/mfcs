<?php
include("../header.php");
recurseInsert("acl.php","php");

try {
	$objectID = isset($engine->cleanGet['MYSQL']['objectID']) ? $engine->cleanGet['MYSQL']['objectID'] : NULL;

	if (isnull($objectID)) {
		throw new Exception("Object ID Not Found.");
	}

	$sql = sprintf("SELECT * FROM `%s` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("objects")),
		$engine->openDB->escape($objectID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['affectedRows'] == 0 || !$sqlResult['result']) {
		errorHandle::newError("Failed to retrieve objectID (".$objectID."): ".$sqlResult['error'], errorHandle::DEBUG);
		throw new Exception("Invalid Object ID.");
	}

	$object = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);
	$object['data'] = decodeFields($object['data']);
}
catch (Exception $e) {
	die($e->getMessage());
}

// Get projects and all joined projects
$allProjects      = projects::getProjects();
$selectedProjects = objects::getProjects($objectID);

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
			$engine->openDB->escape($objectID),
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
				$engine->openDB->escape($objectID),
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
$sql = sprintf("SELECT * FROM `%s` WHERE parentID='%s'",
	$engine->openDB->escape($engine->dbTables("objects")),
	$engine->openDB->escape($objectID)
	);
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		$children[$row['ID']] = $row;
	}
}
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