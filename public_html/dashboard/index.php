<?php
require_once "../header.php";

$counts = array(
"objects_total"       => sprintf("SELECT COUNT(*) FROM `objects` WHERE `metadata`='0'"),
"metadata_total"      => sprintf("SELECT COUNT(*) FROM `objects` WHERE `metadata`='1'"),
"forms_object_total"  => sprintf("SELECT COUNT(*) FROM `forms` WHERE `metadata`='0'"),
"metadate_form_total" => sprintf("SELECT COUNT(*) FROM `forms` WHERE `metadata`='1'"),
"forms_production"    => sprintf("SELECT COUNT(*) FROM `forms` WHERE `metadata`='0' AND `production`='1'"),
"failed_fixity"       => sprintf("SELECT COUNT(*) FROM `filesChecks` WHERE `pass`='0'"),
"no_checksum"         => sprintf("SELECT COUNT(*) FROM `filesChecks` WHERE `checksum` is null"),
"virus_count"         => sprintf("SELECT COUNT(*) FROM `virusChecks` WHERE `state`='3'")
);

foreach ($counts as $type=>$sql) {

	$sqlResult = $engine->openDB->query($sql);
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	}

	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	localvars::add($type,$row["COUNT(*)"]);
}

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Dashboard</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
		</ul>
	</nav>

	<div class="dashboard_container">
		<h2>System Counts</h2>
		<p>Total Objects in system: {local var="objects_total"}</p>
		<p>Total Metadata Objects: {local var="metadata_total"}</p>
		<p>Total Object Forms: {local var="forms_object_total"}</p>
		<p>Object Forms in Production: {local var="forms_production"}</p>
		<p>Total Metadata Forms: {local var="metadate_form_total"}</p>
	</div>

	<div class="dashboard_container">
		<h2>Fixity Information</h2>

		<p>Files with failed fixity: {local var="failed_fixity"}</p>
		<p>Files without Checksum: {local var="no_checksum"}</p>
	</div>

	<div class="dashboard_container">
		<h2>Virus Information</h2>

		<p>Current Virus Count: {local var="virus_count"}</p>
	</div>

</section>

<?php
$engine->eTemplate("include","footer");
?>
