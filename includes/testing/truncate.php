<?php

include("../../header.php");


$sql       = array();
$sql[] = "TRUNCATE TABLE `containers`";
$sql[] = "TRUNCATE TABLE `dupeMatching`";
$sql[] = "TRUNCATE TABLE `forms`";
$sql[] = "TRUNCATE TABLE `objectMetadataLinks`";
$sql[] = "TRUNCATE TABLE `objectProjects`";
$sql[] = "TRUNCATE TABLE `objectTypes`";
$sql[] = "TRUNCATE TABLE `objects`";
$sql[] = "TRUNCATE TABLE `permissions`";
$sql[] = "TRUNCATE TABLE `projects`";
$sql[] = "TRUNCATE TABLE `revisions`";
$sql[] = "TRUNCATE TABLE `users`";
$sql[] = "TRUNCATE TABLE `users_projects`";
$sql[] = "TRUNCATE TABLE `watermarks`";

foreach ($sql as $I=>$K) {
	$sqlResult = $engine->openDB->query($K);
	if ($sqlResult['result'] === FALSE) {
		print $K."\n";
		die($sqlResult['error']);
	}
}

print "Done.";

?>