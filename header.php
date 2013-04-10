<?php
require("engineInclude.php");

// recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/mfcs.php";
mfcs::singleton();

// Process 'my current project' from the session
$currentProjects = sessionGet('currentProject');
if(is_null($currentProjects)){
    // Load the user's projects from the db
    $currentProjects = array();
    $sql = sprintf("SELECT ID,projectName FROM `projects` LEFT JOIN users_projects ON users_projects.projectID=projects.ID WHERE users_projects.userID=%s",
        mfcs::$engine->openDB->escape(mfcs::user('ID')));
    $sqlResult = $engine->openDB->query($sql);
    if(!$sqlResult['result']){
        errorHandle::newError("Failed to load user's projects ({$sqlResult['error']})", errorHandle::HIGH);
        errorHandle::errorMsg("Failed to load your current projects.");
    }else{
        while($row = mysql_fetch_assoc($sqlResult['result'])){
            $currentProjects[ $row['ID'] ] = $row['projectName'];
        }
    }
}

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",$engineVars['WEBROOT']."/mfcs/");
localVars::add('pageTitle',"Metadata Form Creation System");
localVars::add('pageHeader',"Metadata Form Creation System");
?>

<!-- Modal - Select Current Projects -->
<div id="selectProjects" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Your current projects:</h3>
    </div>
    <div class="modal-body">
        <ul id="selectProjectsList">
            <?php
            $currentProjectsIDs = array_keys($currentProjects);
            foreach(mfcs::getProjects() as $project){
                echo sprintf("<li><label><input type='checkbox' value='%s'%s> %s</label></li>",
                    $project['ID'],
                    in_array($project['ID'], $currentProjectsIDs) ? " checked='checked'" : '',
                    $project['projectName']);
            }
            ?>
        </ul>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button class="btn btn-primary" onclick="saveSelectedProjects();">Save changes</button>
    </div>
</div>
