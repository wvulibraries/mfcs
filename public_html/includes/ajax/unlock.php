<?php

require("../../header.php");

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

if (isset(mfcs::$engine->cleanGet['MYSQL']['lockID']) && 
	!is_empty($engine->cleanGet['MYSQL']['lockID'])   && 
	validate::integer($engine->cleanGet['MYSQL']['lockID'])) {

	locks::unlock_by_lockID($engine->cleanGet['MYSQL']['lockID']);

}

die();

?>