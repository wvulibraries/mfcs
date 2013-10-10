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


	###############################################################################################################

	// Catch a form submition (which would be a revision being reverted to)
	if(isset($engine->cleanPost['MYSQL']['revisionID'])){

		// @TODO this should use revert2Revision() method instead of this ... 

		$revisionID = $revisions->getRevisionID($engine->cleanGet['MYSQL']['objectID'], $engine->cleanPost['MYSQL']['revisionID']);
	
		if (($revision = $revisions->getMetadataForID($revisionID)) === FALSE) {
			throw new Exception('Could not load revision.');
		} 

		if (objects::update($engine->cleanGet['MYSQL']['objectID'],$revision['formID'],(decodeFields($revision['data'])),$revision['metadata'],$revision['parentID']) !== FALSE) {
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

	localvars::add("formName", $form['title']);
	localvars::add("objectID", $objectID);
	localvars::add("currentVersion", revisions::generateFieldDisplay($object, $fields));

}catch(Exception $e){
	errorHandle::newError($e->getMessage(), errorHandle::DEBUG);
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<form id="revisionForm" action="" method="post">
	{engine name="csrf"}
	<input type="hidden" name="revisionID" id="revisionID" value="">
</form>


<header class="page-header">
	<h1>{local var="formName"}</h1>
</header>

<div id="left">
	{local var="leftnav"}
</div>

<div class="row-fluid" id="results">
	{local var="results"}
</div>

<div id="objectComparator">
	<section class="revisionSection" id="current">
	<header>Current Version:</header>
	{local var="currentVersion"}
	</section>
	<section class="revisionSection" id="revisions">
		<header>
			Past Version:
			<div>
				<select id="revisionSelector">
					<option>Select a revision</option>
					<?php
					foreach($revisions->getSecondaryIDs($engine->cleanGet['MYSQL']['objectID'], 'DESC') as $revisionID){
						printf('<option value="%s">%s</option>', $revisionID, date('D, M d, Y - h:i a', $revisionID));
					}
					?>
				</select>
				<input id="revertBtn" type="button" value="Revert">
			</div>
		</header>
		<div id="revisionViewer"></div>
	</section>
</div>

<script src="{local var="siteRoot"}includes/js/revisions.js" type="text/javascript" id="revisionsScript" data-objectid="{local var="objectID"}"></script>

<?php
$engine->eTemplate("include","footer");
?>
