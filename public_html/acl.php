<?php

	EngineAPI::$engineVars['loginPage']    = "/login/";

	$engine->accessControl("ADgroup","libraryGroup_staff",TRUE,FALSE);
	$engine->accessControl("ADgroup","libraryGroup_students",TRUE,FALSE);
	$engine->accessControl("ADgroup","ibraryGroup_AppV_WVCStudents",TRUE,FALSE);
	$engine->accessControl("denyAll");
	$engine->accessControl("build");

?>
