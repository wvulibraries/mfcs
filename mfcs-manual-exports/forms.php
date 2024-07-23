<?php

session_save_path('/tmp');

include "../public_html/header.php";

// Set Table name
$table = 'forms';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

$forms = forms::get();

foreach ($forms as $form) {
 if ($form != null) {
	  $sql       = sprintf("SELECT * FROM permissions WHERE permissions.formID='%s'", $form['ID']);
	  $sqlResult = mfcs::$engine->openDB->query($sql);
	  while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
		unset($row['ID']);
		unset($row['formID']);
		$form["permissions"][] = (object)$row;
	  }

    file_put_contents('./' . $table . '/' . $form["ID"] . '.json', print_r(json_encode($form), true));
 }
}

print "Done.";
?>
