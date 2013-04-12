<?php

include("../header.php");

try {

	if (!isset($engine->cleanGet['MYSQL'])) $engine->cleanGet['MYSQL'] = array("listType" => "");

	switch($engine->cleanGet['MYSQL']['listType']) {
		case 'selectForm':
			$list = listGenerator::createFormSelectList();
			break;
		case 'selectProject':
			$list = "Select Form";
			break;
		case 'form':
			$list = listGenerator::createFormObjectList($engine->cleanGet['MYSQL']['formID']);
			break;
		case 'project':
			$list = "Project";
			break;
		case 'all':
			$list = listGenerator::createAllObjectList();
			break;
		default:
			$list = listGenerator::createInitialSelectList();
			break;
	}

	localvars::add("list",$list);

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Listing Objects</h1>
	</header>

	{local var="results"}


	{local var="list"}


</section>


<?php
$engine->eTemplate("include","footer");
?>