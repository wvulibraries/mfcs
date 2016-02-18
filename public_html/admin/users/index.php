<?php

include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(3)){
	header('Location: /index.php?permissionFalse');
}

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
		"field"    => "email",
		"label"    => "Email",
		"dupes"    => TRUE,
		"blank"    => TRUE
		));

	$l->addField(array(
		"field"    => "isStudent",
		"label"    => "Is the User a Student?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$l->addField(array(
		"field"    => "active",
		"label"    => "Is the User Active?",
		"dupes"    => TRUE,
		"type"     => "yesNo"
		));

	$l->addField(array(
		"field"    => "status",
		"label"    => "Status",
		"type"     => "select",
		"dupes"    => TRUE,
		"options"  => array(
			array("value"=>"Editor","label"=>"Editor"),
			array("value"=>"User","label"=>"User","selected"=>TRUE),
			array("value"=>"Admin","label"=>"Admin")
			)
		));

	$l->addField(array(
		"field"    => "formCreator",
		"label"    => "Form Creator",
		"dupes"    => TRUE,
		"type"     => "yesNo"
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

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Users"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>


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
		<div class="table-responsive editUsersTable">
			{listObject display="editTable"}
		</div>
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>
