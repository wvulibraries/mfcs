<?php
include("header.php");

// Handle all the ajax calls
require_once "includes/ajaxHandler.php";

localVars::add("results",displayMessages());
log::insert("Index: View Page");

if(isset($engine->cleanGet['MYSQL']['permissionFalse'])){
	print " No Permisson to that page, please select another page";

	$errorLoggedIn = sprintf('<div class="alert alert-error alert-block error"><button type="button" class="close" data-dismiss="alert">&times;</button>
  								<h4> Warning! </h4>
  								<p> You do not have permissions to access the last page you visited, if you believe that this is an error contact your system admin. </p>
  							 </div>'
  	);

  	localVars::add("feedback", $errorLoggedIn);
}

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1 class="page-title">Select a Task</h1>

		<ul class="breadcrumbs">
 			<li><a href="/">Home</a></li>
 			<li></li>
		</ul>
	</header>



	{local var="feedback"}
	{local var="results"}

	<ul class="picklist">
		<li>
			<a href="dataEntry/selectForm.php" class="btn">Create new Object</a>
		</li>
		<li>
			<a href="dataView/list.php" class="btn">List Objects</a>
		</li>
		<li>
			<a href="dataView/search.php" class="btn">Search Objects</a>
		</li>
		<li>
			<a href="exports/" class="btn">Export</a>
		</li>
		<li>
			<a href="stats/" class="btn">Statistics</a>
		</li>
	</ul>
</section>

<section>
	<ul>
		{local var="projectList"}
	</ul>
</section>


<?php
 $engine->eTemplate("include","footer");
?>
