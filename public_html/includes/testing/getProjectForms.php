<?php

	include("../../header.php");

	$foo = projects::getForms("1");

	print "<pre>";
	var_dump($foo);
	print "</pre>";

		$foo = projects::getForms("1",TRUE);

	print "<pre>";
	var_dump($foo);
	print "</pre>";

?>