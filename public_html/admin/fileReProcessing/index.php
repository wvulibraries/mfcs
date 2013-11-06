<?php
include("../../header.php");

if (isset($engine->cleanPost['MYSQL']['submit'])) {
	try {
		$engine->openDB->transBegin();

		$selectedForms = isset($engine->cleanPost['MYSQL']['forms']) ? $engine->cleanPost['MYSQL']['forms'] : array();

		// Get all object forms
		foreach (forms::getObjectForms() as $form) {
			if (!mfcsPerms::isAdmin($form['ID'])) continue;

			// Get the projects associated with this form
			$formProjects = forms::getProjects($form['ID']);

			// Loop through each selected project
			if (isset($engine->cleanPost['MYSQL']['projects'])) {
				foreach ($engine->cleanPost['MYSQL']['projects'] as $project) {
					// Form must be associated with this project, and not already selected
					if (in_array($project, $formProjects) && !in_array($form['ID'], $selectedForms)) {
						// Only add forms that have file fields
						foreach ($form['fields'] as $field) {
							if ($field['type'] == 'file') {
								// Add this form to the list that needs added to the database
								$selectedForms[] = $form['ID'];
								break;
							}
						}
					}
				}
			}
		}

		// Get a list of all forms already queued
		$sql = sprintf("SELECT `formID` FROM `fileProcessQueue`");
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError("Failed to retrieve queued forms: ".$sqlResult['error'],errorHandle::DEBUG);
			throw new Exception("Failed to retrieve queued forms.");
		}

		// Remove forms that are already queued
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			foreach ($selectedForms as $I => $form) {
				if ($form == $row['formID']) {
					unset($selectedForms[$I]);
				}
			}
		}

		// If something is still checked, add it to the database
		if (count($selectedForms) > 0) {
			$sql = sprintf("INSERT INTO `fileProcessQueue` (`formID`) VALUES ('%s')",
				implode("'),('", $selectedForms)
				);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				errorHandle::newError("Failed to queue files for re-processing: ".$sqlResult['error'],errorHandle::DEBUG);
				throw new Exception("Failed to queue files for re-processing.");
			}
		}

		$engine->openDB->transCommit();
		$engine->openDB->transEnd();
		errorHandle::successMsg("Successfully queued files for re-processing.");
	}
	catch (Exception $e) {
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
		errorHandle::errorMsg($e->getMessage());
	}
}


localVars::add("projectList",projects::generateProjectCheckList());

$tmp = '<ul class="checkboxList">';
foreach (forms::getObjectForms() as $form) {
	if ($form === FALSE) continue;

	if (!mfcsPerms::isAdmin($form['ID'])) continue;

	// Only list forms with a file field
	foreach ($form['fields'] as $field) {
		if ($field['type'] == 'file') {
			$tmp .= sprintf('<li><label class="checkbox" for="%s"><input type="checkbox" id="%s" name="forms[]" value="%s"> %s</label></li>',
				htmlSanitize("form_".$form['ID']), // for=
				htmlSanitize("form_".$form['ID']), // id=
				htmlSanitize($form['ID']),         // value=
				forms::title($form['ID'])          // label text
				);
			break;
		}
	}

}
$tmp .= '</ul>';
localVars::add("formList",$tmp);
unset($tmp);

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Re-Process Files</h1>
	</header>

	{local var="results"}

	<form method="post">
		<section>
			<header>
				<h2>Projects</h2>
			</header>

			{local var="projectList"}
		</section>

		<hr />

		<section>
			<header>
				<h2>Forms</h2>
			</header>

			{local var="formList"}
		</section>

		{engine name="csrf"}
		<input type="submit" class="btn btn-primary" name="submit" value="Submit">
	</form>
</section>

<?php
$engine->eTemplate("include","footer");
?>
