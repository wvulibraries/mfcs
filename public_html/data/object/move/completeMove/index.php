<?php
// header
include("../../../../header.php");


// Mikes Crazy Backend Data Here

if(isset($engine->cleanGet['MYSQL']['objects']) && isset($engine->cleanGet['MYSQL']['formID'])){
    $result = "Yay we got the data";
} else {
    $result = "No Data";
}

header('Content-Type: application/json');
print $result;

?>
