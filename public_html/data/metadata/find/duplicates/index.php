<?php
include("../../../../header.php");

try {

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	$objects = objects::getAllObjectsForForm($engine->cleanGet['MYSQL']['formID']);
	$form    = forms::get($engine->cleanGet['MYSQL']['formID']);

	$fields = array();
	foreach ($form['fields'] as $field) {
		if ($field['required'] == "true" && $field['duplicates'] == "true") {
			$fields[] =  $field['name'];
		}
	}

	$field_counts = array();
	foreach ($fields as $field_name) {
		$field_counts[$field_name] = array();
	}

	$duplicates = array();
	foreach ($objects as $object) {
		foreach ($fields as $field_name) {
			$field_counts[$field_name][$object["data"][$field_name]]++;

			if ($field_counts[$field_name][$object["data"][$field_name]] > 1) {
				$duplicates[] = $object["data"][$field_name];
			}
		}
	}

	if (count($duplicates)) {
		$output = "<ul>";
		foreach ($duplicates as $duplicate) {
			$output .= sprintf("<li>%s</li>", $duplicate);
		}
		$output .= "</ul>";
	}
	else {
		$output = "No duplicates found.";
	}

	localvars::add("dupe_list",$output);
}
catch(Exception $e) {
	log::insert("Data Entry: Metadata: Duplcates Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
}

localvars::add("results",displayMessages());
localvars::add("formID",$form['ID']);
localvars::add("formName",$form['title']);

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Duplicates: </h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
		<li><a href="{local var="siteRoot"}dataEntry/metadata.php?formID={local var="formID"}">{local var="formName"}</a></li>
	</ul>


	{local var="results"}

	{local var="dupe_list"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
