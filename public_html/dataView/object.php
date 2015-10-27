<?php
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

$permissions = TRUE;

try {

	// If we have an objectID and no formID, lookup the formID from the object and set it back into the GET
	if(isset($engine->cleanGet['MYSQL']['objectID']) and !isset($engine->cleanGet['MYSQL']['formID'])){
		$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
		http::setGet('formID', $object['formID']);
	}

	// Object ID Validation
	if (objects::validID(TRUE) === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (mfcsPerms::isViewer($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (!objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		errorHandle::newError("Object not from this form.", errorHandle::DEBUG);
		throw new Exception("Object not from this form");
	}

	log::insert("Data View: Object",$engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID']);

	//////////
	// Metadata Tab Stuff
	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	localvars::add("formName",$form['title']);

	// build the form for displaying
	$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building form.");
	}

	localvars::add("form",$builtForm);

	// build the files list for displaying
	$filesViewer = files::buildFilesPreview($engine->cleanGet['MYSQL']['objectID']);
	localvars::add("filesViewer",$filesViewer);


	// Metadata Tab Stuff
	//////////

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	if(!is_empty($selectedProjects)){
		localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
	}
	else {
		localVars::add("projectOptions","<div class='alert alert-warning'> No Projects Assigned to this Object </div>");
	}

	// Project Tab Stuff
	//////////

	//////////
	// Children Tab Stuff
	if (($formList = listGenerator::generateFormSelectList($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
		errorHandle::errorMsg("Error getting Forms Listing");
		throw new Exception('Error');
	}
	else {
		localvars::add("formList",$formList);
	}
	$childList = listGenerator::generateChildList($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("childrenList", is_empty($childList) ? '<div class="alert alert-warning"> No children available </div>' : $childList);
	// Children Tab Stuff
	//////////

}
catch (Exception $e) {
	log::insert("Data View: Object: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

localvars::add("leftnav",navigation::buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));
localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>View Object</h1>
	</header>

		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			{local var="results"}

			<?php if ($permissions === TRUE) { ?>


			<div class="btn-group btn-group-justified" role="group">
				<div class="btn-group" role="group">
					<a data-toggle="tab" href="#metadata" class="btn btn-primary">Metadata</a>
				</div>
				<div class="btn-group" role="group">
					<a data-toggle="tab" href="#files" id="filesTab" class="btn btn-primary">Files</a>
				</div>
				<div class="btn-group" role="group">
					<a data-toggle="tab" href="#project" class="btn btn-primary">Project</a>
				</div>
				<div class="btn-group" role="group">
					<a data-toggle="tab" href="#children" class="btn btn-primary">Children</a>
				</div>
			</div>

			<div class="tab-content">
				<div class="tab-pane" id="metadata">
					{local var="form"}
				</div>
				<div class="tab-pane" id="files">
					{local var="filesViewer"}
				</div>

				<div class="tab-pane" id="project">
					<h2>Change Project Membership</h2>
					{local var="projectOptions"}
				</div>

				<div class="tab-pane" id="children">
					{local var="childrenList"}
				</div>
			</div>
		</div>
			<?php } // Permissions ?>
</section>

<?php
$engine->eTemplate("include","footer");
?>