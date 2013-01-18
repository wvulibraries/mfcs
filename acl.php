<?php
$engine->accessControl("ADgroup","libraryGroup_staff",TRUE,FALSE);
$engine->accessControl("ADgroup","libraryGroup_students",TRUE,FALSE);
$engine->accessControl("denyAll");
$engine->accessControl("build");
?>
