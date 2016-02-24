<?php
include("header.php");

// Handle all the ajax calls
require_once "includes/ajaxHandler.php";

localVars::add("results",displayMessages());
log::insert("Index: View Page");

$permission_denied = (isset($engine->cleanGet['MYSQL']['permissionFalse']))? true : false;

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1 class="page-title">Welcome to MFCS</h1>
	</header>

	<ul class="breadcrumbs">
			<li><a href="/">Home</a></li>
			<li></li>
			<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

	<?php if($permission_denied) { ?>
	<div class="permissionsAlert alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>
		<h4> Warning! </h4>
		<p> You do not have permissions to access the last page you visited, if you believe that this is an error contact your system admin. </p>
	</div>
	<?php } ?>

	<section class="aboutMFCS">
		<div class="widthContainer">
			<div class="pic">
				<img src="images/mfcsData.png" alt="mfcs holding a data icon" />
			</div>
			<div class="text">
				<p>
					MFCS is an
					<a href="https://en.wikipedia.org/wiki/Preservation_%28library_and_archival_science%29">archival and preservation</a>
					<a href="https://en.wikipedia.org/wiki/Digital_asset_management">Digital Asset Management System</a>
					developed by <a href="https://lib.wvu.edu/">WVU Libraries</a> since 2011. For more information
					about MFCS visit the <a href="https://github.com/wvulibraries/mfcs">MFCS Github Project Page</a>.
				</p>
			</div>
		</div>
	</section>

	<section class="createIndex">
		<div class="widthContainer">
			<span class="span4">
				<h2> <i class="fa fa-tasks"></i> Manage Inventory </h2>
				<div class="text">
					<p>
						MFCS allows you to manage forms, and forms that you have
						created.  Forms can be edited by using the list forms link in
						the Form Management section.  Editing objects works in a
						similar fashion using the Data Entry section.
					</p> <br>
					<div class="actionButton">
						<a href="/dataEntry/selectForm.php" class="btn btn-primary"> Create &amp; Edit Objects </a>
						<a href="/data/search/" class="btn btn-primary"> Search </a>
					</div>
				</div>
			</span>
			<span class="span4">
				<h2> <i class="fa fa-list-alt"></i> Manage Forms </h2>
				<div class="text">
					<p>
						Creating forms allow you to collect certain information along
						with your digital objects and is the basis for creating
						objects. Creating Objects in MFCS is the way your archive
						data for use in a digital repository later.
					</p><br>
					<div class="actionButton">
						<a href="/formCreator/" class="btn btn-primary"> Create A Form </a>
						<a href="/formCreator/list.php" class="btn btn-primary"> List Forms </a>
					</div>
				</div>
			</span>
			<span class="span4">
				<h2> <i class="fa fa-book"></i> Documentation </h2>
				<div class="text">
					<p>
						Need help?  The documentation will help to provide some of
						the basic jargon and techniques to accomplishing tasks in
						MFCS.
					</p><br>
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

</section>


<?php
 $engine->eTemplate("include","footer");
?>
