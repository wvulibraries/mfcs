<?php
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

// Setup revision control
$revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');

$selectedProjects = NULL;
$parentObject     = NULL;
$permissions      = TRUE;
$locked           = FALSE;

try {

	$error = FALSE;

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (mfcsPerms::isEditor($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (isset($engine->cleanGet['MYSQL']['parentID']) && objects::validID(TRUE,$engine->cleanGet['MYSQL']['parentID']) === FALSE) {
		throw new Exception("ParentID Provided is invalid.");
	}

	// if an object ID is provided make sure the object is from this form
	if (!isnull($engine->cleanGet['MYSQL']['objectID']) && !objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("Object not from this form.");
	}

	if (($form = forms::get($engine->cleanGet['MYSQL']['formID'])) === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (forms::isProductionReady($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Form is not production ready.");
	}

	if (forms::isMetadataForm($engine->cleanGet['MYSQL']['formID'])) {
		throw new Exception("Metadata form provided (Object forms only).");
	}

	/* Parent Object 'Stuff' */
	if (isset($engine->cleanGet['MYSQL']['parentID']) && ($parentObject = objects::get($engine->cleanGet['MYSQL']['parentID'])) === FALSE ) {
		throw new Exception("Unable to retrieve parent object");
	}
	/* End Parent Object 'Stuff' */

	// Editor information
	if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {
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
	}

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
	// Project Tab Stuff
	//////////

	localvars::add("formName",$form['title']);
	localvars::add("formID",$form['ID']);

	log::insert("Data Entry: Object: View Page",0,$form['ID']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID']) === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
		http::setGet("objectID",localvars::get("newObjectID"));

		log::insert("Data Entry: Object: Successful Submission",localvars::get("newObjectID"),$form['ID']);

	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']) === FALSE) {
			throw new Exception("Error Updating Form.");
		}

		log::insert("Data Entry: Object: Successful update",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);
	}
	else if (isset($engine->cleanPost['MYSQL']['projectForm'])) {

		$engine->cleanPost['MYSQL']['projects'] = (isset($engine->cleanPost['MYSQL']['projects']))?$engine->cleanPost['MYSQL']['projects']:array();

		// Add All the new ones
		if (objects::addProjects($engine->cleanGet['MYSQL']['objectID'],$engine->cleanPost['MYSQL']['projects']) === FALSE) {
			throw new Exception("Error adding projects to Object.");
		}

		log::insert("Data Entry: Object: Successful Project Update",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);

	}

	// If locked, warn, allow to steal
	// We only need to check for locks on edits, not new objects
	if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {

		if (isset($engine->cleanGet['MYSQL']['unlock']) && $engine->cleanGet['MYSQL']['unlock'] == "unlock") {
			log::insert("Data Entry: Object: Unlocked Object",$engine->cleanGet['MYSQL']['objectID']);
			objects::unlock($engine->cleanGet['MYSQL']['objectID']);
		}
		else if (isset($engine->cleanGet['MYSQL']['unlock']) && $engine->cleanGet['MYSQL']['unlock'] == "cancel") {
			log::insert("Data Entry: Object: Unlocked Object",$engine->cleanGet['MYSQL']['objectID']);
			objects::unlock($engine->cleanGet['MYSQL']['objectID']);
			header("Location: /");
			die();
		}

		// If the object is locked and it is the lock ID that is set in Localvars, we assume that the form was just submitted and we are redisplaying. 
		if (objects::is_locked($engine->cleanGet['MYSQL']['objectID']) && objects::is_locked($engine->cleanGet['MYSQL']['objectID']) == localvars::get("lockID")) {
			$locked = FALSE;
		}
		// We are editing it ... lock it
		else if (objects::is_locked($engine->cleanGet['MYSQL']['objectID'])) {
			$locked = TRUE;
		}
		// If not locked, lock it.
		else if ($locked === FALSE && !(objects::lock($engine->cleanGet['MYSQL']['objectID']))) {
			throw new Exception("Unable to lock Object");
		}
	}

	// build the files list for displaying
	if(isset($engine->cleanGet['MYSQL']['objectID'])){

		if (($filesViewer = files::buildFilesPreview($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
			throw new Exception("Error building files preview.");
		}
		localvars::add("filesViewer",$filesViewer);

		//////////
		// Children Tab Stuff
		if (($formList = listGenerator::generateFormSelectList($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
			errorHandle::errorMsg("Error getting Forms Listing");
			throw new Exception('Error');
		}
		else {
			localvars::add("formList",$formList);
		}
		localVars::add("childrenList",listGenerator::generateChildList($engine->cleanGet['MYSQL']['objectID']));
		// Children Tab Stuff
		//////////
	}

}
catch(Exception $e) {
	log::insert("Data Entry: Object: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
	$error = TRUE;
}

// build the form for displaying
if (forms::validID()) {
	try {
		if (($builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'],$error)) === FALSE) {
			throw new Exception("Error building form.");
		}

		localvars::add("form",$builtForm);
		localvars::add("leftnav",navigation::buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));

		localvars::add("objectID",$engine->cleanGet['MYSQL']['objectID']);

		//////////
		// Project Tab Stuff
		$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
		localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
		// Project Tab Stuff
		//////////
	}
	catch (Exception $e) {
		log::insert("Data Entry: Object: Error",$engine->cleanGet['MYSQL']['objectID'],$engine->cleanGet['MYSQL']['formID'],$e->getMessage());
		errorHandle::errorMsg($e->getMessage());
	}
}

localVars::add("results",displayMessages());

// Display warning if form is not part of current project
forms::checkFormInCurrentProjects($engine->cleanGet['MYSQL']['formID']);

localvars::add("actionHeader",(isnull($engine->cleanGet['MYSQL']['objectID']))?"Add":"Edit");
localvars::add("parentHeader",(isnull($parentObject))?"":"<h2>Adding Child to Parent '".$parentObject['data'][$form['objectTitleField']]."'</h2>");

$engine->eTemplate("include","header");
?>

{local var="projectWarning"}

<section>
	<header class="page-header">
		<h1>{local var="actionHeader"} Object - {local var="formName"}</h1>
		{local var="parentHeader"}
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
			<!-- FLoat Right -->
			<?php if(mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID'])){ ?>
			<li class="pull-right noDivider"><a href="{local var="siteRoot"}formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
			<?php
			}
			if (!isnull($engine->cleanGet['MYSQL']['objectID']) and $revisions->hasRevisions($engine->cleanGet['MYSQL']['objectID'])) { ?>
				<li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/revisions/index.php?objectID={local var="objectID"}">Revisions</a></li>
			<?php } ?>
			<li class="pull-right noDivider"><a href="{phpself query="true"}&unlock=cancel">Cancel Edit &amp; Unlock object</a></li>
		</ul>
	</nav>

	<div class="container-fluid">
		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<?php if ($permissions === TRUE && $locked === FALSE) { ?>

			<div class="row-fluid">
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
						<li><a data-toggle="tab" href="#files" id="filesTab">Files</a></li>
						<li><a data-toggle="tab" href="#project">Project</a></li>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<li><a data-toggle="tab" href="#children">Children</a></li>
						<?php } ?>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}

						<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
							<p>Created by: {local var="createdByUsername"} on {local var="createdOnDate"}</p>
							<p>Modified by: {local var="modifiedByUsername"} on {local var="modifiedOnDate"}</p>
						<?php } ?>
					</div>

					<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
						<div class="tab-pane" id="files">
							<a href="/dataView/allfiles.php?objectID={local var="objectID"}">Download All Files (Zip)</a><br />
							<!-- <a href="/dataView/allfiles.php?id=$engine->cleanGet['MYSQL']['objectID']&amp;type=tar">Download All Files (tar)</a> -->
							<br /><br />
							{local var="filesViewer"}
						</div>
						<div class="tab-pane" id="project">
							<h2>Change Project Membership</h2>

							<form action="{phpself query="true"}" method="post">
								<input type="hidden" name="lockID" value="{local var="lockID"}" />
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
							</form>
						</div>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<div class="tab-pane" id="children">

								<div class="accordion" id="accordion2">
									<div class="accordion-group">
										<div class="accordion-heading">
											<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
												Add a Child Object
											</a>
										</div>
										<div id="collapseOne" class="accordion-body collapse">
											<div class="accordion-inner">
												Select a Form:

												{local var="formList"}
											</div>
										</div>
									</div>
								</div>

								<section>
									<header>
										<h1>Children</h1>
									</header>

									{local var="childrenList"}
								</section>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
			<?php } else if ($locked === TRUE) { // permissions && Locked?>

			<h1>Object is locked by another User</h1>

			<p>Form Name: {local var="formName"}</p>
			<p>Object IDNO: {local var="objectIDNO"}</p>
			<p>Locked by: {local var="lockUsername"}</p>
			<p>Locked On: {local var="lockDate"}</p>

			<p>
				This object is locked for editing. If you are the user that locked this file it is possible that you have
			 	it open in another browser window, or closed the browser window where you were previously working on this 
			 	object without clearing your lock.
			</p>

			<p>
				If another user is listed as the locking user, please check with them before unlocking this object to edit it. 
			</p>

			<p>
				If you unlock this object the previous opened version will not be able to be submitted.
			</p>

			<a href="{phpself query="true"}&unlock=unlock">Unlock this object</a>

			<?php }?>

		</div>
	</div>
</section>

<!-- @TODO : scripts should be moved out of this file -->
<script type="text/javascript">
	$(function() {
		// Show first tab on page load
		$(".nav-tabs a:first").tab("show");

		var $objectSubmitBtn = $('#objectSubmitBtn');
		$objectSubmitBtn.closest('form').submit(function(){
			var $objectSubmitProcessing = $('#objectSubmitProcessing');
			if($objectSubmitProcessing.length){
				$objectSubmitBtn.hide();
				$objectSubmitProcessing.show();
			}
		});
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>
