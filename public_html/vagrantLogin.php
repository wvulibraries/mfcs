<?php 

// if (strtolower($_SERVER['SERVER_NAME']) != "localhost") {
// 	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
// 	print "Page not found.";
// 	die();
// }

require 'engineInclude.php';

$_SESSION = unserialize(base64_decode('YTo2OntzOjQ6IkNTUkYiO3M6MzI6Ijc0MmQ5MDkzNGFiNDVkNDFkNmFkZDA1OTkwZTgwMTMwIjtzOjY6Imdyb3VwcyI7YToyOntpOjA7czoxMjoiRG9tYWluIFVzZXJzIjtpOjE7czoxODoibGlicmFyeUdyb3VwX3N0YWZmIjt9czoyOiJvdSI7czo0OiJNYWluIjtzOjg6InVzZXJuYW1lIjtzOjc6InZhZ3JhbnQiO3M6ODoiYXV0aFR5cGUiO3M6NDoibGRhcCI7czo5OiJhdXRoX2xkYXAiO2E6Mzp7czo2OiJncm91cHMiO2E6MTp7aTowO3M6NDg6IkNOPURvbWFpbiBVc2VycyxDTj1Vc2VycyxEQz13dnUtYWQsREM9d3Z1LERDPWVkdSI7fXM6NjoidXNlckROIjtzOjkzOiJDTj12YWdyYW50IGJveCxPVT1zeXN0ZW1zR2VuZXJhdGVkQ291cnRlc3lBY2NvdW50cyxPVT1MaWJyYXJ5LE9VPU1haW4sREM9d3Z1LWFkLERDPXd2dSxEQz1lZHUiO3M6ODoidXNlcm5hbWUiO3M6NzoidmFncmFudCI7fX0='));

?>

<p>You are now logged in. Use the links below to navigate:</p>

<ul>
	<li><a href="/">MFCS</a></li>
</ul>