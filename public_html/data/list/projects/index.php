<?php

include("../../../header.php");

ini_set('memory_limit','-1');

$display_list_toggle = FALSE;

	if (!isset($engine->cleanGet['MYSQL'])) $engine->cleanGet['MYSQL'] = array("listType" => "");

	// Setup the start of the breadcrumbs and pre-populate what we can
	$siteRoot = localvars::get('siteRoot');
	$breadCrumbs = array(
		sprintf('<a href="%s">Home</a>', $siteRoot)
	);

	log::insert("Data View: List",0,(isset($engine->cleanGet['MYSQL']['formID']))?$engine->cleanGet['MYSQL']['formID']:0,$engine->cleanGet['MYSQL']['listType']);

	// Figure out what kind of list we're building
	switch($engine->cleanGet['MYSQL']['listType']) {
		case 'project':
			$list    = listGenerator::createProjectObjectList($engine->cleanGet['MYSQL']['projectID']);
			$project = projects::get($engine->cleanGet['MYSQL']['projectID']);
			localvars::add('subTitle',' - '.$project['projectName']);
			$breadCrumbs[] = sprintf('<a href="%sdata/list/projects/?listType=project&projectID=%s">%s</a>', $siteRoot, $project['ID'], $project['projectName']);
			break;
		case 'selectProject':
		default:
			$list = listGenerator::createProjectSelectList();
			localvars::add('subTitle',' - Select Project');
			$breadCrumbs[] = sprintf('<a href="%sdata/list/projects/">Select Project</a>', $siteRoot);
			break;
	}

	localvars::add("list",$list);

	// Make breadcrumbs
	$crumbs = '';
	foreach($breadCrumbs as $breadCrumb){
		$crumbs .= "<li>$breadCrumb</li>";
	}
	localvars::add("breadcrumbs", $crumbs);



localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1> List Objects {local var="subTitle"} </h1>
	</header>
	<ul class="breadcrumbs">
		{local var="breadcrumbs"}
	</ul>

	<?php if ($display_list_toggle) { ?>
		<div class="listToggles btn-group button-group pull-right">
			<a class="btn btn-primary" href="{local var="siteRoot"}dataView/list.php?listType=form&formID={local var="formID"}"><i class="fa fa-list-ol"></i></a>
			<a class="btn btn-primary" href="{local var="siteRoot"}dataView/list.php?listType=formThumbnailView&formID={local var="formID"}"><i class="fa fa-picture-o"></i></a>
			<a class="btn btn-primary" href="{local var="siteRoot"}dataView/list.php?listType=formShelfList&formID={local var="formID"}"><i class="fa fa-list"></i></a>
		</div>
	<?php } ?>

	{local var="results"}


	{local var="list"}


</section>


<?php
$engine->eTemplate("include","footer");
?>