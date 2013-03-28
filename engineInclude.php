<?php
require_once("/home/dev1.systems.lib.wvu.edu/phpincludes/engine/engineAPI/3.2/engine.php");
$engine = EngineAPI::singleton();

// Load the mfcs class
require_once "includes/mfcs.php";
mfcs::singleton();

errorHandle::errorReporting(errorHandle::E_ALL);
?>
