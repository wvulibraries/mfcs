<?php

include("../header.php");

recurseInsert("acl.php","php");

try {
	if (!isset($engine->cleanGet['MYSQL']['id'])   
		|| is_empty($engine->cleanGet['MYSQL']['id']) 
		|| !validate::integer($engine->cleanGet['MYSQL']['id'])) {

		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Project ID Provided.");
		throw new Exception('Error');
	}

	if (!isset($engine->cleanGet['MYSQL']['formID']) 
		|| is_empty($engine->cleanGet['MYSQL']['formID']) 
		|| !validate::integer($engine->cleanGet['MYSQL']['formID'])) {

		errorHandle::newError(__METHOD__."() - No Project ID Provided.", errorHandle::DEBUG);
		errorHandle::errorMsg("No Form ID Provided.");
		throw new Exception('Error');
	}
}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>{local var="projectName"}</h1>
	</header>

	{local var="results"}


<div id="left">

	<p>Built up left nav will go here</p>

</div>
<div id="right">

	{local var="form"}

</div>


</section>


<?php
$engine->eTemplate("include","footer");
?>