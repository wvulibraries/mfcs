<?php
global $engine;

$engine->dbTables("dbTables","prod","dbTables");
$engine->dbTables("forms","prod","forms");
$engine->dbTables("formFields","prod","formFields");
$engine->dbTables("formFieldProperties","prod","formFieldProperties");
// $engine->dbTables("permissions","prod","permissions");
$engine->dbTables("projects","prod","projects");
// $engine->dbTables("userPermissions","prod","userPermissions");
$engine->dbTables("users","prod","users");
$engine->dbTables("validationTypes","prod","validationTypes");

// temp table for permissions object
// $engine->dbTables("tempPermissions","prod","mfcsPermissions");
?>
