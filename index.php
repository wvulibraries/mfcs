<?php
include("header.php");

// Handle all the ajax calls
require_once "includes/ajaxHandler.php";

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Task</h1>
	</header>

    <nav id="breadcrumbs">
        <ul class="breadcrumb">
            <li><a href="{local var="siteRoot"}">Home</a></li>
        </ul>
    </nav>  

	{local var="results"}

	<ul>
		{local var="projectList"}
	</ul>


	<ul class="pickList">
		<li>
			<a href="dataEntry/selectForm.php" class="btn">Create new Object</a>
		</li>
		<li>
			<a href="dataView/list.php" class="btn">List Objects</a>
		</li>
        <li>
            <a href="dataView/search.php" class="btn">Search Objects</a>
        </li>
<!-- 		<li>
			<a href="dataEntry/selectMetadataForm.php" class="btn">Metadata Forms</a>
		</li> -->
		<li>
			<a href="" class="btn">Export</a>
		</li>
        <li>
            <a href="stats/" class="btn">Statistics</a>
        </li>
	</ul>

</section>


<?php
$engine->eTemplate("include","footer");
?>
