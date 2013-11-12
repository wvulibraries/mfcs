<?php
require("header.php");



$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Administrator Actions</h1>
	</header>

    <nav id="breadcrumbs">
        <ul class="breadcrumb">
            <li><a href="{local var="siteRoot"}">Home</a></li>
        </ul>
    </nav>  

	<ul class="pickList">
		<li>
			<a href="projects/" class="btn">Projects</a>
		</li>
		<li>
			<a href="users/" class="btn">Users</a>
		</li>
        <li>
            <a href="watermarks/" class="btn">Watermarks</a>
        </li>
	</ul>

</section>

<?php
$engine->eTemplate("include","footer");
?>
