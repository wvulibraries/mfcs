<?php
$currentProjects = sessionGet('currentProject');
if(isset($currentProjects) and sizeof($currentProjects)){
    localvars::add('currentProjectNames', implode(', ',array_values($currentProjects)));
    localvars::add('currentProjectIDs',   implode(',',array_keys($currentProjects)));
}else{
    localvars::add('currentProjectNames', '<span style="color: #999; font-style: italic;">None Selected</span>');
    localvars::add('currentProjectIDs',   '');
}
?>
<ul class="nav">
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			Navigation
			<b class="caret"></b>
		</a>
		<ul class="dropdown-menu">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li class="dropdown-submenu">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Object Management<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Create</a></li>
					<li><a href="{local var="siteRoot"}dataView/list.php">List</a></li>
					<li><a href="{local var="siteRoot"}dataView/search.php">Search</a></li>
				</ul>
			</li>
			<li class="dropdown-submenu">
				<a tabindex="-1" href="#">Form Management</a>
				<ul class="dropdown-menu">
					<li><a href="{local var="siteRoot"}formCreator/">New Form</a></li>
					<li><a href="{local var="siteRoot"}formCreator/list.php">List Forms</a></li>
				</ul>
			</li>
			<li class="dropdown-submenu">
				<a tabindex="-1" href="#">Administrative Panel</a>
				<ul class="dropdown-menu">
					<li><a href="{local var="siteRoot"}admin/fileReProcessing/">File Re-Processing</a></li>
					<li><a href="{local var="siteRoot"}admin/projects/">Projects</a></li>
					<li><a href="{local var="siteRoot"}admin/users/">Users</a></li>
					<li><a href="{local var="siteRoot"}admin/watermarks/">Watermarks</a></li>
				</ul>
			</li>
			<li class="divider"></li>
			<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>
		</ul>
	</li>
</ul>
<div class="pull-right" style="padding: 8px; font-size: 18px; color: #ccc;">
    <strong>Current projects:</strong>
    <a href="#selectProjectsModal" id="currentProjectsLink" title="Click to change" data-toggle="modal" data-selected_projects='{local var="currentProjectIDs"}'>{local var="currentProjectNames"}</a>
</div>
