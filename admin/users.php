<?php

include("../header.php");

$tableName = $engine->dbTables("users");

function defineList($tableName) {
	$l      = new listManagement($tableName);

	$l->addField(array(
		"field"    => "username",
		"label"    => "Username",
		));

	$l->addField(array(
		"field"    => "firstname",
		"label"    => "First Name",
		));

	$l->addField(array(
		"field"    => "lastname",
		"label"    => "Last Name",
		));

	$l->addField(array(
		"field"    => "status",
		"label"    => "Status",
		"type"     => "select",
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
	$list = defineList($tableName);
	$list->insert();
}
if (isset($engine->cleanPost['MYSQL'][$tableName."_update"])) {
	$list = defineList($tableName);
	$list->update();
}

$list = defineList($tableName);

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Manage Users</h1>
	</header>

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