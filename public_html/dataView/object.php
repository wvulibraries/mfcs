<?php
include("../header.php");

$permissions      = TRUE;

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
//	if ($filesViewer === FALSE) {
//		throw new Exception("Error building files view.");
//	}

	localvars::add("filesViewer",$filesViewer);
	// Metadata Tab Stuff
	//////////

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
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
	localVars::add("childrenList", is_empty($childList) ? 'No children available' : $childList);
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

	<div class="container-fluid">
		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<?php if ($permissions === TRUE) { ?>

			<div class="row-fluid">
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<li><a data-toggle="tab" href="#files" id="filesTab">Files</a></li>
					<li><a data-toggle="tab" href="#project">Project</a></li>
					<li><a data-toggle="tab" href="#children">Children</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}
					</div>
					<div class="tab-pane" id="files">
						{local var="filesViewer"}
					</div>

					<div class="tab-pane" id="project">
						<h2>Change Project Membership</h2>

						<form action="{phpself query="true"}" method="post">
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
						</form>
					</div>

					<div class="tab-pane" id="children">
						{local var="childrenList"}
					</div>
				</div>
			</div>
			<?php } // Permissions ?>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(function() {
		// Show first tab on page load
		$(".nav-tabs a:first").tab("show");

		// Disable form input fields
		$(":input").not('.btn,.close').prop("disabled",true);

		// Remove form submits
		$(":input[type=submit]").remove();

		// Remove file upload boxed
		$('.fineUploader').remove();

		// Remove form actions
		$("form").removeAttr("action");
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>