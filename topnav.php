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
					<li><a href="{local var="siteRoot"}admin/users.php">Manage Users</a></li>
				</ul>
			</li>
			<li><a href="{local var="siteRoot"}projects/">Project Management</a></li>
			<li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
			<li class="divider"></li>
			<li><a href="{engine var="logoutPage"}?csrf={engine name="csrfGet"}">Logout</a></li>
		</ul>
	</li>
</ul>
