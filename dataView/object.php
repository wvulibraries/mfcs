<?php
include("../header.php");
recurseInsert("acl.php","php");

try {

	// Object ID Validation
	if (objects::validID(TRUE) === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (forms::validID() === FALSE) {
		throw new Exception("No Form ID Provided.");
	}

	if (!objects::checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		errorHandle::newError("Object not from this form.", errorHandle::DEBUG);
		throw new Exception("Object not from this form");
	}

	//////////
	// Metadata Tab Stuff
	$form = forms::get($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		throw new Exception("Error retrieving form.");
	}

	localvars::add("formName",$form['title']);

	// build the form for displaying
	$builtForm = forms::build($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		throw new Exception("Error building form.");
	}

	localvars::add("form",$builtForm);
	// Metadata Tab Stuff
	//////////

	//////////
	// Project Tab Stuff
	$selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
	localVars::add("projectOptions",projects::generateProjectChecklist($selectedProjects));
	// Project Tab Stuff
	//////////

	//////////
	// Children Tab Stuff
	$children = objects::getChildren($engine->cleanGet['MYSQL']['objectID']);
	// Children Tab Stuff
	//////////

}
catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));
localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>View Object</h1>
	</header>

	<div class="container-fluid">
		<div class="span3">
			{local var="leftnav"}
		</div>

		<div class="span9">
			<div class="row-fluid" id="results">
				{local var="results"}
			</div>

			<div class="row-fluid">
				<ul class="nav nav-tabs">
					<li><a data-toggle="tab" href="#metadata">Metadata</a></li>
					<li><a data-toggle="tab" href="#project">Project</a></li>
					<li><a data-toggle="tab" href="#children">Children</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane" id="metadata">
						{local var="form"}
					</div>

					<div class="tab-pane" id="project">
						<h2>Change Project Membership</h2>

						<form action="{phpself query="true"}" method="post">
							{local var="projectOptions"}
							{engine name="csrf"}
							<input type="submit" class="btn btn-primary" name="projectForm">
						</form>
					</div>

					<div class="tab-pane" id="children">
						Children content
						<?php
						print "<pre>";
						print_r($children);
						print "</pre>";
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(function() {
		// Show first tab on page load
		$(".nav-tabs a:first").tab("show");

		// Disable form input fields
		$(":input").prop("disabled",true);

		// Remove form submits
		$(":input[type=submit]").remove();

		// Remove form actions
		$("form").removeAttr("action");
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>