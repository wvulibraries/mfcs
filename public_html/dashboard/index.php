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

$sql       = sprintf("SELECT `value` FROM `system_information` WHERE `name`='file_types'");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	errorHandle::errorMsg("Unable to get File Types");
}

$row        = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
$file_types = decodeFields($row['value']);

$file_types_list = "";
foreach ($file_types["types"] as $type=>$count) {
	$file_types_list .= sprintf('<p><strong class="fileItem">%s:</strong><span class="fileCount">%s</span></p>',
		(is_empty($type))?"[No File Extension]":htmlSanitize($type),
		htmlSanitize($count)
		);
}

$files_type_list_by_form = "";
foreach ($file_types['forms'] as $formID=>$types) {

	$files_type_list_by_form .= sprintf('<h3>%s</h3>',forms::title($formID));
	foreach ($types['types'] as $type=>$count) {
		$files_type_list_by_form .= sprintf('<p><strong class="fileItem">%s:</strong><span class="fileCount">%s</span></p>',
			(is_empty($type))?"[No File Extension]":htmlSanitize($type),
			htmlSanitize($count)
			);
	}
}

localvars::add("drive_size",sprintf("%10.2fGB",system_information::drive_size()));
localvars::add("free_space",sprintf("%10.2fGB",system_information::free_space()));
localvars::add("archives_usage",sprintf("%10.2fGB",system_information::archives_usage()));
localvars::add("files_type_list",        $file_types_list);
localvars::add("files_type_list_by_form",$files_type_list_by_form);
$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Dashboard</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
	</ul>

	<div class="leftContainerDash">
		<div class="dashboardContainer">
			<h2> System Counts </h2>
			<p>
				<strong class="fileItem">Total Objects in system: </strong>
				<span class="fileCount">{local var="objects_total"}</span>
			</p>
			<p>
				<strong class="fileItem">Total Metadata Objects: </strong>
				<span class="fileCount">{local var="metadata_total"}</span>
			</p>
			<p>
				<strong class="fileItem">Total Object Forms: </strong>
				<span class="fileCount">{local var="forms_object_total"}</span>
			</p>
			<p>
				<strong class="fileItem">Object Forms in Production: </strong>
				<span class="fileCount">{local var="forms_production"}</span>
			</p>
			<p>
				<strong class="fileItem">Total Metadata Forms: </strong>
				<span class="fileCount">{local var="metadate_form_total"}</span>
			</p>
		</div>
	</div>

	<div class="rightContainerDash">
		<div class="dashboardContainer">
			<h2>Fixity Information</h2>
			<p>
				<strong class="fileItem"> Files with failed fixity: </strong>
				<span class="fileCount"> {local var="failed_fixity"}</span>
			</p>
			<p>
				<strong class="fileItem">Files without Checksum: </strong>
				<span class="fileCount">{local var="no_checksum"} </span>
			</p>
		</div>
		<div class="dashboardContainer">
			<h2>Virus Information</h2>
			<p>
				<strong class="fileItem"> Current Virus Count: </strong>
				<span class="fileCount"> {local var="virus_count"}</span>
			</p>
		</div>
	</div>

	<div class="leftContainerDash">
		<div class="dashboardContainer">
			<h2>File Types Counts, by Form</h2>
			{local var="files_type_list_by_form"}
		</div>
	</div>

	<div class="rightContainerDash">
		<div class="dashboardContainer">
			<h2>File Types Total Counts</h2>
			{local var="files_type_list"}
		</div>
	</div>

	<div class="rightContainerDash">
		<div class="dashboardContainer">
			<h2>Drive Space Information</h2>
			<p>
				<strong class="fileItem">Archives Usage: </strong>
				<span class="fileCount"> {local var="archives_usage"}</span>
			</p>
			<p>
				<strong class="fileItem">Drive Size/Free Space: </strong>
				<span class="fileCount">{local var="drive_size"}/{local var="free_space"} </span>
			</p>
		</div>
		<div class="dashboardContainer">
			<h2>Virus Information</h2>
			<p>
				<strong class="fileItem"> Current Virus Count: </strong>
				<span class="fileCount"> {local var="virus_count"}</span>
			</p>
		</div>
	</div>
</section>

<?php
$engine->eTemplate("include","footer");
?>
