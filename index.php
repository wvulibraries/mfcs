<?php
include("header.php");



try {
	$sql       = sprintf("SELECT * FROM `projects`");
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
		errorHandle::errorMsg("Error getting Projects");
		throw new Exception('Error');
	}

	$projectList = "";
	while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		if (checkProjectPermissions($row['ID']) === TRUE) {
			$projectList .= sprintf('<li><a href="dataEntry/index.php?id=%s">%s</a></li>',
				$engine->openDB->escape($row['ID']),
				$engine->openDB->escape($row['projectName'])
				);
		}

	}

	localvars::add("projectList",$projectList);

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Project</h1>
	</header>

	{local var="results"}

	<ul>
		{local var="projectList"}
	</ul>


	<ul>
		<li>
			<a href="dataEntry/selectForm.php">Create new Object</a>
		</li>
		<li>
			<a href="">List Objects</a>
		</li>
		<li>
			<a href="">Metadata Forms</a>
		</li>
		<li>
			<a href="">Export</a>
		</li>
	</ul>

</section>


<?php
$engine->eTemplate("include","footer");
?>
