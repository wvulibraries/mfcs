<?php

// standard engine acl file
// recommend limiting by IP address, at the very least

// this file should be sylinked to your mfcs base directory, example:
// ln -s /home/mfcs.lib.wvu.edu/git_pull/mfcs/api_acl.php /home/mfcs.lib.wvu.edu/api_acl.php

// example usage:
// $engine->accessControl("accessControl_ip_checkIPAddr","xxx.xxx.xxx.*",TRUE,FALSE);
// $engine->accessControl("denyAll");
// $engine->accessControl("build");

?>
