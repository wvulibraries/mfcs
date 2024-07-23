<?php
require_once "../../header.php";

log::insert("Dashboard: Duplicate View");

$sql       = sprintf("SELECT `checksum`, COUNT(`checksum`) FROM `filesChecks` GROUP BY `checksum` HAVING COUNT(`checksum`) > 1");
$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
}

$duplicate_files = "";

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

	$sql       = sprintf("SELECT * from `filesChecks` WHERE `checksum`='%s'",$row['checksum']);
	$sqlResult2 = $engine->openDB->query($sql);
	
	if (!$sqlResult2['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
		return FALSE;
	}
	
	$duplicate_files .= sprintf('<div class="duplicate_files_holder"><h2>%s</h2>',$row['checksum']);
	while ($file = mysql_fetch_array($sqlResult2['result'],  MYSQL_ASSOC)) {
		$object = objects::get($file['objectID']);
		$duplicate_files .= sprintf('<p><span class="dupe_object">Object ID: <a href="%sdataEntry/object.php?objectID=%s">%s</a></span>',mfcs::config("siteRoot"),$file['objectID'],$file['objectID']);
		$duplicate_files .= sprintf('<p><span class="dupe_form">Form: %s</span>',forms::title($object['formID']));

	}
	$duplicate_files .= "</div>";

}

localvars::add("duplicate_files",$duplicate_files);

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Duplicate Files</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dashboard">Dashboard</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Duplicates" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

<p></p>

{local var="duplicate_files"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
