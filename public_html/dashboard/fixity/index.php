<?php
require_once "../../header.php";

$sql       = sprintf("SELECT * FROM `logs` WHERE `action`='fixity' ORDER BY `date`,`formID`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
}

$fixity_info = "";
while($row       = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
	$object = objects::get($row['objectID']);

	$fixity_info = sprintf('<div id="fixity_holder"><span class="fixity_date"><b>Date:</b> %s</span>  <span class="fixity_form_title"><b>Form:</b> %s</span>  <span class="fixity_obj_idno"><b>Object IDNO:</b> %s</span>  <span class="fixity_file"><b>File:</b> %s</span></div>',
		date("Y-m-d H:i",$row['date']),
		forms::title($object['formID']),
		$object['idno'],
		htmlSanitize($row['info'])
		);
}

if (is_empty($fixity_info)) {
	$fixity_info = '<p class="no_fixity_history successMessage" >No fixity error history found</p>';
}

localvars::add("fixity_info", $fixity_info);

log::insert("Dashboard: Fixity Audit");

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Fixity History</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dashboard">Dashboard</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Fixity" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

<p></p>

{local var="fixity_info"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
