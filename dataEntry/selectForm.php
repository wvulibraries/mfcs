<?php
include("../header.php");

// @TODO
// Check if the user has an editing privileges here

try {

	if (($formList = listGenerator::generateFormSelectList()) === FALSE) {
		errorHandle::errorMsg("Error getting Forms Listing");
		throw new Exception('Error');
	}
	else {
		localvars::add("formList",$formList);
	}

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
