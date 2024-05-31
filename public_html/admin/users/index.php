<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(3)){
	header('Location: /index.php?permissionFalse');
}

$tableName = "users";

function defineList($tableName) {
    $l = new listManagement($tableName);

	$fields = array(
		array("field" => "username", "label" => "Username"),
		array("field" => "firstname", "label" => "First Name", "dupes" => true),
		array("field" => "lastname", "label" => "Last Name", "dupes" => true),
		array("field" => "email", "label" => "Email", "dupes" => true, "blank" => true),
		array("field" => "isStudent", "label" => "Is the User a Student?", "dupes" => true, "type" => "yesNo"),
		array("field" => "active", "label" => "Is the User Active?", "dupes" => true, "type" => "yesNo"),
		array("field" => "status", "label" => "Status", "type" => "select", "dupes" => true, "options" => array(
			array("value" => "Editor", "label" => "Editor"),
			array("value" => "User", "label" => "User", "selected" => true),
			array("value" => "Admin", "label" => "Admin")
		)),
		array("field" => "formCreator", "label" => "Form Creator", "dupes" => true, "type" => "yesNo")
	);

    foreach ($fields as $field) {
        $l->addField($field);
    }

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
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Users" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
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
