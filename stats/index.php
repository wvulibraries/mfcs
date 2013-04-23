<?php
include("../header.php");
$engine->eTemplate("include","header");

$stats = new mfcsStats(".");
?>
<section>
	<header class="page-header">
		<h1>System Stats</h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}/stats/">Stats</a></li>
		</ul>
	</nav>

	<?php $stats->showStatFiles(); ?>

</section>
<?php
$engine->eTemplate("include","footer");
?>