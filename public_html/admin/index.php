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


	<p>
		Please select an option from the Navigation Menu.
	</p>
</section>

<?php
$engine->eTemplate("include","footer");
?>
