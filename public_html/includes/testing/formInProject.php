<?php

include("../../header.php");

$foo = forms::checkFormInProject("1","21");

print "<pre>";
var_dump($foo);
print "</pre>";

$foo = forms::checkFormInProject("2","21");

print "<pre>";
var_dump($foo);
print "</pre>";

?>