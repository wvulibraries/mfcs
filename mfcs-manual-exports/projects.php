<?php

session_save_path('/tmp');

include "../public_html/header.php";

// Set Table name
$table = 'projects';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

$projects = projects::getProjects();

foreach ($projects as $project) {
 if ($project != null) {

	  $sql       = sprintf("SELECT * FROM users_projects WHERE users_projects.projectID='%s'", $project['ID']);
	  $sqlResult = mfcs::$engine->openDB->query($sql);
	  while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
      unset($row['UD']);
      unset($row['projectID']);
      $project["users"][] = (object)$row;
	  }

    file_put_contents('./'.$table.'/' . $project["ID"] .'.json', print_r(json_encode($project), true));
 }
}

print "Done.";
?>
