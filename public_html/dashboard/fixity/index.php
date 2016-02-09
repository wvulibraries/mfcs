<?php
require_once "../../header.php";

$sql       = sprintf("SELECT * FROM `logs` WHERE `action`='fixity' ORDER BY `date`,`formID`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
}

$fixity_info = "";
while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
	$object = objects::get($row['objectID']);

	$fixity_info = sprintf('<p><span class="fixity_date">Date: %s</span> -- <span class="fixity_form_title">Form: %s</span> -- <span class="fixity_obj_idno">Object IDNO: %s</span> -- <span class="fixity_file">File: %s</span></p>',
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
	</ul>

<p></p>

{local var="fixity_info"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
