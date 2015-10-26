<?php
$currentProjectsIDs = array_keys(sessionGet('currentProject'));
$projectListHTML = '<ul id="selectProjectsList">';

try {
	if (($projects = projects::getProjects()) === FALSE) {
		throw new Exception("Error retrieving project list.");
	}

	foreach($projects as $project){
		$projectListHTML .= sprintf("<li><label><input type='checkbox' value='%s' data-label='%s'%s> %s</label></li>",
			$project['ID'],
			$project['projectName'],
			in_array($project['ID'], $currentProjectsIDs) ? " checked='checked'" : '',
			$project['projectName']);
	}
}
catch(Exception $e) {
	$projectListHTML .= "<li>".$e->getMessage()."</li>";
}

$projectListHTML .= '</ul>';
localvars::add('projectModalList', $projectListHTML);
?>
{engine name="csrf"}