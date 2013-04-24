<?php
include("../header.php");

try {

	if (($forms = forms::getObjectForms()) === FALSE) {
		throw new Exception("Error getting Forms");
	}

	if (($metaForms = forms::getMetadataForms()) === FALSE) {
		throw new Exception("Errot getting Metadata Forms.");
	}

	$formList = '<ul class="pickList">';
	foreach ($forms as $form) {

		$metadataForms = forms::getObjectFormMetaForms($form['ID']);

		if (count($metadataForms) < 1) continue;

		$formList .= '<li>';
		$formList .= sprintf('<h1 class="pickListHeader">%s</h1>',
			htmlSanitize($form['title'])
			);
		$formList .= '<ul class="pickList">';

		foreach ($metadataForms as $metadataForm) {

			// Remove the array from the master list of metadata forms, so we 
			// don't redisplay it below
			if (in_array($metadataForm,$metaForms,TRUE)) {
				if(($index = array_search($metadataForm, $metaForms)) !== FALSE) {
					unset($metaForms[$index]);
				}
			}

			$formList .= '<li>';
			$formList .= sprintf('<a href="metadata.php?formID=%s" class="btn">%s</a>',
				$metadataForm['ID'],
				htmlSanitize($metadataForm['title'])
				);
			$formList .= '</li>';

		}

		$formList .= '</ul>';
		$formList .= '</li>';

	}
	if (count($metaForms) > 0) {

		$formList .= '<li>';
		$formList .= '<h1 class="pickListHeader">Unassigned Metadata Forms</h1>';
		$formList .= '<ul class="pickList">';

		foreach ($metaForms as $metadataForm) {
			$formList .= '<li>';
			$formList .= sprintf('<a href="metadata.php?formID=%s" class="btn">%s</a>',
				$metadataForm['ID'],
				htmlSanitize($metadataForm['title'])
				);
			$formList .= '</li>';
		}

		$formList .= '</ul>';
		$formList .= '</li>';

	}
	$formList .= "<ul>";

	localvars::add("formList",$formList);

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Form.</h1>
	</header>
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/dataEntry/selectMetadataForm.php">Select Metadata Form</a></li>
		</ul>
	</nav>

	<div class="row-fluid" id="results">
		{local var="results"}
	</div>
	
	{local var="formList"}

</section>


<?php
$engine->eTemplate("include","footer");
?>
