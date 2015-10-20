<?php
include("../header.php");

try {

	if (($accordionList = listGenerator::generateAccordionFormList()) === FALSE) {
		throw new Exception("Error generating Form List");
	}

	localvars::add("accordionList",$accordionList);
}
catch(Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localVars::add("results",displayMessages());
log::insert("Form Creator: View Forms");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Form</h1>
	</header>
	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/formCreator/list.php">List Forms</a></li>
		</ul>
	</nav>

	{local var="results"}

	<div class="container-fluid">
		{local var="accordionList"}
	</div>

</section>


<?php
$engine->eTemplate("include","footer");
?>
