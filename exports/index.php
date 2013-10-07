<?php
include("../header.php");
$engine->eTemplate("include","header");

$stats = new exporting(".");
localvars::add("exportsList",$stats->showExportListing());
?>
<section>
	<header class="page-header">
		<h1>Exports</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/exports/">Exports</a></li>
		</ul>
	</nav>

	{local var="exportsList"}

</section>
<?php
$engine->eTemplate("include","footer");
?>