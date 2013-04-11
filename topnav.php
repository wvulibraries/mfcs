<!-- Modal - Select Current Projects -->
<div id="selectProjects" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Your current projects:</h3>
    </div>
    <div class="modal-body">
        <ul id="selectProjectsList">
            <?php

            // @TODO : This logic should be moved out of the HTML and put into a local variable

            // $currentProjectsIDs = array_keys($currentProjects);
            // foreach(projects::getProjects() as $project){
            //     echo sprintf("<li><label><input type='checkbox' value='%s'%s> %s</label></li>",
            //         $project['ID'],
            //         in_array($project['ID'], $currentProjectsIDs) ? " checked='checked'" : '',
            //         $project['projectName']);
            // }
            ?>
	</ul>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button class="btn btn-primary" onclick="saveSelectedProjects();">Save changes</button>
    </div>
</div>

<ul class="nav">
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			Navigation
			<b class="caret"></b>
		</a>
		<ul class="dropdown-menu">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li class="dropdown-submenu">
				<a tabindex="-1" href="#">Administrative Panel</a>
				<ul class="dropdown-menu">
					<li><a href="{local var="siteRoot"}admin/objectTypes.php">Manage Object Types</a></li>
					<li><a href="{local var="siteRoot"}admin/containers.php">Manage Containers</a></li>
					<li><a href="{local var="siteRoot"}admin/watermarks.php">Manage Watermarks</a></li>
					<li><a href="{local var="siteRoot"}admin/users.php">Manage Users</a></li>
				</ul>
			</li>
			<li><a href="{local var="siteRoot"}projects/">Project Management</a></li>
			<li class="dropdown-submenu">
				<a tabindex="-1" href="#">Form Management</a>
				<ul class="dropdown-menu">
					<li><a href="{local var="siteRoot"}formCreator/">New Form</a></li>
					<li><a href="{local var="siteRoot"}formCreator/list.php">List Forms</a></li>
				</ul>
			</li>
			<li class="divider"></li>
			<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>
		</ul>
	</li>
</ul>
<div class="pull-right" style="padding: 8px; font-size: 18px; color: #ccc;">
    <strong>Current projects:</strong>
    <a href="#selectProjects" title="Click to change" data-toggle="modal">
        <?php
        // Global is needed because this file is loaded inside a function (and looses variable scope)
        global $currentProjects;
        if(isset($currentProjects) and sizeof($currentProjects)){
            $projects = array();
            foreach($currentProjects as $currentProjectID => $currentProjectName){
                $projects[] = $currentProjectName;
            }
            echo implode(', ',$projects);
        }else{
            echo '<span style="color: #999; font-style: italic;">None Selected</span>';
        }
        ?>
    </a>
</div>
