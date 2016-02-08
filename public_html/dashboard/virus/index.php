<?php
require_once "../../header.php";

$sql       = sprintf("SELECT * FROM `logs` WHERE `action`='virus' ORDER BY `date`,`formID`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
}

$virus_info = "";
while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
	$object = objects::get($row['objectID']);

	$virus_info = sprintf('<p><span class="virus_date">Date: %s</span> -- <span class="virus_form_title">Form: %s</span> -- <span class="virus_obj_idno">Object IDNO: %s</span> -- <span class="virus_file">File: %s</span></p>',
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

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Virus History</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dashboard">Dashboard</a></li>
	</ul>

<p></p>

{local var="virus_info"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
