<?php

include("../header.php");

try {

	if (!isset($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['id']) || !validate::integer($engine->cleanGet['MYSQL']['id'])) {
		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}




	// Get the current project from the database
	$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s' LIMIT 1",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - Error getting project data", errorHandle::DEBUG);
		errorHandle::errorMsg("Error Building Page");
		throw new Exception('Error');
	}

	if ($sqlResult['numrows'] == 0) {
		throw new Exception('Error');
		errorHandle::errorMsg("Project not Found");
	}



	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	// Get the forms that belong to this project
	if (!is_empty($row)) {
		$currentForms = decodeFields($row['forms']);
	}
	else {
		$currentForms = array();
	}

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

		if (in_array($row['ID'],$currentForms['metadata'])) {
			continue;
		}

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


	$objectForms          = array();
	$availableObjectForms = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		$objectForms[] = $row;

		if (in_array($row['ID'],$currentForms['objectForms'])) {
			continue;
		}

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

	}
catch (Exception $e) {
}



localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Project Management : Edit Project</h1>
	</header>

	{local var="results"}

<?php 
if (is_empty($engine->errorStack)) {
	?>

	<ul>
		<li><a href="#addForms">Add Forms</a></li>
		<li><a href="#groupings">Groupings</a></li>
		<li><a href="#permissions">Permissions</a></li>
	</ul>

	<div id="addForms">
		<header>
			<h1>Add Forms</h1>
		</header>

		<a name="addForms"></a>

		<table>

			<tr>
				<th>Metadata Forms</th>
				<th>Object Forms</th>
			<tr>

				<td>

					<select name="selectedMetadataForms" size="5">

					</select>

					<br />

					<select name="availableMetadataForms">
						<option value="null">Select a Form</option>
						{local var="availableMetadataForms"}
					</select>

				</td>

				<td>

					<select name="selectedObjectForms" size="5">

					</select>
					
					<br />

					<select name="availableObjectForms">
						<option value="null">Select a Form</option>
						{local var="availableObjectForms"}
					</select>

				</td>

			</tr>

		</table>

	</div>

	<div id="groupings">
		<header>
			<h1>Manage Groupings</h1>
		</header>

		<a name="groupings"></a>

		<ul>
			<li>New Grouping</li>
			<li>Log Out</li>
			<li>Export Link (needs definable properties)</li> 
			<li>random link</li>
		</ul>

		Forms
		<ul>
		</ul>


	</div>

	<div id="permissions">
		<header>
			<h1>Manage Permissions</h1>
		</header>

		<a name="permissions"></a>

		<select name="selectedUsers" size="5">

		</select>

		<br />

		<select name="availableUsers">
			{local var="availableUsersList"}
		</select>

	</div>

</section>
<?php 
}
?>

<?php
$engine->eTemplate("include","footer");
?>