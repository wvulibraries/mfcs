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

<section class="createIndex">
	<div class="widthContainer">
		<span class="span4">
			<div class="hero"><img src="images/mfcsLines-01.svg"></div>
			<div class="text">
				<h2> Create Objects & Forms </h2>
				<p>Creating forms allow you to collect certain information along with your digital objects and is the basis for creating objects. Creating Objects in MFCS is the way your archive data for use in a digital repository later. </p>
				<div class="actionButton">
					<a href="" class="btn btn-primary"> Create A Form </a> <a href="" class="btn btn-primary"> Create An Object </a>
				</div>
			</div>
		</span>
		<span class="span4">
			<div class="hero"><img src="images/mfcsLine-02.svg"></div>
			<div class="text">
				<h2> Manage Inventory </h2>
				<p> MFCS allows you to manage forms, and forms that you have created.  Forms can be edited by using the list forms link in the Form Management section.  Editing objects works in a similar fashion using the Data Entry section.    </p>
				<div class="actionButton">
					<a href="" class="btn btn-primary"> List Forms </a> <a href="" class="btn btn-primary"> List Objects </a> <a href="" class="btn btn-primary"> Search </a>
				</div>
			</div>
		</span>
		<span class="span4">
			<div class="hero"><img src="images/mfcsLines-03.svg"></div>
			<div class="text">
				<h2> System Administration </h2>
				<p> There are other things that can be managed by system administrators.  Adding users, granting permissions, and exporting for digital repositories.  If you are need one of these tasks done contact your system administrator. </p>
			</div>
		</span>
	</div>
</section>

<section class="aboutMFCS">
	<div class="widthContainer">
		<div class="pic">
			<img src="images/mfcsData.png" alt="mfcs holding a data icon" />
		</div>
		<div class="text">
			<h2> Welcome to Metadata Form Creation System </h2>
			<p> MFCS is a system built to store and archive digital projects, finding aids, and historical material entrusted to the library.  This system allows us to keep secure data, achive the original, and make modifications for other technologies to use.  The matieral ultimately ends up in a repository system such as Hydra, Islandora, or DLXS. </p>
		</div>
	</div>
</section>

<section>
	<ul>
		{local var="projectList"}
	</ul>
</section>


<?php
 $engine->eTemplate("include","footer");
?>
