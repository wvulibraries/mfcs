<?php
include("../newEngine.php");

recurseInsert("acl.php","php");

recurseInsert("dbTableList.php","php");
$engine->dbConnect("database","mfcs",TRUE);

recurseInsert("vars.php","php");
recurseInsert("showField.php","php");
recurseInsert("phpFunctions.php","php");


$type = isset($engine->cleanGet['MYSQL']['type']) ? $engine->cleanGet['MYSQL']['type'] : NULL;
$id   = isset($engine->cleanGet['MYSQL']['id'])   ? $engine->cleanGet['MYSQL']['id']   : NULL;

print editFormItem($id,$type);
?>
