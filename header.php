<?php
require("engineInclude.php");

// recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

// Load the mfcs class
require_once "includes/mfcs.php";
require_once "includes/projects.php";
require_once "includes/forms.php";
require_once "includes/objects.php";
mfcs::singleton();

// Process 'my current project' from the session
$currentProjects = sessionGet('currentProject');
// if(is_null($currentProjects)){
//     // Load the user's projects from the db
//     $currentProjects = array();
//     $sql = sprintf("SELECT ID,projectName FROM `projects` LEFT JOIN users_projects ON users_projects.projectID=projects.ID WHERE users_projects.userID=%s",
//         mfcs::$engine->openDB->escape(mfcs::user('ID')));
//     $sqlResult = $engine->openDB->query($sql);
//     if(!$sqlResult['result']){
//         errorHandle::newError("Failed to load user's projects ({$sqlResult['error']})", errorHandle::HIGH);
//         errorHandle::errorMsg("Failed to load your current projects.");
//     }else{
//         while($row = mysql_fetch_assoc($sqlResult['result'])){
//             $currentProjects[ $row['ID'] ] = $row['projectName'];
//         }
//     }
// }

recurseInsert("includes/functions.php","php");
recurseInsert("includes/validator.php","php");

$engine->eTemplate("load","distribution");

localVars::add("siteRoot",$engineVars['WEBROOT']."/mfcs/");
localVars::add('pageTitle',"Metadata Form Creation System");
localVars::add('pageHeader',"Metadata Form Creation System");
?>