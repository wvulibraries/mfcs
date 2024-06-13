<?php 

// if (strtolower($_SERVER['SERVER_NAME']) != "localhost") {
// 	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
// 	print "Page not found.";
// 	die();
// }

require 'engineInclude.php';

$_SESSION = unserialize(base64_decode('YTo2OntzOjQ6IkNTUkYiO3M6MzI6Ijc0MmQ5MDkzNGFiNDVkNDFkNmFkZDA1OTkwZTgwMTMwIjtzOjY6Imdyb3VwcyI7YToyOntpOjA7czoxMjoiRG9tYWluIFVzZXJzIjtpOjE7czoxODoibGlicmFyeUdyb3VwX3N0YWZmIjt9czoyOiJvdSI7czo0OiJNYWluIjtzOjg6InVzZXJuYW1lIjtzOjY6ImRvY2tlciI7czo4OiJhdXRoVHlwZSI7czo0OiJsZGFwIjtzOjk6ImF1dGhfbGRhcCI7YTozOntzOjY6Imdyb3VwcyI7YToxOntpOjA7czo0ODoiQ049RG9tYWluIFVzZXJzLENOPVVzZXJzLERDPXd2dS1hZCxEQz13dnUsREM9ZWR1Ijt9czo2OiJ1c2VyRE4iO3M6OTI6IkNOPWRvY2tlciBkZXYsT1U9c3lzdGVtc0dlbmVyYXRlZENvdXJ0ZXN5QWNjb3VudHMsT1U9TGlicmFyeSxPVT1NYWluLERDPXd2dS1hZCxEQz13dnUsREM9ZWR1IjtzOjg6InVzZXJuYW1lIjtzOjY6ImRvY2tlciI7fX0='));

// $_SESSION["username"] = "docker";
// $_SESSION["auth_ldap"]["username"] = "docker";
// $_SESSION['auth_ldap']['userDN'] = "CN=docker dev,OU=systemsGeneratedCourtesyAccounts,OU=Library,OU=Main,DC=wvu-ad,DC=wvu,DC=edu";

// $new_session_data = base64_encode(serialize($_SESSION));
// echo $new_session_data;
// die();
?>

<p>You are now logged in. Use the links below to navigate:</p>

<ul>
	<li><a href="/">MFCS</a></li>
</ul>