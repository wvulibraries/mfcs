<?php
include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}


if (isset($engine->cleanGet['MYSQL']['toggle']) && $engine->cleanGet['MYSQL']['toggle'] == "true") {
	if (checks::is_ok("readonly")) {
		checks::set_error("readonly");
	}
	else {
		checks::set_ok("readonly");
	}
}

localvars::add("mode",(checks::is_ok("readonly"))?"Read Only":"Write");
localvars::add("phpself",$_SERVER['PHP_SELF']);
log::insert("Admin: Toggle MFCS Read Only");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Read Only Mode</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/">Admin</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Read-Only-Mode" target="_blank"> <span class="fa fa-book"></span> Documentation</a></li>
	</ul>

	{local var="results"}

	<section>
		<div class="systemMessage {local var="mode"}"> <p> System is currently in {local var="mode"} mode. </p></div>
	</section>

	<a href="{local var="phpself"}?toggle=true" class="toggleSystem btn btn-primary">Toggle</a>
</section>

<?php
$engine->eTemplate("include","footer");
?>
