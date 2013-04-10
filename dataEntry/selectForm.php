<?php
include("../header.php");



try {
	
	$sql       = sprintf("SELECT `ID`, `title` FROM `forms` WHERE `metadata`='0'");
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
		errorHandle::errorMsg("Error getting Projects");
		throw new Exception('Error');
	}

	$formList = "<ul>";
	while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		// if (checkProjectPermissions($row['ID']) === TRUE) {
		// }
		$formList .= sprintf('<li><a href="form.php?formID=%s">%s</a></li>',
			$engine->openDB->escape($row['ID']),
			$engine->openDB->escape($row['title'])
			);

	}
	$formList .= "<ul>";

	localvars::add("formList",$formList);

}
catch(Exception $e) {
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Select a Form</h1>
	</header>

	{local var="results"}

	{local var="formList"}

</section>


<?php
$engine->eTemplate("include","footer");
?>
