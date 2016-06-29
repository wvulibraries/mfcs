<?php
// header
include("../../../header.php");

$compatibleForms = listGenerator::createFormDropDownList($engine->cleanGet['MYSQL']['formID']);

header('Content-Type: text/html');
print $compatibleForms;

?>
