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
<!-- Modal - Select Current Projects -->
<div id="selectProjectsModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Your current projects:</h3>
	</div>
	<div class="modal-body">{local var="projectModalList"}</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button class="btn btn-primary" onclick="saveSelectedProjects();">Save changes</button>
	</div>
</div>

