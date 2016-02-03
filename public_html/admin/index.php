<?php
require("header.php");

log::insert("Admin: View index Page");

// I don't think this is a used page
// @TODO REMOVE?

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(3)){
	header('Location: /index.php?permissionFalse');
}

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1 class="page-title">Admin Home</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/index.php">Admin Home</a></li>
	</ul>


	<ul class="pickList">
		<li>
			<a href="projects/" class="btn">Projects</a>
		</li>
		<li>
			<a href="users/" class="btn">Users</a>
		</li>
		<li>
			<a href="watermarks/" class="btn">Watermarks</a>
		</li>
	</ul>
</section>

<?php
$engine->eTemplate("include","footer");
?>
