<?php
include("../header.php");

try {

	if (($formList = listGenerator::generateAccordionFormList(TRUE)) === FALSE) {
		throw new Exception("Error getting Forms Listing");
	}
	else {
		localvars::add("formList",$formList);
	}

}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());

log::insert("Data Entry: Form Select: View Page");

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
