<?php

include("../header.php");

ini_set('memory_limit','-1');

$display_list_toggle = FALSE;

	if (!isset($engine->cleanGet['MYSQL'])) $engine->cleanGet['MYSQL'] = array("listType" => "");

	// Setup the start of the breadcrumbs and pre-populate what we can
	$siteRoot = localvars::get('siteRoot');
	$breadCrumbs = array(
		sprintf('<a href="%s">Home</a>', $siteRoot),
		sprintf('<a href="%sdataEntry/selectForm.php">Select a Form</a>', $siteRoot)
	);

	log::insert("Data View: List",0,(isset($engine->cleanGet['MYSQL']['formID']))?$engine->cleanGet['MYSQL']['formID']:0,$engine->cleanGet['MYSQL']['listType']);

	// Figure out what kind of list we're building
	switch($engine->cleanGet['MYSQL']['listType']) {
		case 'metadataObjects':
			$list = listGenerator::metadataObjects($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
			$breadCrumbs[] = '<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Subject-Headings"> <i class="fa fa-book"></i> Documentation</a></li>';
			break;
		case 'selectForm':
			$list = listGenerator::createFormSelectList();
			localvars::add('subTitle',' - Select Form');
			// $breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=selectForm">Select Form</a>', $siteRoot);
			break;
		case 'selectProject':
			$list = listGenerator::createProjectSelectList();
			localvars::add('subTitle',' - Select Project');
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=selectProject">Select Project</a>', $siteRoot);
			break;
		case 'form':

			$display_list_toggle = TRUE;

			// $time_start = microtime(true);

			$list = listGenerator::createFormObjectList($engine->cleanGet['MYSQL']['formID']);
			$form = forms::get($engine->cleanGet['MYSQL']['formID']);
			localvars::add('subTitle',' - '.$form['title']);
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=form&formID=%s">%s</a>', $siteRoot, $form['ID'], $form['title']);
			break;
		case'formShelfList':
			$display_list_toggle = TRUE;
			$list = listGenerator::createFormShelfList($engine->cleanGet['MYSQL']['formID']);
			$form = forms::get($engine->cleanGet['MYSQL']['formID']);
			localvars::add('subTitle',' - '.$form['title']);
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=form&formID=%s">%s</a>', $siteRoot, $form['ID'], $form['title']);
			break;
		case 'formThumbnailView':
			$display_list_toggle = TRUE;
		    $list = listGenerator::createFormObjectList($engine->cleanGet['MYSQL']['formID'],TRUE);
		    $form = forms::get($engine->cleanGet['MYSQL']['formID']);
		    localvars::add('subTitle',' - '.$form['title']);
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=form&formID=%s">%s</a>', $siteRoot, $form['ID'], $form['title']);
		    break;
		case 'project':
			$list    = listGenerator::createProjectObjectList($engine->cleanGet['MYSQL']['projectID']);
			$project = projects::get($engine->cleanGet['MYSQL']['projectID']);
			localvars::add('subTitle',' - '.$project['projectName']);
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=project&projectID=%s">%s</a>', $siteRoot, $project['ID'], $project['projectName']);
			break;
		case 'all':
			$list = listGenerator::createAllObjectList_new();
			localvars::add('subTitle',' - All Objects');
			$breadCrumbs[] = sprintf('<a href="%sdataView/list.php?listType=all">All Objects</a>', $siteRoot);
			break;
		default:
			$list = listGenerator::createInitialSelectList();
			break;
	}

	localvars::add("list",$list);
	localvars::add("formID",$engine->cleanGet['MYSQL']['formID']);

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