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

		$forms = encodeFields($forms);

		$sql       = sprintf("UPDATE `projects` SET `forms`='%s' WHERE `ID`='%s'",
			$engine->openDB->escape($forms),
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
		$selectedMetadataForms .= sprintf('<option value="%s">%s</option>',
			$engine->openDB->escape($row['ID']),
			$engine->openDB->escape($row['title'])
			);

	}

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
		$selectedObjectForms .= sprintf('<option value="%s">%s</option>',
			$engine->openDB->escape($row['ID']),
			$engine->openDB->escape($row['title'])
			);

	}

	localvars::add("selectedMetadataForms",$selectedMetadataForms);
	localvars::add("selectedObjectForms",$selectedObjectForms);


	// Get all the metadata forms
	$sql       = sprintf("SELECT * FROM `forms` WHERE `production`='1' AND `metadata`='1' ORDER BY `title`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - Error getting Metadata forms", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Building Page");
		throw new Exception('Error');
	}

	$metadataForms          = array();
	$availableMetadataForms = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		$metadataForms[] = $row;

		$availableMetadataForms .= sprintf('<option value="%s">%s</option>',
			htmlSanitize($row['ID']),
			htmlSanitize($row['title'])
			);
	}

	localvars::add("availableMetadataForms",$availableMetadataForms);

	// Get all the Object forms
	$sql       = sprintf("SELECT * FROM `forms` WHERE `production`='1' AND `metadata`='0' ORDER BY `title`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - Error getting Metadata forms", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Building Page");
		throw new Exception('Error');
	}


	$availableObjectForms = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		$availableObjectForms .= sprintf('<option value="%s">%s</option>',
			htmlSanitize($row['ID']),
			htmlSanitize($row['title'])
			);
	}

	localvars::add("availableObjectForms",$availableObjectForms);


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

<script type="text/javascript" src="{local var="siteRoot"}includes/js/projectEdit.js"></script>

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

			<form action="{phpself query="true"}" method="post">
				{engine name="csrf"}

				<div class="row-fluid" id="addForms">
					<header>
						<h1>Add Forms</h1>
					</header>

					<a name="addForms"></a>

					<table>
						<tr>
							<th>Metadata Forms</th>
							<th>Object Forms</th>
						</tr>
						<tr>
							<td>
								<select name="selectedMetadataForms[]" id="selectedMetadataForms" size="5" multiple="multiple">
									{local var="selectedMetadataForms"}
								</select>
								<br />
								<select name="availableMetadataForms" id="availableMetadataForms" onchange="addItemToID('selectedMetadataForms', this.options[this.selectedIndex])">
									<option value="null">Select a Form</option>
									{local var="availableMetadataForms"}
								</select>
								<br />
								<input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedMetadataForms', this.form.selectedMetadataForms)" />
							</td>
							<td>
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
							</td>
						</tr>
					</table>
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
									<ul class="unstyled draggable">
										<li><a href="#" class="btn btn-block">New Grouping</a></li>
										<li><a href="#" class="btn btn-block">Log Out</a></li>
										<li><a href="#" class="btn btn-block">Export Link (needs definable properties)</a></li>
										<li><a href="#" class="btn btn-block">Random Link</a></li>
									</ul>

									Forms
									<ul class="unstyled draggable" id="groupingsFormsAdd"></ul>
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
						</div>
						<div class="span6">
							<ul class="sortable unstyled" id="GroupingsPreview"></ul>
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
				<input type="submit" class="btn btn-large btn-block btn-primary" name="submitProjectEdits" value="Update Project" onclick="entrySubmit()" />
			</form>
			<?php
		}
		?>
	</div>


</section>

<?php
$engine->eTemplate("include","footer");
?>