<?php
include("../header.php");

try {

	if (($objectFormList = listGenerator::generateFormSelectListForFormCreator(FALSE)) === FALSE) {
		throw new Exception("Error generating object form list.");
	}

	if (($metadataFormList = listGenerator::generateFormSelectListForFormCreator(TRUE)) === FALSE) {
		throw new Exception("Error generating metadata form list.");
	}

	localvars::add("objectFormList",$objectFormList);
	localvars::add("metadataFormList",$metadataFormList);

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
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li>
				<a href="{local var="siteRoot"}">Home</a><span class="divider">/</span><a href="{local var="siteRoot"}/formCreator/list.php">List Forms</a>
			</li>
		</ul>
	</nav>

	{local var="results"}

	<div class="container-fluid">
		<div class="span4 text-center">
			<header>
				<h2>Object Forms</h2>
			</header>
			{local var="objectFormList"}
		</div>

		<div class="span4 text-center">
			<header>
				<h2>Metadata Forms</h2>
			</header>
			{local var="metadataFormList"}
		</div>
	</div>

</section>


<?php
$engine->eTemplate("include","footer");
?>
