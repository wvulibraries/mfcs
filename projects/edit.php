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
            htmlSanitize($form['ID']),
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
			htmlSanitize($row['username'])
			);
	}
	localvars::add("availableUsersList",$availableUsersList);

    $selectedEntryUsers  = "";
	$selectedViewUsers   = "";
	$selectedUsersAdmins = "";

	$sql       = sprintf("SELECT permissions.type as type, users.status as status, users.firstname as firstname, users.lastname as lastname, users.ID as userID FROM permissions LEFT JOIN users ON permissions.userID=users.ID WHERE permissions.projectID='%s'",
		$engine->cleanGet['MYSQL']['id']
		);
	$sqlResult = $engine->openDB->query($sql);
	if(!$sqlResult['result']) throw new Exception("MySQL Error - getting permissions ({$sqlResult['error']})");

	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
        $optionHTML = sprintf('<option value="%s">%s, %s (%s)</option>',
            $engine->openDB->escape($row['userID']),
            $engine->openDB->escape($row['lastname']),
            $engine->openDB->escape($row['firstname']),
            $engine->openDB->escape($row['status']));
        switch($row['type']){
            case mfcs::AUTH_VIEW:
                $selectedViewUsers .= $optionHTML;
                break;
            case mfcs::AUTH_ENTRY:
                $selectedEntryUsers .= $optionHTML;
                break;
            case mfcs::AUTH_ADMIN:
                $selectedUsersAdmins .= $optionHTML;
                break;
        }
	}

	localvars::add("selectedEntryUsers",$selectedEntryUsers);
	localvars::add("selectedViewUsers",$selectedViewUsers);
	localvars::add("selectedUsersAdmins",$selectedUsersAdmins);

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

		<?php if(is_empty($engine->errorStack['error'])){ ?>


        <div class="alert alert-block" style="display: none;" id="updateProjectAlert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Update Project!</h4>
            Update project to reload UI
        </div>
        <form name="projectEdits" action='{phpself query="true"}' method="post">
            {engine name="csrf"}

            <ul class="nav nav-tabs">
                <!-- <li class="active"><a href="#home" data-toggle="tab">Edit Project</a></li> -->
                <li class="active"><a href="#forms" data-toggle="tab">Forms</a></li>
                <li><a href="#groupings" data-toggle="tab">Groupings</a></li>
                <li><a href="#permissions" data-toggle="tab">Permissions</a></li>
            </ul>
            <div class="tab-content">
                <!-- <div class="tab-pane active" id="home">...</div> -->
                <div class="tab-pane active" id="forms">
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos dolor ea illum nesciunt temporibus? Blanditiis consequatur distinctio, ex harum modi nostrum quaerat, quas quod sequi similique ut velit veritatis voluptates?</p>
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
                </div>
                <div class="tab-pane" id="groupings">
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

                                    {local var="metadataForms"}
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
                <div class="tab-pane" id="permissions">
                    <table>
                        <tr>
                            <th>Data Entry Users</th>
                            <th>Data View Users</th>
                            <th>Administrators</th>
                        </tr>
                        <tr>
                            <td>
                                <select name="selectedEntryUsers[]" id="selectedEntryUsers" size="5" multiple="multiple">
                                    {local var="selectedEntryUsers"}
                                </select>
                                <br />
                                <select name="availableEntryUsers" id="availableEntryUsers" onchange="addItemToID('selectedEntryUsers', this.options[this.selectedIndex])">
                                    {local var="availableUsersList"}
                                </select>
                                <br />
                                <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedEntryUsers', this.form.selectedEntryUsers)" />
                            </td>
                            <td>
                                <select name="selectedViewUsers[]" id="selectedViewUsers" size="5" multiple="multiple">
                                    {local var="selectedViewUsers"}
                                </select>
                                <br />
                                <select name="availableViewUsers" id="availableViewUsers" onchange="addItemToID('selectedViewUsers', this.options[this.selectedIndex])">
                                    {local var="availableUsersList"}
                                </select>
                                <br />
                                <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedViewUsers', this.form.selectedViewUsers)" />
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
                                <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedUsersAdmins', this.form.selectedUsersAdmins)" />
                            </td>
                        <tr>
                    </table>
                </div>
            </div>

        <input type="submit" class="btn btn-large btn-block btn-primary" name="submitProjectEdits" value="Update Project" />
        </form>
        <?php } ?>
	</div>


</section>

<?php
$engine->eTemplate("include","footer");
?>