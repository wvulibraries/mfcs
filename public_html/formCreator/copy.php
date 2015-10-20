<?php
include("../header.php");

log::insert("Form Creator: Copy: View");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

try {
	// Get list of forms for choices dropdown
	if (($objectForms = forms::getObjectForms()) === FALSE) {
		throw new Exception("Errer retreiving metadata forms");
	}

	if (is_array($objectForms)) {
		$tmp = '';
		foreach ($objectForms as $form) {
			$tmp .= sprintf('<option value="%s">%s</option>',
				$form['ID'],
				$form['title']
				);
		}
		localVars::add("formsOptions",$tmp);
		unset($tmp);
	}

	if (isset(mfcs::$engine->cleanPost['MYSQL']['submitCopy'])) {
		if (!isset(mfcs::$engine->cleanPost['MYSQL']['newTitle']) || is_empty(mfcs::$engine->cleanPost['MYSQL']['newTitle'])) {
			throw new Exception("New Form Title is required.");
		}

		// Get all fields from the forms table except the primary key
		$fields = mfcs::$engine->openDB->listFields("forms",FALSE);

		// Remove unique field
		foreach ($fields as $I => $field) {
			if ($field == 'title') {
				unset($fields[$I]);
			}
		}

		mfcs::$engine->openDB->transBegin();

		log::insert("Form Creator: Copy: ",0,mfcs::$engine->cleanPost['MYSQL']['formSelect'],mfcs::$engine->cleanPost['MYSQL']['newTitle']);

		$sql = sprintf("INSERT INTO `forms` (`title`,`%s`) (SELECT '%s',`%s` FROM `forms` WHERE `ID`='%s' LIMIT 1)",
			implode('`,`', $fields),
			mfcs::$engine->cleanPost['MYSQL']['newTitle'],
			implode('`,`', $fields),
			mfcs::$engine->cleanPost['MYSQL']['formSelect']
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError("Error copying form - ".$sqlResult['error'],errorHandle::DEBUG);
			throw new Exception("Error copying form");
		}

		errorHandle::successMsg("Form copied successfully.");

		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();
	}
}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());

	mfcs::$engine->openDB->transRollback();
	mfcs::$engine->openDB->transEnd();
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Copy a Form</h1>
	</header>
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/formCreator/copy.php">Copy Form</a></li>
		</ul>
	</nav>

	{local var="results"}

	<form method="post" class="form-horizontal">
		<div class="control-group">
			<label class="control-label" for="formSelect">Source Form</label>
			<div class="controls">
				<select name="formSelect" id="formSelect">
					{local var="formsOptions"}
				</select>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="newTitle">New Form Title</label>
			<div class="controls">
				<input type="text" name="newTitle" id="newTitle" required focus>
			</div>
		</div>

		<input type="submit" class="btn btn-primary" name="submitCopy" value="Copy Form">
		{engine name="csrf"}
	</form>
</section>


<?php
$engine->eTemplate("include","footer");
?>
