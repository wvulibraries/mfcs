<?php
include("../header.php");

try {

	$forms = forms::getObjectForms();

	if ($forms === FALSE) {
		errorHandle::errorMsg("Error getting Forms");
		throw new Exception('Error');
	}

	$formList = "<ul>";
	foreach ($forms as $form) {

		// @TODO
		// if (projects::checkPermissions($row['ID']) === TRUE) {
		// }
		$formList .= sprintf('<li><a href="object.php?formID=%s">%s</a></li>',
			htmlSanitize($form['ID']),
			htmlSanitize($form['title'])
			);

	}
	$formList .= "<ul>";

	localvars::add("formList",$formList);

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Form.</h1>
	</header>

	{local var="results"}

	{local var="formList"}

</section>


<?php
$engine->eTemplate("include","footer");
?>
