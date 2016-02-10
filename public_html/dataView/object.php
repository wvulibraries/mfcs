<?php
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

$permissions      = TRUE;

try {

	if (($validate_return = valid::validate(array("metedata"=>false,"authtype"=>"viewer","productionReady"=>true))) !== TRUE) {
		$permissions = FALSE;
		throw new Exception($validate_return);
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

	// Editor information
	$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
	if (is_empty($object['createdBy'])) {
		localvars::add("createdByUsername","Unavailable");
	}
	else {
		$user   = users::get($object['createdBy']);
		localvars::add("createdByUsername",$user['username']);
	}

	localvars::add("createdOnDate",date('D, d M Y H:i',$object['createTime']));

	if (is_empty($object['modifiedBy'])) {
		localvars::add("modifiedByUsername","Unavailable");
	}
	else {
		$user   = users::get($object['modifiedBy']);
		localvars::add("modifiedByUsername",$user['username']);
	}

	localvars::add("modifiedOnDate",date('D, d M Y H:i',$object['modifiedTime']));

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
			<ul class="menu">
				{local var="leftnav"}
			</ul>
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
					<!-- <li><a data-toggle="tab" href="#children">Children</a></li> -->
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}


						<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
							<p>Created by: {local var="createdByUsername"} on {local var="createdOnDate"}</p>
							<p>Modified by: {local var="modifiedByUsername"} on {local var="modifiedOnDate"}</p>
						<?php } ?>
					</div>
					<div class="tab-pane" id="files">
						<a href="/dataView/allfiles.php?objectID={local var="objectID"}"  class="btn btn-primary">Download All Files (Zip)</a><br><br>
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

<!-- 					<div class="tab-pane" id="children">
						{local var="childrenList"}
					</div> -->
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