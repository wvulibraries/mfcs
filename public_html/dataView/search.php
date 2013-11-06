<?php

include("../header.php");

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

		$results = mfcsSearch::search($engine->cleanPost['MYSQL']);
		if($results === FALSE) throw new Exception("Error retrieving results");
	}
	catch(Exception $e) {
		errorHandle::errorMsg($e->getMessage());
	}
}
elseif(isset($engine->cleanGet['MYSQL']['page'])) {
	$searchPOST = sessionGet('searchPOST');
	if($searchPOST) {

		$results = mfcsSearch::search($searchPOST);
		if($results === FALSE) throw new Exception("Error retrieving results");

	}
}
else{
	sessionDelete('searchPOST');
}

if(isset($results)) localvars::add("objectTable",listGenerator::createAllObjectList(0,50,NULL,$results));


// build the search interface, we do this regardless of 
try {

	$interface = mfcsSearch::buildInterface();
	localvars::add("searchInterface",$interface);
}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

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