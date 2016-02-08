<?php

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");

if (!isCLI()) {
	print "Must be run from the command line.";
	exit;
}

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

if (system_information::archives_usage() <= mfcs::config("drive_space_min_free")) {
	notification::notifyAdmins("MFCS Archives HDD Space Low", "Free Space on MFCS: ".system_information::free_space());
}


?>