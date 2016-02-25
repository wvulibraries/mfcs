<?php

include("../header.php");

if(isset($_SESSION)) {

	if (!isset($engine->cleanGet['MYSQL']['csrf']) && isset($_SESSION['CSRF'])) {
		$engine->cleanGet['MYSQL']['csrf'] = $_SESSION['CSRF'];
	}

	$termed = sessionEnd($engine);
}

?>

<?php
if ($termed) {

	$logoutRedirect = $engineVars['WEBROOT'];
	if (isset($engine->cleanGet['HTML']['redirect'])) {
		$logoutRedirect = $engine->cleanGet['HTML']['redirect'];
	}

	header( 'Location: '.$logoutRedirect ) ;

}
else {
 print "<h1>CSRF Error Check</h1>";
}
?>