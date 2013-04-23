<?php

include("../header.php");

try {
	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		throw new Exception('No Project ID Provided.');
	}


	// Submission
	if (isset($engine->cleanPost['MYSQL']['submitProjectEdits'])) {
		try{
			// trans: begin transaction
			$engine->openDB->transBegin();

			// update permissions
			$sql = sprintf("DELETE FROM `permissions` WHERE `projectID`='%s'",
				$engine->cleanGet['MYSQL']['id']
			);
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("MySQL Error - Wipe Permissions ({$sqlResult['error']} -- $sql)");
			$permissionValueGroups = array();
			if (isset($engine->cleanPost['MYSQL']['selectedViewUsers'])) {
				foreach($engine->cleanPost['MYSQL']['selectedViewUsers'] as $key => $value) {
					$permissionValueGroups[] = sprintf("('%s','%s','%s')",
						$engine->openDB->escape($value),
						$engine->cleanGet['MYSQL']['id'],
						mfcs::AUTH_VIEW
					);
				}
			}
			if (isset($engine->cleanPost['MYSQL']['selectedEntryUsers'])) {
				foreach($engine->cleanPost['MYSQL']['selectedEntryUsers'] as $key => $value) {
					$permissionValueGroups[] = sprintf("('%s','%s','%s')",
						$engine->openDB->escape($value),
						$engine->cleanGet['MYSQL']['id'],
						mfcs::AUTH_ENTRY
					);
				}
			}
			if (isset($engine->cleanPost['MYSQL']['selectedUsersAdmins'])) {
				foreach($engine->cleanPost['MYSQL']['selectedUsersAdmins'] as $key => $value) {
					$permissionValueGroups[] = sprintf("('%s','%s','%s')",
						$engine->openDB->escape($value),
						$engine->cleanGet['MYSQL']['id'],
						mfcs::AUTH_ADMIN
					);
				}
			}

			if(sizeof($permissionValueGroups)){
				$sql = sprintf("INSERT INTO `permissions` (userID,projectID,type) VALUES%s",
					implode(',', $permissionValueGroups)
				);
				$sqlResult = $engine->openDB->query($sql);
				if(!$sqlResult['result']) throw new Exception("MySQL Error - Insert Permissions ({$sqlResult['error']} -- $sql)");
			}

			// generate forms serialized arrays
			$forms             = array();
			$forms['metadata'] = array();
			$forms['objects']  = array();
			if (isset($engine->cleanPost['MYSQL']['selectedMetadataForms'])) {
				foreach($engine->cleanPost['MYSQL']['selectedMetadataForms'] as $I=>$V) {
					$forms['metadata'][] = $V;
				}
			}

			if (isset($engine->cleanPost['MYSQL']['selectedObjectForms'])) {
				foreach($engine->cleanPost['MYSQL']['selectedObjectForms'] as $I=>$V) {
					$forms['objects'][] = $V;
				}
			}

			$groupings = json_decode($engine->cleanPost['RAW']['groupings'], TRUE);

			if (!is_empty($groupings)) {
				foreach ($groupings as $I => $grouping) {
					$positions[$I] = $grouping['position'];
				}

				array_multisort($positions, SORT_ASC, $groupings);
			}

			$forms     = encodeFields($forms);
			$groupings = encodeFields($groupings);

			$sql       = sprintf("UPDATE `projects` SET `forms`='%s', `groupings`='%s' WHERE `ID`='%s'",
				$engine->openDB->escape($forms),
				$engine->openDB->escape($groupings),
				$engine->cleanGet['MYSQL']['id']
			);
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("MySQL Error - Inserting Forms ({$sqlResult['error']} -- $sql)");

			// If we get here then the project successfully updated!
			$engine->openDB->transCommit();
			$engine->openDB->transEnd();
			errorHandle::successMsg("Successfully updated Project.");

		}catch(Exception $e){
			errorHandle::newError("{$e->getFile()}:{$e->getLine()} {$e->getMessage()}", errorHandle::DEBUG);
			errorHandle::errorMsg("Error Updating Project");
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
		}

	}

	// Get the current project from the database
	$project = projects::get($engine->cleanGet['MYSQL']['id']);
	if ($project === FALSE) {
		errorHandle::errorMsg("Error retrieving project.");
		throw new Exception('Error');
	}

	localvars::add("numbering",$project['numbering']);

	// Get the forms that belong to this project
	if (!is_empty($project['forms'])) {
		$currentForms = $project['forms'];
	}
	else {
		$currentForms = array();
	}

	$metadataForms         = array();
	$objectForms           = array();
	$objectFormsEven       = NULL;
	$objectFormsOdd        = NULL;
	$metadataFormsEven     = NULL;
	$metadataFormsOdd      = NULL;
	$selectedMetadataForms = "";
	$selectedObjectForms   = "";

	// If there's forms, then start looping through them grabbing their metadataForms
	if(sizeof($currentForms['objects'])){
		foreach($currentForms['objects'] as $i => $formID){
			$metadataForms = array_merge($metadataForms, forms::getObjectFormMetaForms($formID));
		}
	}

	// Now loop through all the metadata forms building their HTML and putting it in the right place
	foreach ($metadataForms as $i => $form) {
		$targetVar = ($i % 2) ? 'metadataFormsOdd' : 'metadataFormsEven';
		$$targetVar .= sprintf('<li data-type="metadataForm" data-formid="%s"><a href="#" class="btn btn-block">%s</a></li>',
			htmlSanitize($form['formID']),
			htmlSanitize($form['title'])
		);
	}

	localvars::add("selectedMetadataForms",$selectedMetadataForms);
	if(!empty($metadataFormsEven) and !empty($metadataFormsOdd)){
		localvars::add("metadataForms", sprintf('
        <h3>Metadata Forms</h3>
        <div class="row-fluid">
            <ul class="unstyled draggable span6">%s</ul>
            <ul class="unstyled draggable span6">%s</ul>
        </div>
	', $metadataFormsEven, $metadataFormsOdd));
	}else{
		localvars::add("metadataForms",  '');
	}

	// Object Forms
	if(isset($currentForms['objects'])){
		foreach ($currentForms['objects'] as $i => $objectID) {
			$sql = sprintf("SELECT ID, title FROM `forms` WHERE ID='%s'",
				$engine->openDB->escape($objectID)
			);
			$sqlResult = $engine->openDB->query($sql);
			if(!$sqlResult['result']) throw new Exception("MySQL error - getting form titles ({$sqlResult['error']})");

			$row  = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
			$targetVar = ($i % 2) ? 'objectFormsOdd' : 'objectFormsEven';
			$$targetVar .= sprintf('<li data-type="objectForm" data-formID="%s"><a href="#" class="btn btn-block">%s</a></li>',
				htmlSanitize($row['ID']),
				htmlSanitize($row['title'])
			);
			$selectedObjectForms .= sprintf('<option value="%s">%s</option>',
				$engine->openDB->escape($row['ID']),
				$engine->openDB->escape($row['title'])
			);
		}
	}


	localVars::add("objectFormsEven",$objectFormsEven);
	localVars::add("objectFormsOdd",$objectFormsOdd);
	localvars::add("selectedObjectForms",$selectedObjectForms);


	// Get all the Object forms
	$sql       = sprintf("SELECT * FROM `forms` WHERE `production`='1' ORDER BY `title`");
	$sqlResult = $engine->openDB->query($sql);
	if(!$sqlResult['result']) throw new Exception("MySQL Error - Error getting forms ({$sqlResult['error']})");

	$availableMetadataForms = "";
	$availableObjectForms   = "";
	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		if ($row['metadata'] == "1") {
			$availableMetadataForms .= sprintf('<option value="%s">%s</option>',
				htmlSanitize($row['ID']),
				htmlSanitize($row['title'])
			);
		}
		else if ($row['metadata'] == "0") {
			$availableObjectForms .= sprintf('<option value="%s">%s</option>',
				htmlSanitize($row['ID']),
				htmlSanitize($row['title'])
			);
		}
	}
	localvars::add("availableMetadataForms",$availableMetadataForms);
	localvars::add("availableObjectForms",$availableObjectForms);


	// Get existing groupings
	$sql = sprintf("SELECT * FROM `projects` WHERE `ID`='%s' LIMIT 1",
		$engine->cleanGet['MYSQL']['id']
	);
	$sqlResult = $engine->openDB->query($sql);
	if(!$sqlResult['result']) throw new Exception("MySQL Error - Error getting project ({$sqlResult['error']})");

	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
}
catch (Exception $e) {
	errorHandle::newError("{$e->getFile()}:{$e->getLine()} {$e->getMessage()}", errorHandle::DEBUG);
	errorHandle::errorMsg("Error Building Page");
}



localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

	<script type="text/javascript" src='{local var="siteRoot"}includes/js/projectEdit.js'></script>

	<section>
		<header class="page-header">
			<h1>Project Management : Edit Project</h1>
		</header>

		<div class="container-fluid">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<?php if(!isset($engine->errorStack['error']) || (isset($engine->errorStack['error']) && is_empty($engine->errorStack['error']))){ ?>


				<div class="alert alert-block" style="display: none;" id="updateProjectAlert">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<h4>Update Project!</h4>
					Update project to reload UI
				</div>
				<form name="projectEdits" action='{phpself query="true"}' method="post">
					{engine name="csrf"}
					<p>Here you can select the forms that are a part of this project.</p>
					<select name="selectedObjectForms[]" id="selectedObjectForms" size="5" multiple="multiple">
						{local var="selectedObjectForms"}
					</select>
					<br />
					<select name="availableObjectForms" id="availableObjectForms" onchange="addItemToID('selectedObjectForms', this.options[this.selectedIndex]);$('#updateProjectAlert').show();">
						<option value="null">Select a Form</option>
						{local var="availableObjectForms"}
					</select>
					<br />
					<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedObjectForms', this.form.selectedObjectForms);$('#updateProjectAlert').show();" />
					<hr>
					<input type="submit" class="btn btn-large btn-block btn-primary" name="submitProjectEdits" value="Update Project" />
				</form>
			<?php } ?>
		</div>


	</section>

<?php
$engine->eTemplate("include","footer");
?>