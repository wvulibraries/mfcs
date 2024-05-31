<?php

session_save_path('/tmp');

include "../public_html/header.php";

// Set Table name
$table = 'users';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

foreach (users::getUsers() as $user) {
 if ($user != null) {
   file_put_contents('./'.$table.'/' . $user["ID"] .'.json', print_r(json_encode($user), true));
 }
}

print "Done.";
?>
