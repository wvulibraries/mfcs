<?php
// header
include("../../../../header.php");


// Mikes Crazy Backend Data Here

if(isset($engine->cleanPost['MYSQL']['objects']) && isset($engine->cleanPost['MYSQL']['formID'])){
    $result = "Yay we got the data";
} else {
    $result = "No Data";
}

header('Content-Type: application/json');
print $result;

?>
