<?php
// header
include("../../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

$siteRoot = localvars::get('siteRoot');

// Process search Submission
if (isset($engine->cleanGet['MYSQL']['reset'])) {
    sessionDelete("searchQuery");
    sessionDelete('searchPOST');
    sessionDelete("lastSearchForm");
}
else if (isset($engine->cleanPost['MYSQL']['search'])) {
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

        if (isset($engine->cleanPost['MYSQL']['thumbnail'])) sessionSet("searchThumbs",$engine->cleanPost['MYSQL']['thumbnail']);

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

    $searchQuery = sessionGet('searchQuery');

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

$searchThumbs = str2bool(sessionGet("searchThumbs"));

if(isset($results)){
    localvars::add('objectTable', listGenerator::moveObjectListResults($results, $searchThumbs));
    localvars::add('moveToForm', listGenerator::createFormDropDownList());
}

// build the search interface, we do this regardless of
try {
    $interface = mfcsSearch::buildInterface();

    if($interface === false){
        throw new Exception('Problem rendering the search interface in the move objects page.');
    }

    localvars::add("searchInterface",$interface);
}
catch(Exception $e) {
    log::insert("Search Inteface Move Page Error: Error",0,0,$e->getMessage());
    errorHandle::errorMsg($e->getMessage());
}


$engine->eTemplate("include","header");

?>

<section>

    <header class="page-header">
        <h1> Move Files </h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/moveObjects/"> Move Objects </a></li>

        <li class="pull-right noDivider">
            <a href="#batchUploadDocumentation" target="_blank">
                <i class="fa fa-book"></i> Documentation
            </a>
        </li>
    </ul>

    <div id="formAlert" class="alert alert-danger alert-dismissible hide" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>Error!</strong> <p> Please check to be sure that you have selected objects to move and that you have selected a form that will be receiving these objects.  </p>
    </div>

    <div class="row-fluid">
        <h2> Search Objects </h2>

        {local var="searchInterface"}
        {local var="objectTable"}

        <div class="selectControls">
            <a href="javascript:void(0)" class="btn btn-default pull-right removeAllObjects objBtn"> Un-Select All </a>
            <a href="javascript:void(0)" class="btn btn-primary pull-right selectAllObjects objBtn"> Select All </a>
        </div>
        <br><br><br>
    </div>

    <div class="row-fluid">
        <form id="performMove">
            <h2>Move Objects to Form </h2>
            {local var="moveToForm"}
            <input id="selectedObjectIDs" type="hidden" name="selectedObjectIDs" value="" />

            <input type="submit" class="btn pull-right submit"/>
        </form>
    </div>

</section>

<?php
$engine->eTemplate("include","footer");
?>
