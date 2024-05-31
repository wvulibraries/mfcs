<?php
require_once "../../header.php";

$sql       = sprintf("SELECT `extension` FROM `obsoleteFileTypes`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

$obsoleteFileTypes = array();
while($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
	$obsoleteFileTypes[] = $row['extension'];
}



$files_type_list_by_form = '<ul id="obsolete_files_list">';
foreach ($obsoleteFileTypes as $extension) {

	$sql       = sprintf('SELECT COUNT(*) as `count` FROM `filesChecks` WHERE `location` LIKE "%%%s"',$extension);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
		errorHandle::errorMsg("Unable to get Obsolete File Types");
	}

	$row  = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC);

	if ($row['count'] > 0) {

		$sql       = sprintf('SELECT `location`,`objectID` FROM `filesChecks` WHERE `location` LIKE "%%%s"',$extension);
		$sqlResult2 = $engine->openDB->query($sql);

		if (!$sqlResult2['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			errorHandle::errorMsg("Unable to get Files");
		}

		while($file = mysqli_fetch_array($sqlResult2['result'],  MYSQLI_ASSOC)) {
			$object = objects::get($file['objectID']);
			$files_type_list_by_form .= sprintf('<li><a href="/dataEntry/object.php?objectID=%s">Edit<i class="fa fa-edit"></i></a> <span class="filename">%s</span> from <span class="obsolete_form_name">%s</span></li>',
				$file['objectID'],
				basename($file['location']),
				forms::title($object['formID'])
				);
		}

		$files_type_list_by_form .= "</li>";

	}

	$files_type_list_by_form .= "</li>";
}
$files_type_list_by_form .= "</ul>";

localvars::add("files_type_list_by_form",$files_type_list_by_form);

log::insert("Dashboard: View");

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Obsolete Files</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Obsolete" target="_blank"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>


	{local var="files_type_list_by_form"}

</section>

<?php
$engine->eTemplate("include","footer");
?>
