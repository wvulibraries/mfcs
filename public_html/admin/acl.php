<?php
// prevent ACLs on vagrant
if ($_SERVER['SERVER_NAME'] != "localhost") { 
	$engine->accessControl("ADgroup","libraryGroup_staff",TRUE,FALSE);
	$engine->accessControl("ADgroup","libraryGroup_students",TRUE,FALSE);
	$engine->accessControl("denyAll");
	$engine->accessControl("build");
}
?>
