<?php

include("../../header.php");
include("../../includes/classes/scheduler.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(3)){
	header('Location: /index.php?permissionFalse');
}

$tableName = "scheduler";
$scheduler = new scheduler("../../crons");

include("../../includes/formDefinitions/form_scheduler.php");

if (isset($engine->cleanPost['MYSQL'][$tableName."_submit"])) {

	log::insert("Admin: Insert New Cron Job");

	$list = defineList($scheduler, $tableName);
	$list->insert();
}

if (isset($engine->cleanPost['MYSQL'][$tableName."_update"])) {

	log::insert("Admin: Update Cron Job");

	$list = defineList($scheduler, $tableName);
	$list->update();
}

$list = defineList($scheduler, $tableName);

localVars::add("results",displayMessages());

log::insert("Admin: View Scheduler Page");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Manage Cron Jobs</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Scheduler" target="_blank"> <span class="fa fa-book"></span> Documentation</a></li>
	</ul>

	{local var="results"}

	<section>
		<header>
			<h2>Add New Job</h2>
		</header>
		{listObject display="insertForm"}
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Scheduled Jobs</h2>
		</header>
		<div class="table-responsive editUsersTable">
			{listObject display="editTable"}
		</div>
	</section>

</section>

<?php
$engine->eTemplate("include","footer");
?>
