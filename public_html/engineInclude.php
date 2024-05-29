<?php

// Set memory limit to unlimited
ini_set('memory_limit', -1);

// Include the EngineAPI library
require_once "/home/mfcs.lib.wvu.edu/phpincludes/engine/engineAPI/latest/engine.php";

// Initialize the EngineAPI singleton instance
$engine = EngineAPI::singleton();

// Set error reporting to display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
