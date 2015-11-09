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
	</header>

	<ul class="breadcrumbs">
			<li><a href="/">Home</a></li>
			<li></li>
	</ul>
</section>

<section class="aboutMFCS">
	<div class="widthContainer">
		<div class="text">
			<h2> Welcome to Metadata Form Creation System </h2>
			<p> MFCS is a system built to store and archive digital projects, finding aids, and historical material entrusted to the library.  This system allows us to keep secure data, achive the original, and make modifications for other technologies to use.  The matieral ultimately ends up in a system such as Hydra, Islandora, or DLXS. <br><br>  As systems change, become obsolete, or are updated the only change that should be needed to migrate data to a new system are new or updated MFCS export scripts. No more exporting and importing data between your old authoritative system and your new one, which reduces the risk of cross walking errors and data corruption.  </p>
		</div>
	</div>
</section>

<section class="createIndex">
	<h2> Create Objects </h2>
	<ul>
		<li>
			<h3> Objects </h3>
			<a class="btn btn-primary"> New Object </a>
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
