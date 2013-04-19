<?php

include("../header.php");

try {

	if (!isset($engine->cleanGet['MYSQL'])) $engine->cleanGet['MYSQL'] = array("listType" => "");

	switch($engine->cleanGet['MYSQL']['listType']) {
		case 'selectForm':
			$list = listGenerator::createFormSelectList();
			break;
		case 'selectProject':
			$list = listGenerator::createProjectSelectList();
			break;
		case 'form':
			$list = listGenerator::createFormObjectList($engine->cleanGet['MYSQL']['formID']);
			break;
		case 'project':
			$list = listGenerator::createProjectObjectList($engine->cleanGet['MYSQL']['projectID']);
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
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li>
				<a href="{local var="siteRoot"}">Home</a>
				<span class="divider">/</span>
				<a href="{local var="siteRoot"}/dataView/list.php">List Objects</a>
			</li>
		</ul>
	</nav>

	{local var="results"}


	{local var="list"}


</section>


<?php
$engine->eTemplate("include","footer");
?>