<?
include("header.php");
?>

<!-- Page Content Goes Above This Line -->

<?
$engine->localVars("listTable",$engine->dbTables("validationTypes"));


function listFields() {
	
	global $engine;

	$listObj = new listManagement($engine,$engine->localVars("listTable"));
	
	$options = array();
	$options['field'] = "name";
	$options['label'] = "Name";
	$options['size']  = "20";
	$listObj->addField($options);
	unset($options);

	return $listObj;

}


$listObj = listFields();

// Form Submission
if(isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_submit'])) {
	
	$errorMsg .= $listObj->insert();

}
else if (isset($engine->cleanPost['MYSQL'][$engine->localVars("listTable").'_update'])) {
	
	$errorMsg .= $listObj->update();
	
}
// Form Submission
?>

<h2>Edit Validation Types</h2>

<?
if (!is_empty($errorMsg)) {
	print $errorMsg."<hr />";
}
?>

<h3>New Validation Type</h3>
<? $listObj = listFields(); ?>
<?= $listObj->displayInsertForm(); ?>

<hr />

<h3>Edit Validation Types</h3>
<? $listObj = listFields(); ?>
<?= $listObj->displayEditTable(); ?>

<!-- Page Content Goes Above This Line -->

<?php
$engine->eTemplate("include","footer");
?>

