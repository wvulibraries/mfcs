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

	if (($validate_return = valid::validate(array("metedata"=>false,"authtype"=>"editor","productionReady"=>true))) !== TRUE) {
		$permissions = FALSE;
		throw new Exception($validate_return);
	}

	if (($form = forms::get($engine->cleanGet['MYSQL']['formID'])) === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	/* Parent Object 'Stuff' */
	if (isset($engine->cleanGet['MYSQL']['parentID']) && ($parentObject = objects::get($engine->cleanGet['MYSQL']['parentID'])) === FALSE ) {
		throw new Exception("Unable to retrieve parent object");
	}
	/* End Parent Object 'Stuff' */

	// do we have a previous successful submission that needs displayed?
	$previous_submission = sessionGet("mfcs_previous_submission_result");
	if (!is_empty($previous_submission)) {
		errorHandle::successMsg($previous_submission);
	}
	sessionDelete("mfcs_previous_submission_result");

	// Editor information
	if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {
		revisions::history_created($engine->cleanGet['MYSQL']['objectID']);
		revisions::history_last_modified($engine->cleanGet['MYSQL']['objectID']);
	}

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects,$engine->cleanGet['MYSQL']['formID']));
	// Project Tab Stuff
	//////////

	localvars::add("formName",$form['title']);
	localvars::add("formID",$form['ID']);

	// handle submissions
	if (isset($engine->cleanPost['MYSQL']['checksum_submit'])) {
		log::insert("Data Entry: Object: Checksum Submission",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);
		if (($uploaded_checksums = checksum::parse_uploaded_checksums($_FILES["checksum"]['tmp_name'])) === FALSE) {
			throw new Exception("checksum file is not valid.");
		}
		if (checksum::apply_checksum_to_files($engine->cleanGet['MYSQL']['objectID'],$uploaded_checksums) === FALSE) {
			throw new Exception("Error importing checksums");
		}
	}

	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID']) === FALSE) {
			throw new Exception("Error Submitting Form.");
		}
		http::setGet("objectID",localvars::get("newObjectID"));

		log::insert("Data Entry: Object: Successful Submission",localvars::get("newObjectID"),$form['ID']);

		sessionset("mfcs_previous_submission_result","Object Created Successfully.");
		header(sprintf("Location: %sdataEntry/object.php?objectID=%s",localvars::get("siteRoot"),localvars::get("newObjectID")));
		die();

	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		if (forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']) === FALSE) {
			throw new Exception("Error Updating Form.");
		}

		log::insert("Data Entry: Object: Successful update",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);

		sessionset("mfcs_previous_submission_result","Object Updated Successfully.");
		objects::unlock($engine->cleanGet['MYSQL']['objectID']);
		header(sprintf("Location: %sdataEntry/object.php?objectID=%s",localvars::get("siteRoot"),$engine->cleanGet['MYSQL']['objectID']));
		die();
	}
	else if (isset($engine->cleanPost['MYSQL']['projectForm'])) {

		$engine->cleanPost['MYSQL']['projects'] = (isset($engine->cleanPost['MYSQL']['projects']))?$engine->cleanPost['MYSQL']['projects']:array();

		// Add All the new ones
		if (objects::addProjects($engine->cleanPost['MYSQL']['projects_objectID'],$engine->cleanPost['MYSQL']['projects']) === FALSE) {
			throw new Exception("Error adding projects to Object.");
		}
		else {
			errorHandle::successMsg("Object Projects updated successfully.");
		}

		log::insert("Data Entry: Object: Successful Project Update",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);

	}

	// If locked, warn, allow to steal
	// We only need to check for locks on edits, not new objects
	if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {
		localvars::add('objectID', $engine->cleanGet['MYSQL']['objectID']);
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

	// build the items for Public Urls
	// @TODO; there are too many redundant isnull checks. can they be consolidated?
	if(!isnull($engine->cleanGet['MYSQL']['objectID'])) {
    $oai_url = sprintf("%s?verb=GetRecord&identifier=%s&metadataPrefix=oai_dc", mfcs::config("oai_url"), objects::getIDNOForObjectID($engine->cleanGet['MYSQL']['objectID']));
    $local_urls = sprintf('<li><a href="%1$s">%1$s</a></li>',objects::getUrl($engine->cleanGet['MYSQL']['objectID']));
    $local_urls .= sprintf('<li><a href="%s">OAI</a>', $oai_url);
		localvars::add('publicUrls',$local_urls);

    $oai_output = file_get_contents($oai_url);
    if ($oai_output) {
      $doc = new DomDocument('1.0');
      $doc->preserveWhiteSpace = false;
      $doc->formatOutput = true;
      $doc->loadXML($oai_output);
      $oai_output = htmlSanitize($doc->saveXML());
    } else {
      $oai_output = "OAI Record not available in OAI Application";
    }
    localvars::add('oaiOutput', $oai_output);
	}

	// build the files list for displaying
	if(isset($engine->cleanGet['MYSQL']['objectID'])){

		if (($filesViewer = files::buildFilesPreview($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
			throw new Exception("Error building files preview.");
		}

		localvars::add("filesViewer",$filesViewer);
		localvars::add("objectID", $engine->cleanGet['MYSQL']['objectID']);

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
		//////////
		// Project Tab Stuff
		$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
		localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects,$engine->cleanGet['MYSQL']['formID']));
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

log::insert("Data Entry: Object: View Page",$engine->cleanGet['MYSQL']['objectID'],$form['ID']);

$engine->eTemplate("include","header");
?>

{local var="projectWarning"}

<section>
	<header class="page-header">
		<h1>{local var="actionHeader"} Object - {local var="formName"}</h1>
		{local var="parentHeader"}
	</header>


	<ul class="breadcrumbs">
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
		<li class="pull-right noDivider"><a href="/data/object/history/?objectID={local var="objectID"}">History</a></li>
		<li class="pull-right noDivider"><a href="/data/object/reprocess/?objectID={local var="objectID"}">Reprocess Object</a></li>
		<?php if ($locked === TRUE) { ?>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Object-Locking" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
		<?php } else { ?>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Entering-Metadata" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
		<?php } ?>

	</ul>

	<div class="container-fluid">
		<div class="span3">
			<h2> Object Navigation </h2>
			<ul class="objectNav">
				{local var="leftnav"}
			</ul>
		</div>

		<div class="span9">
			<h2> Data Entry </h2>
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<?php if ($permissions === TRUE && $locked === FALSE) { ?>

			<div>
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
						<li><a data-toggle="tab" href="#files" id="filesTab">Files</a></li>
						<li><a data-toggle="tab" href="#project">Project</a></li>
						<li><a data-toggle="tab" href="#publicUrls">Public Urls &amp; OAI</a></li>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<!-- <li><a data-toggle="tab" href="#children">Children</a></li> -->
						<?php } ?>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}

						<?php if (!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>
							<p><b>Created by:</b> <em>{local var="createdByUsername"} on {local var="createdOnDate"}</em></p>
							<p><b>Modified by:</b> <em>{local var="modifiedByUsername"} on {local var="modifiedOnDate"}</em></p>
						<?php } ?>
					</div>

					<?php if(!isnull($engine->cleanGet['MYSQL']['objectID'])) { ?>

						<div class="tab-pane" id="files">
							<a href="/dataView/allfiles.php?objectID={local var="objectID"}">Download All Files (Zip)</a><br />
							<br /><br />
							{local var="filesViewer"}
							<br /><br />
							<form action="{phpself query="true"}" method="post" enctype='multipart/form-data'>
								{engine name="csrf"}
								<input type="hidden" name="lockID" value="{local var="lockID"}" />
								<label for="checksum_upload">Upload checksums file.</label>
								<input type="file" name="checksum" id="checksum_upload">
								<input type="submit" name="checksum_submit" />
							</form>
						</div>
						<div class="tab-pane" id="project">
							<h2>Change Project Membership</h2>

							<form action="{phpself query="true"}" method="post">
								<input type="hidden" name="lockID" value="{local var="lockID"}" />
								<input type="hidden" name="projects_objectID" value="{local var="objectID"}" ?>
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
							</form>
						</div>
						<div class="tab-pane" id="publicUrls">
							<h2>Public Urls &amp; OAI</h2>
              <p>Note: If the public URL has not been registered with MFCS yet, the first item may be blank.</p>
							<ul>
								{local var="publicUrls"}
							</ul>
              <p>OAI Record:</p>
              <pre>
                {local var="oaiOutput"}
              </pre>
						</div>
						<?php if(forms::isContainer($engine->cleanGet['MYSQL']['formID'])) { ?>
							<!-- <div class="tab-pane" id="children">

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
								</div> -->

								<!-- <section>
									<header>
										<h1>Children</h1>
									</header>

									{local var="childrenList"}
								</section> -->
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

<!-- Modal Preview -->
<div class="modal imagePreviewModal" id="modal" tabindex="-1" role="dialog" aria-labelledby="preview modal" aria-hidden="true">
	<div class="modalContainer">
	    <div class="modal-header">
	        <button type="button" class="close" aria-hidden="true">×</button>
	        <h3>File Preview</h3>
	    </div>
			<div class="modal-body">
	 		<div class="video-container">
	     		<iframe src="" frameborder="0" id="iFrameTarget"></iframe>
	     	</div>
	  	</div>
	    <div class="modal-footer">
	        <button class="btn close" aria-hidden="true">Close</button>
	    </div>
	</div>
</div>

<!-- @TODO : scripts should be moved out of this file -->
<script type="text/javascript">
inForm = false;
$("form").submit(function(e){inForm = true;});
$(window).on("beforeunload", function() {
	if (!inForm) {
		$.ajax({
			url: "/includes/ajax/unlock.php?lockID={local var="lockID"}",
			dataType: "json",
			success: function(responseData) {
			},
			error: function(jqXHR,error,exception) {
			},
			async:   true
		});
	}
});
</script>

<?php
$engine->eTemplate("include","footer");
?>
