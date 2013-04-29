<?php
include("../header.php");

// @TODO
// Check if the user has an editing privileges here

try {

	if (($formList = listGenerator::generateAccordionFormList(TRUE)) === FALSE) {
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
		<h1>Select a Form</h1>
	</header>

    <nav id="breadcrumbs">
        <ul class="breadcrumb">
            <li><a href="{local var="siteRoot"}">Home</a></li>
            <li><a href="{local var="siteRoot"}/dataEntry/selectForm.php">Select a Form</a></li>
        </ul>
    </nav> 

	{local var="results"}

	{local var="formList"}

</section>


<?php
$engine->eTemplate("include","footer");
?>
