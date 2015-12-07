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
		<h1 class="page-title">Welcome to MFCS</h1>
	</header>

	<ul class="breadcrumbs">
			<li><a href="/">Home</a></li>
			<li></li>
	</ul>
</section>

<section class="aboutMFCS">
	<div class="widthContainer">
		<div class="pic">
			<img src="images/mfcsData.png" alt="mfcs holding a data icon" />
		</div>
		<div class="text">
			<p> MFCS is a system built to store and archive digital projects, finding aids, and historical material entrusted to the library.  This system allows us to keep secure data, achive the original, and make modifications for other technologies to use.  The material ultimately ends up in a repository system such as Hydra, Islandora, or DLXS. </p>
		</div>
	</div>
</section>

<section class="createIndex">
	<div class="widthContainer">
		<span class="span4">
			<h2> <i class="fa fa-tasks"></i> Manage Inventory </h2>
			<div class="text">
				<p> MFCS allows you to manage forms, and forms that you have created.  Forms can be edited by using the list forms link in the Form Management section.  Editing objects works in a similar fashion using the Data Entry section.    </p> <br>
				<div class="actionButton">
					<a href="/dataEntry/selectForm.php" class="btn btn-primary"> Create Objects </a>
					<a href="/dataView/list.php" class="btn btn-primary"> List Objects </a>
					<a href="/dataView/search.php" class="btn btn-primary"> Search </a>
				</div>
			</div>
		</span>
		<span class="span4">
			<h2> <i class="fa fa-list-alt"></i> Manage Forms </h2>
			<div class="text">
				<p>Creating forms allow you to collect certain information along with your digital objects and is the basis for creating objects. Creating Objects in MFCS is the way your archive data for use in a digital repository later. </p><br>
				<div class="actionButton">
					<a href="/formCreator/" class="btn btn-primary"> Create A Form </a>
					<a href="/formCreator/list.php" class="btn btn-primary"> List Forms </a>
				</div>
			</div>
		</span>
		<span class="span4">
			<h2> <i class="fa fa-book"></i> Documentation </h2>
			<div class="text">
				<p> Need help?  The documentation will help to provide some of the basic jargon and techniques to accomplishing tasks in MFCS.  </p><br>
				<div class="actionButton">
					<a href="https://github.com/wvulibraries/mfcs/wiki" class="btn btn-primary"> MFCS Wiki </a>
				</div>
			</div>
		</span>
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
