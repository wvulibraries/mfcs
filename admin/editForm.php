<?php
include("header.php");

$errorMsg = NULL;
$engine->localVars("requireIdentifier",FALSE);

$sql = sprintf("SELECT formType, parentForm FROM %s WHERE projectID='%s' AND formName='%s' LIMIT 1",
	$engine->openDB->escape($engine->dbTables("forms")),
	$engine->openDB->escape($engine->localVars("projectID")),
	$engine->openDB->escape($engine->localVars("formName"))
	);
$engine->openDB->sanitize = FALSE;
$sqlResult                = $engine->openDB->query($sql);
$row                      = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

if ($row['formType'] == 'record' && $row['parentForm'] == '0') {
	$engine->localVars("requireIdentifier",TRUE);
}


// Form Submission
if (isset($engine->cleanPost['MYSQL']['createFormSubmit'])) {

	recurseInsert("includes/editForm_submit.php","php");

}
// Form Submission

$engine->eTemplate("include","header");
?>

<script type="text/javascript" src="{local var="siteRoot"}includes/editForm_functions.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/editForm_dynamic.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/editForm_validate.js"></script>

<h1><?= htmlSanitize($engine->localVars("formLabel")) ?> Form</h1>

<div id="draggableFormElementsContainer">
	<strong>Drag to create a new element:</strong>
	<ul id="draggableFormElements">
		<li>Identifier</li>
		<?php
		if ($row['formType'] == 'metadata') {
			print '<li>Release to Public</li>';
		}
		?>
		<li>Link</li>
		<li>Text</li>
		<li>Select</li>
		<li>Multiselect</li>
		<li>Textarea</li>
		<li>Date</li>
		<li>WYSIWYG</li>
	</ul>
</div>

<?php
if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}
?>

<form method="post" id="createForm">

 	<ul id="mainList">
	<?php
	if (!isnull($engine->localVars("formName"))) {
		$i = 0;

		$sql = sprintf("SELECT formFields.ID, formFields.type FROM %s AS formFields LEFT JOIN %s AS forms ON forms.ID=formFields.formID WHERE forms.projectID='%s' AND forms.formName='%s' ORDER BY formFields.position",
			$engine->openDB->escape($engine->dbTables("formFields")),
			$engine->openDB->escape($engine->dbTables("forms")),
			$engine->openDB->escape($engine->localVars("projectID")),
			$engine->openDB->escape($engine->localVars("formName"))
			);
		$engine->openDB->sanitize = FALSE;
		$sqlResult                = $engine->openDB->query($sql);
		
		if ($sqlResult['result']) {
			while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {

				print '<li>'.editFormItem($i++,$row['type'],$row['ID']).'</li>';

			}
		}
	}
	?>
	</ul>

	{engine name="insertCSRF"}
	<input type="hidden" name="requireIdentifier" value="<?= htmlSanitize($engine->localVars("requireIdentifier")) ?>" />
	<input type="hidden" name="form" value="<?= htmlSanitize($engine->localVars("formName")) ?>" />
	<input type="submit" name="createFormSubmit" value="Submit" disabled />
</form>

<script type="text/javascript">
	$(document).ready(init);
</script>

<?php
$engine->eTemplate("include","footer");
?>