<?php
include("../../header.php");

// Setup revision control
$revisions = revisions::create();

###############################################################################################################

try{
	if(	!isset($engine->cleanGet['MYSQL']['objectID']) ||
		!validate::integer($engine->cleanGet['MYSQL']['objectID'])){
		throw new Exception('No Object ID Provided.');
	}

	$objectID = $engine->cleanGet['MYSQL']['objectID'];
	$object   = objects::get($objectID);
	$form     = forms::get($object['formID']);
	$fields   = $form['fields'];
	if(mfcsPerms::isEditor($form['ID']) === FALSE) throw new Exception("Permission Denied to view objects created with this form.");

	log::insert("Data Entry: Revision: View Page",$objectID);

	###############################################################################################################

	// Catch a form submition (which would be a revision being reverted to)
	if(isset($engine->cleanPost['MYSQL']['revisionID'])){

		log::insert("Data Entry: Revision: Revert",$objectID);

		// @TODO this should use revert2Revision() method instead of this ...

		$revisionID = $revisions->getRevisionID($engine->cleanGet['MYSQL']['objectID'], $engine->cleanPost['MYSQL']['revisionID']);

		if (($revision = $revisions->getMetadataForID($revisionID)) === FALSE) {
			throw new Exception('Could not load revision.');
		}

		if (objects::update($engine->cleanGet['MYSQL']['objectID'],$revision['formID'],(decodeFields($revision['data'])),$revision['metadata'],$revision['parentID'], NULL, $revision['publicRelease']) !== FALSE) {
			// Reload the object - To refresh the data
			$object = objects::get($objectID,TRUE);
		}
		else {
			throw new Exception('Could not update object with revision.');
		}
	}

	###############################################################################################################

	// Is this just a revision AJAX request?
	if((isset($engine->cleanGet['MYSQL']['revisionID']))) {
		$revisionID = $revisions->getRevisionID($engine->cleanGet['MYSQL']['objectID'], $engine->cleanGet['MYSQL']['revisionID']);
		$revision   = $revisions->getMetadataForID($revisionID);

		if(!$revision){
			die('Error reading revision');
		}else{
			die(revisions::generateFieldDisplay($revision, $fields));
		}
	}

	###############################################################################################################

	// Build the select list
	$selectARevision = "";
	foreach($revisions->getSecondaryIDs($engine->cleanGet['MYSQL']['objectID'], 'DESC') as $revisionID){
		$selectARevision .= sprintf('<option value="%s">%s</option>',
			$revisionID,
			date('D, M d, Y - h:i a', $revisionID)
			);
	}

	localVars::add("selectARevision",$selectARevision);

	localvars::add("formName", $form['title']);
	localvars::add("objectID", $objectID);
	localvars::add("currentVersion", revisions::generateFieldDisplay($object, $fields));

}catch(Exception $e){

	log::insert("Data Entry: Revision: Caught Exception",0,0,$e->getMessage());

	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>
<section>
	<form id="revisionForm" action="" method="post">
		{engine name="csrf"}
		<input type="hidden" name="revisionID" id="revisionID" value="">
	</form>


	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/object.php?objectID={local var="objectID"}">Object Edit Page</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Revision-Control" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

	<div id="objectComparator">
		<div class="revisionSection" id="current">
			<div class="revisionHeader">
				<h2>Current Version:</h2>
			</div>
			<div id="#grabVersion" class="revisionBody">
				{local var="currentVersion"}
			</div>
		</div>
		<div class="revisionSection" id="revisions">
			<div class="revisionHeader">
				<h2> Past Version:</h2>
				<div class="revisionTool">
					<select id="revisionSelector">
						<option>Select a revision</option>
						{local var="selectARevision"}
					</select>
				</div>
			</div>
			<div class="revisionBody">
				<div id="revisionViewer"></div>
			</div>

			<div class="revert">
				<h2> Revert to this Revision? </h2>
				<input id="revertBtn" class="btn btn-primary" type="button" value="Revert">
			</div>
		</div>
	</div>
</section>

<div id="revisionsScript" data-objectid="{local var="objectID"}"></div>

<?php
$engine->eTemplate("include","footer");
?>
