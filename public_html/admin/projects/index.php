<?php
include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

$tableName = "projects";

function defineList($tableName) {
	// $engine = EngineAPI::singleton();
	$l = new listManagement($tableName);

	$l->addField(array(
		"field"    => "projectName",
		"label"    => "Project Name",
	));

	$l->addField(array(
		"field"    => "projectID",
		"label"    => "Project ID (Short Name)",
		"validate" => "alphaNoSpaces"
	));

	$l->addField (array(
		'field'    => "ID",
		'label'    => "ID",
		'type'     => "hidden",
		'disabled' => TRUE
	));

	return $l;
}

if (isset($engine->cleanPost['MYSQL'][$tableName."_submit"])) {

	log::insert("Admin: Add Project");

	$list = defineList($tableName);
	$list->insert();
}
if (isset($engine->cleanPost['MYSQL'][$tableName."_update"])) {

	log::insert("Admin: Update Projects");

	$list = defineList($tableName);
	$list->update();
}

$list = defineList($tableName);

localVars::add("results",displayMessages());

log::insert("Admin: View Projects Page");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Manage Projects</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
	</ul>

	{local var="results"}

	<section>
		<header>
			<h2>Add Project</h2>
		</header>
		{listObject display="insertForm"}
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Projects</h2>
		</header>
		<div class="projectsEditTable responsive-table">
			{listObject display="editTable"}
		</div>
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>
