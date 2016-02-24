<?php
include("../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

$permissions      = TRUE;

try {

	if (!isset($engine->cleanGet['MYSQL']['objectID']) && isset($engine->cleanPost['MYSQL']['objectID'])) {
		http::setGet("objectID",$engine->cleanPost['MYSQL']['objectID']);
	}

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (($object = objects::get($engine->cleanGet['MYSQL']['objectID'])) === FALSE) {
		throw new Exception("Error retrieving Object");
	}

	if (($form = forms::get($object['formID'])) === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	if (mfcsPerms::isAdmin($object['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (forms::isMetadataForm($object['formID']) === FALSE) {
		throw new Exception("Object provided (Only Metadata can be moved).");
	}

	// handle submission
	$return = NULL;
	if (isset($engine->cleanPost['MYSQL']['moveMetadata'])) {

	}

	if (($compatibleForms = forms::compatibleForms($form['ID'])) === FALSE) {
		throw new Exception("Error getting compatible forms");
	}

	$temp = '<option value="NULL">-- Select an Item --</option>';
	foreach ($compatibleForms as $cform) {
		$temp .= sprintf('<option value="%s">%s</option>',
			$cform['ID'],
			forms::title($cform['ID'])
			);
	}

	localvars::add("originalFormTitle",forms::title($form['ID']));
	localvars::add("compatibleForms",$temp);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['moveSubmit'])) {
		if (!isset($compatibleForms[$engine->cleanPost['MYSQL']['form']])) {
			throw new Exception("Selected form is not compatible with original form.");
		}

		// @TODO this logic shouldn't be here
		$sql       = sprintf("UPDATE `objects` SET `formID`='%s' WHERE `ID`='%s' AND `formID`='%s' LIMIT 1",
			$engine->cleanPost['MYSQL']['form'],
			$engine->openDB->escape($engine->cleanPost['MYSQL']['objectID']),
			$engine->openDB->escape($form['ID'])
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			throw new Exception("Error updating object record.");
		}

		if (($form = forms::get($engine->cleanPost['MYSQL']['form'])) === FALSE) {
			throw new Exception("Error retrieving form.");
		}

		log::insert("Data Entry: Move: Successful Move",$engine->cleanPost['MYSQL']['objectID'],$form['ID'],$engine->cleanPost['MYSQL']['form']);

		errorHandle::successMsg("Object Moved.");

		localvars::add("originalFormTitle",forms::title($form['ID']));
	}

}
catch(Exception $e) {
	log::insert("Data Entry: Move: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

log::insert("Data Entry: Move: Page View");

localVars::add("results",displayMessages());
$engine->eTemplate("include","header");
?>

{local var="projectWarning"}

<section>
	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/dataEntry/selectForm.php">Select a Form</a></li>
			<li class="pull-right"><a href="{local var="siteRoot"}/formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
			<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Subject-Headings" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
		</ul>
	</nav>

	{local var="results"}

	<?php if ($permissions === TRUE) { ?>

	<div class="row-fluid">

		<p>You are moving the selected metadata object from the '<strong>{local var="originalFormTitle"}</strong>' form to another. Only compatible forms are listed.</p>


		<form action="{phpself query="false"}" method="post">
			{engine name="csrf"}
			<input type="hidden" name="objectID" value="{queryString var="objectID"}" />

			<select name="form">
				{local var="compatibleForms"}
			</select>
			<br />
			<input type="submit" name="moveSubmit" value="Move Object" />
		</form>

	</div>

	<?php } ?>
</section>


<?php
	$engine->eTemplate("include","footer");
?>
