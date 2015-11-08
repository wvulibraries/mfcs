<?php

// this file should be scheduled to run occasionally. Mark your calendars and check
// for an email from it to confirm that email is working.

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");

if (!isCLI()) {
	print "Must be run from the command line.";
	exit;
}

notification::email($notificationEmails, "MFCS Test Email", "This is just a test");

?>