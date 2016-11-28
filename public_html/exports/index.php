<?php
include("../header.php");
$engine->eTemplate("include","header");

$stats = new exporting(".");

log::insert("Exporting: View Index");

localvars::add("exportsList",$stats->showExportListing());
?>
<section>
	<header class="page-header">
		<h1>Exports</h1>
	</header>

	<ul class="breadcrumbs">
			<li><a href="/">Home</a></li>
			<li><a href="/exports"> Exports </a></li>
			<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Exporting" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

	{local var="exportsList"}

</section>
<?php
$engine->eTemplate("include","footer");
?>
