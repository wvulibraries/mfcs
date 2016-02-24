<?php
include("../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(3)){
	header('Location: /index.php?permissionFalse');
}

$permissions = TRUE;
$confirmed   = FALSE;

try {

	$forms = forms::getObjectForms();

	$formList = '<option value="NULL">-- Select a Form --</option>';
	foreach ($forms as $form) {
		$formList .= sprintf('<option value="%s">%s</option>',
			$form['ID'],
			$form['title']
			);
	}

	localvars::add("formList",$formList);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['reprocess'])) {

		if (!validate::integer($engine->cleanPost['MYSQL']['formList'])) {
			throw new Exception("No form Selected");
		}

		// build date clause
		if (!isempty($engine->cleanPost['MYSQL']['startDate']) && isempty($engine->cleanPost['MYSQL']['endDate'])) {
			// start provided, but no end
			$date_clause = sprintf("AND `createTime` >= '%s'",
				strtotime($engine->cleanPost['MYSQL']['startDate'])
				);
		}
		else if (isempty($engine->cleanPost['MYSQL']['startDate']) && !isempty($engine->cleanPost['MYSQL']['endDate'])) {
			// end provided, but no start
			$date_clause = sprintf("AND `createTime` <= '%s'",
				strtotime($engine->cleanPost['MYSQL']['endDate'])
				);
		}
		else if (!isempty($engine->cleanPost['MYSQL']['startDate']) && !isempty($engine->cleanPost['MYSQL']['endDate'])) {
			// both start and end provided
			$date_clause = sprintf("AND `createTime` >= '%s' AND `createTime` <= '%s'",
				strtotime($engine->cleanPost['MYSQL']['startDate']),
				strtotime($engine->cleanPost['MYSQL']['endDate'])
				);
		}
		else {
			$date_clause = "";
		}

		$sql = sprintf("SELECT * FROM `objects` WHERE `formID`='%s' %s",
			$engine->cleanPost['MYSQL']['formList'],
			$date_clause
			);

		$objects = objects::getObjectsForSQL($sql);

		log::insert("Object: Insert Object for Reprocessing",NULL,$engine->cleanPost['MYSQL']['formList'],$date_clause);

		// start transactions
		if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
			throw new Exception("unable to start database transactions");
		}

		foreach ($objects as $object) {

			// we are grabbing the fields for each object, in case we ever want to offer project reprocessing
			$fields = forms::get_file_fields($object['formID']);

			foreach ($fields as $field) {
				if (files::insert_into_processing_table($object['ID'],$field['name']) === FALSE) {
					mfcs::$engine->openDB->transRollback();
					mfcs::$engine->openDB->transEnd();
					throw new Exception("Error inserting for reprocessing.");
				}
			}

		}

		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		errorHandle::successMsg("Objects Inserted for Reprocessing");

		$confirmed = TRUE;
	}

}
catch(Exception $e) {
	log::insert("Data Entry: Metadata: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

log::insert("Admin: View Object Reprocess page");

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Reprocess Object</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}admin/index.php">Admin Home</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Reprocessing" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>


	{local var="results"}

		<?php if ($permissions === TRUE && $confirmed === FALSE) { ?>

		<form action="{phpself query="false"}" method="post">
			{engine name="csrf"}

			<div class="">

			</div>

			<div class="searchBlock form-group well">
				<h3>Select a form to Reprocess</h3>
				<select name="formList" id="searchFormSelect">
					{local var="formList"}
				</select>
			</div>

			<div class="dates form-group well">
				<h3> Filter By Dates </h3>
				<div class="start">
					<label for="startDate">Start Date</label>
					<input type="date" name="startDate" />
				</div>
				<div class="end">
					<label for="endDate">End Date</label>
					<input type="date" name="endDate" />
				</div>
			</div>

			<div class="search">
				<input type="submit" name="reprocess" value="Reprocess" />
			</div>


		</form>

		<?php } ?>



</section>


<?php
	$engine->eTemplate("include","footer");
?>
