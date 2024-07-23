<?php

session_save_path('/tmp');

include "../public_html/header.php";

// Set Table name
$table = 'objectProcessing';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

$sql       = sprintf("SELECT * FROM " . $table);
$sqlResult = mfcs::$engine->openDB->query($sql);
while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {    
    file_put_contents('./'.$table.'/' . $row["ID"] .'.json', print_r(json_encode($row), true));
}

print "Done.";
?>
