<?php

include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

// Setup the start of the breadcrumbs and pre-populate what we can
$siteRoot = localvars::get('siteRoot');
$breadCrumbs = array(
	sprintf('<a href="%s">Home</a>', $siteRoot),
	sprintf('<a href="%s/dataView/Search.php">Search Objects</a>', $siteRoot)
	);

// Process search Submission
if (isset($engine->cleanPost['MYSQL']['search'])) {
	try {
		if(isnull($engine->cleanPost['MYSQL']['formList'])){
			throw new Exception("No form selected.");
		}

		if (isempty($engine->cleanPost['MYSQL']['query']) && (isempty($engine->cleanPost['MYSQL']['startDate']) || isempty($engine->cleanPost['MYSQL']['endDate']))) {
			throw new Exception("No Query Provided.");
		}

		sessionSet("lastSearchForm",$engine->cleanPost['HTML']['formList']);
		sessionSet("searchResults","");
		sessionSet("searchQuery", $engine->cleanPost['MYSQL']);

		log::insert("Data View: Search: Search",0,0,$engine->cleanPost['MYSQL']['query']);

		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
		// $results = mfcsSearch::search($engine->cleanPost['MYSQL']);
		// if($results === FALSE) throw new Exception("Error retrieving results");
	}
	catch(Exception $e) {
		log::insert("Data View: Search: Error",0,0,$e->getMessage());
		errorHandle::errorMsg($e->getMessage());
	}
}
else if (!is_empty(sessionGet('searchResults'))) {
	log::insert("Data View: Search: get results");
	$results = sessionGet('searchResults');
}
else if (!is_empty(sessionGet('searchQuery'))) {

	log::insert("Data View: Search: get saved search");

	$searchQuery = sessionGET('searchQuery');

	try {
		$results = mfcsSearch::search($searchQuery);
		if($results === FALSE) throw new Exception("Error retrieving results");
		sessionSet("searchResults",$results);
	}
	catch(Exception $e) {
		log::insert("Data View: Search: Error",0,0,$e->getMessage());
		errorHandle::errorMsg($e->getMessage());
	}
}
else if(isset($engine->cleanGet['MYSQL']['page'])) {

	log::insert("Data View: Search: page");

	$searchPOST = sessionGet('searchPOST');
	if($searchPOST) {

		$results = mfcsSearch::search($searchPOST);
		if($results === FALSE) throw new Exception("Error retrieving results");

	}
}
else{
	log::insert("Data View: Search: Delete post");
	sessionDelete('searchPOST');
}

if(isset($results)) localvars::add("objectTable",listGenerator::createAllObjectList(0,50,NULL,$results));


// build the search interface, we do this regardless of
try {

	$interface = mfcsSearch::buildInterface();
	localvars::add("searchInterface",$interface);
}
catch(Exception $e) {
	log::insert("Data View: Search: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

// Make breadcrumbs
$crumbs = '';
foreach($breadCrumbs as $breadCrumb){
	$crumbs .= "<li>$breadCrumb</li>";
}
localvars::add("breadcrumbs", $crumbs);

localVars::add("results",displayMessages());

log::insert("Data View: Search: View Page");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Search Objects{local var="subTitle"}</h1>
	</header>
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			{local var="breadcrumbs"}
		</ul>
	</nav>

	{local var="results"}


	{local var="searchInterface"}

	<hr />

	{local var="objectTable"}


</section>


<?php
$engine->eTemplate("include","footer");
?>