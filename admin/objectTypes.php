<?php
include("../header.php");

$tableName = $engine->dbTables("objectTypes");

function defineList($tableName) {
	$engine = EngineAPI::singleton();
	$l      = new listManagement($tableName);

	$l->addField(array(
		"field"    => "objectType",
		"label"    => "Object Type",
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
		<h1>Manage Object Types</h1>
	</header>

	{local var="results"}

	<section>
		<header>
			<h2>Add Object Type</h2>
		</header>
		{listObject display="insertForm"}
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Object Types</h2>
		</header>
		{listObject display="editTable"}
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>