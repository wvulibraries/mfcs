<?php

include("../../header.php");

$tableName = "users";

function defineList($tableName) {
	$l      = new listManagement($tableName);

	$l->addField(array(
		"field"    => "username",
		"label"    => "Username",
		));

	$l->addField(array(
		"field"    => "firstname",
		"label"    => "First Name",
		"dupes"    => TRUE
		));

	$l->addField(array(
		"field"    => "lastname",
		"label"    => "Last Name",
		"dupes"    => TRUE
		));

	$l->addField(array(
		"field"    => "status",
		"label"    => "Status",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => array(
			array("value"=>"Librarian","label"=>"Librarian"),
			array("value"=>"Staff","label"=>"Staff"),
			array("value"=>"Student","label"=>"Student","selected"=>TRUE),
			array("value"=>"Systems","label"=>"Systems")
			)
		));

	return $l;
}

if (isset($engine->cleanPost['MYSQL'][$tableName."_submit"])) {

	log::insert("Admin: Insert New User");

	$list = defineList($tableName);
	$list->insert();
}
if (isset($engine->cleanPost['MYSQL'][$tableName."_update"])) {

	log::insert("Admin: Update User");

	$list = defineList($tableName);
	$list->update();
}

$list = defineList($tableName);

localVars::add("results",displayMessages());

log::insert("Admin: View Users Page");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Manage Users</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
		</ul>
	</nav>

	{local var="results"}

	<section>
		<header>
			<h2>Add User</h2>
		</header>
		{listObject display="insertForm"}
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Users</h2>
		</header>
		{listObject display="editTable"}
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>
