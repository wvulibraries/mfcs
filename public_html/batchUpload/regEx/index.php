<?php
// header
include("../../header.php");

if(isset($engine->cleanGet['MYSQL']['regex']) && isset($engine->cleanGet['MYSQL']['filename'])){
    $regEx = stripslashes($engine->cleanGet['MYSQL']['regex']);
    $sampleString = $engine->cleanGet['MYSQL']['filename'];
} else {
    $regEx = "no RegEx";
    $sampleString = "no string";
}

// // Tests
// $regEx = "/(\w+?)\.([\w|\s]+?)\.([\w|\s]+?)\./";
// $sampleString = "rwar.something cool.testj12854.pdf";

preg_match_all($regEx,$sampleString,$matches);
header('Content-Type: application/json');
print json_encode($matches);


// header('Content-Type: application/json');
// print json_encode(array($regEx, $sampleString));
?>