<?php
require_once "../../header.php";

$sql       = sprintf("SELECT * FROM `logs` WHERE `action`='virus' ORDER BY `date`,`formID`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
}

$virus_info = "";
while($row       = mysqli_fetch_array($sqlResult['result'])) {
	$object = objects::get($row['objectID']);

	$virus_info = sprintf('<div id="virus_holder"><span class="virus_date"><b>Date:</b> %s</span>  <span class="virus_form_title"><b>Form:</b> %s</span>  <span class="virus_obj_idno"><b>Object IDNO:</b> %s</span>  <span class="virus_file"><b>File:</b> %s</span></div>',
		date("Y-m-d H:i",$row['date']),
		forms::title($row['formID']),
		$object['idno'],
		htmlSanitize($row['info'])
		);
}

if (is_empty($virus_info)) {
	$virus_info = '<p class="no_virus_history successMessage" >No virus history found</p>';
}

localvars::add("virus_info", $virus_info);

log::insert("Dashboard: Virus Audit");

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Virus History</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dashboard">Dashboard</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Virus" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

<p></p>

{local var="virus_info"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
