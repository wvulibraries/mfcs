<?php
// header
include("../../header.php");

if(isset($engine->cleanGet['MYSQL']['filename'])){
    $sampleString = $engine->cleanGet['MYSQL']['filename'];
}

if(isset($engine->cleanGet['MYSQL']['regex'])){
    $regEx = $engine->cleanGet['MYSQL']['regex'];
}

// $regEx        = '/(\w+?)\.(\w+?)\.(.+?)\./';
// $sampleString = "foo.123.Some Crazy Stuff.pdf";
preg_match_all($regEx,$sampleString,$matches);

header('Content-Type: application/json');
print json_encode($matches);


?>