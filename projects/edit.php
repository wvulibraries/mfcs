<?php

include("../header.php");

try {

	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}


	// Submission
	if (isset($engine->cleanPost['MYSQL']['submitProjectEdits'])) {
		// trans: begin transaction
		$engine->openDB->transBegin();

		// update permissions
		$sql       = sprintf("DELETE FROM `permissions` WHERE `projectID`='%s'",
			$engine->cleanGet['MYSQL']['id']
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - deleting previous permissions: ".$sqlResult['error']." -- ".$sql, errorHandle::DEBUG);
			errorHandle::errorMsg("Error Updating Project");
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception('Error');
		}


		if (isset($engine->cleanPost['MYSQL']['selectedUsers'])) {
			foreach($engine->cleanPost['MYSQL']['selectedUsers'] as $I=>$V) {
				$sql       = sprintf("INSERT INTO `permissions` (userID,projectID,type) VALUES('%s','%s','0')",
					$engine->openDB->escape($V),
					$engine->cleanGet['MYSQL']['id']
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
					errorHandle::errorMsg("Error Updating Project");
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();
					throw new Exception('Error');
				}
			}
		}

		if (isset($engine->cleanPost['MYSQL']['selectedUsersAdmins'])) {
			foreach($engine->cleanPost['MYSQL']['selectedUsersAdmins'] as $I=>$V) {
				$sql       = sprintf("INSERT INTO `permissions` (userID,projectID,type) VALUES('%s','%s','1')",
					$engine->openDB->escape($V),
					$engine->cleanGet['MYSQL']['id']
					);
				$sqlResult = $engine->openDB->query($sql);

				if (!$sqlResult['result']) {
					errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
					errorHandle::errorMsg("Error Updating Project");
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();
					throw new Exception('Error');
				}
			}
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

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - Inserting Forms", errorHandle::DEBUG);
			errorHandle::errorMsg("Error Updating Project");
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception('Error');
		}

		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
		errorHandle::successMsg("Successfully updated Project.");

	}


	// Get the current project from the database
	$project = getProject($engine->cleanGet['MYSQL']['id']);
	if ($project === FALSE) {
		errorHandle::errorMsg("Error retrieving project.");
		throw new Exception('Error');
	}

	localvars::add("numbering",$project['numbering']);

	// Get the forms that belong to this project
	if (!is_empty($project['forms'])) {
		$currentForms = decodeFields($project['forms']);
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

	// Metadata Forms
	foreach ($currentForms['metadata'] as $I => $V) {
		$sql       = sprintf("SELECT ID, title FROM `forms` WHERE ID='%s'",
			$engine->openDB->escape($V)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting form titles (metadata)", errorHandle::DEBUG);
			errorHandle::errorMsg("Error Building Page");
			throw new Exception('Error');
		}

		$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		$metadataForms[] = array(
			"ID"    => $row['ID'],
			"title" => $row['title'],
			);
		$selectedMetadataForms .= sprintf('<option value="%s">%s</option>',
			$engine->openDB->escape($row['ID']),
			$engine->openDB->escape($row['title'])
			);
	}

	foreach ($metadataForms as $I => $form) {
		$tmp = sprintf('<li data-type="metadataForm" data-formid="%s"><a href="#" class="btn btn-block">%s</a></li>',
			htmlSanitize($form['ID']),
			htmlSanitize($form['title'])
			);

		if ($I % 2 == 0) { // even
			$metadataFormsEven .= $tmp;
		}
		else { // odd
			$metadataFormsOdd .= $tmp;
		}
	}

	localVars::add("metadataFormsEven",$metadataFormsEven);
	localVars::add("metadataFormsOdd",$metadataFormsOdd);
	localvars::add("selectedMetadataForms",$selectedMetadataForms);


	// Object Forms
	foreach ($currentForms['objects'] as $I => $V) {
		$sql       = sprintf("SELECT ID, title FROM `forms` WHERE ID='%s'",
			$engine->openDB->escape($V)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting form titles (object)", errorHandle::DEBUG);
			errorHandle::errorMsg("Error Building Page");
			throw new Exception('Error');
		}

		$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		$objectForms[] = array(
			"ID"    => $row['ID'],
			"title" => $row['title'],
			);
		$selectedObjectForms .= sprintf('<option value="%s">%s</option>',
			$engine->openDB->escape($row['ID']),
			$engine->openDB->escape($row['title'])
			);
	}

	foreach ($objectForms as $I => $form) {
		$tmp = sprintf('<li data-type="objectForm" data-formID="%s"><a href="#" class="btn btn-block">%s</a></li>',
			htmlSanitize($form['ID']),
			htmlSanitize($form['title'])
			);

		if ($I % 2 == 0) { // even
			$objectFormsEven .= $tmp;
		}
		else { // odd
			$objectFormsOdd .= $tmp;
		}
	}

	localVars::add("objectFormsEven",$objectFormsEven);
	localVars::add("objectFormsOdd",$objectFormsOdd);
	localvars::add("selectedObjectForms",$selectedObjectForms);


	// Get all the Object forms
	$sql       = sprintf("SELECT * FROM `forms` WHERE `production`='1' ORDER BY `title`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - Error getting forms", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Building Page");
		throw new Exception('Error');
	}

	$availableMetadataForms = "";
	$availableObjectForms   = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
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

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - Error getting project", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Building Page");
		throw new Exception('Error');
	}

	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	if (!is_empty($row['groupings'])) {
		$tmp       = decodeFields($row['groupings']);
		$groupings = array();
		$preview   = NULL;

		// Get all groupings needed
		foreach ($tmp as $I => $V) {
			if (!is_empty($V['grouping'])) {
				$groupings[$V['grouping']] = array(
					"type"     => "grouping",
					"grouping" => $V['grouping'],
					);
			}
		}

		$positionOffset = 0;
		foreach ($tmp as $I => $V) {
			$values = json_encode($V);

			if (!is_empty($V['grouping']) && isset($groupings[$V['grouping']])) {
				$preview .= sprintf('
					<li id="GroupingsPreview_%s">
						<div class="groupingPreview">
							<script type="text/javascript">
								$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s"));
							</script>
						</div>
						<div class="groupingValues">
							<script type="text/javascript">
								$("#GroupingsPreview_%s .groupingValues").html(newGroupingValues("%s","%s",%s));
							</script>
						</div>
					</li>',
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($groupings[$V['grouping']]['type']),
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($V['position'] + $positionOffset),
					htmlSanitize($groupings[$V['grouping']]['type']),
					json_encode($groupings[$V['grouping']])
					);

				$positionOffset++;
				unset($groupings[$V['grouping']]);
			}

			$preview .= sprintf('
				<li id="GroupingsPreview_%s">
					<div class="groupingPreview">
						<script type="text/javascript">
							$("#GroupingsPreview_%s .groupingPreview").html(newGroupingPreview("%s"));
						</script>
					</div>
					<div class="groupingValues">
						<script type="text/javascript">
							$("#GroupingsPreview_%s .groupingValues").html(newGroupingValues("%s","%s",%s));
						</script>
					</div>
				</li>',
				htmlSanitize($V['position'] + $positionOffset),
				htmlSanitize($V['position'] + $positionOffset),
				htmlSanitize($V['type']),
				htmlSanitize($V['position'] + $positionOffset),
				htmlSanitize($V['position'] + $positionOffset),
				htmlSanitize($V['type']),
				$values
				);
		}
		localvars::add("existingGroupings",$preview);
	}

	// Get all users
	$sql       = sprintf("SELECT * FROM `users`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - retrieving users.", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving users.");
		throw new Exception('Error');
	}

	$availableUsersList = '<option value="null">Select a User</option>';
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		$availableUsersList .= sprintf('<option value="%s">%s, %s (%s)</option>',
			htmlSanitize($row['ID']),
			htmlSanitize($row['lastname']),
			htmlSanitize($row['firstname']),
			htmlSanitize($row['status'])
			);
	}
	localvars::add("availableUsersList",$availableUsersList);

	$selectedUsers       = "";
	$selectedUsersAdmins = "";

	$sql       = sprintf("SELECT permissions.type as type, users.status as status, users.firstname as firstname, users.lastname as lastname, users.ID as userID FROM permissions LEFT JOIN users ON permissions.userID=users.ID WHERE permissions.projectID='%s'",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - getting permissions", errorHandle::DEBUG);
		errorHandle::errorMsg("Error retrieving users.");
		throw new Exception('Error');
	}

	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		if ($row['type'] == "0") {
			$selectedUsers .= sprintf('<option value="%s">%s, %s (%s)</option>',
				$engine->openDB->escape($row['userID']),
				$engine->openDB->escape($row['lastname']),
				$engine->openDB->escape($row['firstname']),
				$engine->openDB->escape($row['status'])
				);
		}
		if ($row['type'] == "1") {
			$selectedUsersAdmins .= sprintf('<option value="%s">%s, %s (%s)</option>',
				$engine->openDB->escape($row['userID']),
				$engine->openDB->escape($row['lastname']),
				$engine->openDB->escape($row['firstname']),
				$engine->openDB->escape($row['status'])
				);
		}
	}

	localvars::add("selectedUsers",$selectedUsers);
	localvars::add("selectedUsersAdmins",$selectedUsersAdmins);

}
catch (Exception $e) {
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

		<?php
		if (is_empty($engine->errorStack)) {
			?>
			<div class="row-fluid" id="pageNav">
				<ul>
					<li><a href="#addForms">Add Forms</a></li>
					<li><a href="#groupings">Groupings</a></li>
					<li><a href="#permissions">Permissions</a></li>
					<li><a href="#numbering">Project Numbering</a></li>
				</ul>
			</div>

			<form name="projectEdits" action="{phpself query="true"}" method="post">
				{engine name="csrf"}

				<div class="row-fluid" id="addForms">
					<header>
						<h1>Add Forms</h1>
					</header>

					<a name="addForms"></a>

                    <select name="selectedObjectForms[]" id="selectedObjectForms" size="5" multiple="multiple">
                        {local var="selectedObjectForms"}
                    </select>
                    <br />
                    <select name="availableObjectForms" id="availableObjectForms" onchange="addItemToID('selectedObjectForms', this.options[this.selectedIndex])">
                        <option value="null">Select a Form</option>
                        {local var="availableObjectForms"}
                    </select>
                    <br />
                    <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedObjectForms', this.form.selectedObjectForms)" />

                </div>

				<div class="row-fluid" id="groupings">
					<header>
						<h1>Manage Groupings</h1>
					</header>
					<a name="groupings"></a>

					<div class="row-fluid">
						<div class="span6">
							<ul class="nav nav-tabs" id="groupingTab">
								<li><a href="#groupingsAdd" data-toggle="tab">Add</a></li>
								<li><a href="#groupingsSettings" data-toggle="tab">Settings</a></li>
							</ul>

							<div class="tab-content">
								<div class="tab-pane" id="groupingsAdd">
									<ul class="unstyled draggable span6">
										<li><a href="#" class="btn btn-block">New Grouping</a></li>
										<li><a href="#" class="btn btn-block">Log Out</a></li>
									</ul>
									<ul class="unstyled draggable span6">
										<li><a href="#" class="btn btn-block">Export Link (needs definable properties)</a></li>
										<li><a href="#" class="btn btn-block">Link</a></li>
									</ul>

									<h3>Object Forms</h3>
									<div class="row-fluid">
										<ul class="unstyled draggable span6">{local var="objectFormsEven"}</ul>
										<ul class="unstyled draggable span6">{local var="objectFormsOdd"}</ul>
									</div>

									<h3>Metadata Forms</h3>
									<div class="row-fluid">
										<ul class="unstyled draggable span6">{local var="metadataFormsEven"}</ul>
										<ul class="unstyled draggable span6">{local var="metadataFormsOdd"}</ul>
									</div>
								</div>

								<div class="tab-pane" id="groupingsSettings">
									<div class="alert alert-block" id="noGroupingSelected">
										<h4>No Grouping Selected</h4>
										To change a grouping, click on it in the preview to the right.
									</div>

									<div class="control-group well well-small" id="groupingsSettings_container_grouping">
										<label for="groupingsSettings_grouping">
											Grouping Label
										</label>
										<input type="text" class="input-block-level" id="groupingsSettings_grouping" name="groupingsSettings_grouping" />
										<span class="help-block hidden"></span>
									</div>

									<div class="control-group well well-small" id="groupingsSettings_container_label">
										<label for="groupingsSettings_label">
											Label
										</label>
										<input type="text" class="input-block-level" id="groupingsSettings_label" name="groupingsSettings_label" />
										<span class="help-block hidden"></span>
									</div>

									<div class="control-group well well-small" id="groupingsSettings_container_url">
										<label for="groupingsSettings_url">
											Address
										</label>
										<input type="text" class="input-block-level" id="groupingsSettings_url" name="groupingsSettings_url" />
										<span class="help-block hidden"></span>
									</div>
								</div>
							</div>
							<input type="hidden" name="groupings">
						</div>

						<div class="span6">
							<ul class="sortable unstyled" id="GroupingsPreview">
								{local var="existingGroupings"}
							</ul>
						</div>
					</div>
				</div>

				<div class="row-fluid" id="permissions">
					<header>
						<h1>Manage Permissions</h1>
					</header>

					<a name="permissions"></a>

					<table>
						<tr>
							<th>Data Entry Users</th>
							<th>Administrators</th>
						</tr>
						<tr>
							<td>
								<select name="selectedUsers[]" id="selectedUsers" size="5" multiple="multiple">
									{local var="selectedUsers"}
								</select>
								<br />
								<select name="availableUsers" id="availableUsers" onchange="addItemToID('selectedUsers', this.options[this.selectedIndex])">
									{local var="availableUsersList"}
								</select>
								<br />
								<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedUsers', this.form.selectedUsers)" />
							</td>
							<td>
								<select name="selectedUsersAdmins[]" id="selectedUsersAdmins" size="5" multiple="multiple">
									{local var="selectedUsersAdmins"}
								</select>
								<br />
								<select name="availableUsersAdmins" id="availableUsersAdmins" onchange="addItemToID('selectedUsersAdmins', this.options[this.selectedIndex])">
									{local var="availableUsersList"}
								</select>
								<br />
								<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedUsersAdmins', this.form.selectedUsers)" />
							</td>
						<tr>
					</table>
				</div>

				<br />
				<input type="submit" class="btn btn-large btn-block btn-primary" name="submitProjectEdits" value="Update Project" />
			</form>
			<?php
		}
		?>
	</div>


</section>

<?php
$engine->eTemplate("include","footer");
?>