<?php
include("../header.php");

try {

	if (($formList = listGenerator::generateFormSelectListForFormCreator()) === FALSE) {
		throw new Exception("Error generating form list.");
	}

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
		<h1>Select a Form</h1>
	</header>

	{local var="results"}

	{local var="formList"}

</section>


<?php
$engine->eTemplate("include","footer");
?>
