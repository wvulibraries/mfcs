<?php
include("../header.php");

try {

	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& (is_empty($engine->cleanGet['MYSQL']['objectID'])
			|| !validate::integer($engine->cleanGet['MYSQL']['objectID']))
		) {

		errorHandle::newError(__METHOD__."() - ObjectID Provided is invalid", errorHandle::DEBUG);
		errorHandle::errorMsg("ObjectID Provided is invalid..");
		throw new Exception('Error');
	}
	else if (!isset($engine->cleanGet['MYSQL']['objectID'])) {
		$engine->cleanGet['MYSQL']['objectID'] = NULL;
	}

	if (!isset($engine->cleanGet['MYSQL']['formID'])
		|| is_empty($engine->cleanGet['MYSQL']['formID'])
		|| !validate::integer($engine->cleanGet['MYSQL']['formID'])) {

		if (!isnull($engine->cleanGet['MYSQL']['objectID'])) {
			$object = objects::get($engine->cleanGet['MYSQL']['objectID']);

			if ($object === FALSE) {
				errorHandle::newError(__METHOD__."() - No Form ID Provided, error getting Object", errorHandle::DEBUG);
				errorHandle::errorMsg("No Form ID Provided, error getting Object.");
				throw new Exception('Error');
			}

			http::setGet('formID',$object['formID']);

		}
		else {
			errorHandle::newError(__METHOD__."() - No Form ID Provided.", errorHandle::DEBUG);
			errorHandle::errorMsg("No Form ID Provided.");
			throw new Exception('Error');
		}
	}


	// check for edit permissions on the project
	// if (checkProjectPermissions($engine->cleanGet['MYSQL']['id']) === FALSE) {
	// 	errorHandle::errorMsg("Permissions denied for working on this project");
	// 	throw new Exception('Error');
	// }

	// check that this form is part of the project
	// // TODO need forms from User
	// if (!checkFormInProject($engine->cleanGet['MYSQL']['id'],$engine->cleanGet['MYSQL']['formID'])) {
	// 	errorHandle::errorMsg("Form is not part of project.");
	// 	throw new Exception('Error');
	// }

	// if an object ID is provided make sure the object is from this form
	if (isset($engine->cleanGet['MYSQL']['objectID'])
		&& !checkObjectInForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID'])) {
		errorHandle::errorMsg("Object not from this form");
		throw new Exception('Error');
	}

	// Get the project
	// $project = NULL; // TODO: Needs to be gotten from the user info
	// if ($project === FALSE) {
	// 	errorHandle::errorMsg("Error retrieving project.");
	// 	throw new Exception('Error');
	// }

	$form = getForm($engine->cleanGet['MYSQL']['formID']);
	if ($form === FALSE) {
		errorHandle::errorMsg("Error retrieving form.");
		throw new Exception('Error');
	}

	localvars::add("formName",$form['title']);

	// handle submission
	if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID']);
		if ($return === FALSE) {
			errorHandle::errorMsg("Error Submitting Form.");
			throw new Exception('Error');
		}
	}
	else if (isset($engine->cleanPost['MYSQL']['updateForm'])) {
		$return = forms::submit($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
		if ($return === FALSE) {
			errorHandle::errorMsg("Error Updating Form.");
			throw new Exception('Error');
		}
	}

	// build the form for displaying
	$builtForm = buildForm($engine->cleanGet['MYSQL']['formID'],$engine->cleanGet['MYSQL']['objectID']);
	if ($builtForm === FALSE) {
		errorHandle::errorMsg("Error building form.");
		throw new Exception('Error');
	}

	localvars::add("form",$builtForm);

	// localvars::add("leftnav",buildProjectNavigation($engine->cleanGet['MYSQL']['id']));

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/css/fineuploader.css" />
<script type="text/javascript" src="{local var="siteRoot"}includes/js/jquery.fineuploader.min.js"></script>
<style>
  /* Fine Uploader
  -------------------------------------------------- */
  .qq-upload-list {
    text-align: left;
  }

  li.alert-success {
    background-color: #DFF0D8;
  }

  li.alert-error {
    background-color: #F2DEDE;
  }

  .alert-error .qq-upload-failed-text {
    display: inline;
  }
</style>
<section>
	<header class="page-header">
		<h1>{local var="formName"}</h1>
	</header>

	{local var="results"}

	<div class="row-fluid">
		<div class="span3" id="left">
			{local var="leftnav"}
		</div>
		<div class="span9" id="right">
			{local var="form"}
		</div>
	</div>
</section>


<?php
$engine->eTemplate("include","footer");
?>
