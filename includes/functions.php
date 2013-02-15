<?php
function displayMessages() {
	$engine = EngineAPI::singleton();
	if (is_empty($engine->errorStack)) {
		return FALSE;
	}
	return '<section><header><h1>Results</h1></header>'.errorHandle::prettyPrint().'</section>';
}

function encodeFields($fields) {

	return base64_encode(serialize($fields));

}

function decodeFields($fields) {

	return unserialize(base64_decode($fields));

}

function checkProjectPermissions($id) {

	$engine = EngineAPI::singleton();

	$username = sessionGet("username");

	$sql       = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.projectID='%s' AND users.username='%s'",
		$engine->openDB->escape($id),
		$engine->openDB->escape($username)
		);
	$sqlResult = $engine->openDB->query($sql);
	
	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
		return(FALSE);
	}
	
	$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	if ((int)$row['COUNT(permissions.ID)'] > 0) {
		return(TRUE);
	}

	return(FALSE);

}

?>