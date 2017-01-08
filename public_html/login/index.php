<?php
require_once("/home/mfcs.lib.wvu.edu/phpincludes/engine/engineAPI/latest/engine.php");
$engine = EngineAPI::singleton();

errorHandle::errorReporting(errorHandle::E_ALL);

$engine->dbConnect("database","mfcs",TRUE);

require_once "../includes/index.php";
mfcs::singleton();

// Login Type
$loginType = "ldap";
// Domain for ldap login
$engine->localVars("domain","wvu-ad");

$authFail  = FALSE; // Authorization to the current resource .. we may end up not using this
$loginFail = FALSE; // Login Success/Failure

if (isset($engine->cleanGet['HTML']['page'])) {
	$page = $engine->cleanGet['HTML']['page'];
	if (isset($engine->cleanGet['HTML']['qs'])) {
		$qs = urldecode($engine->cleanGet['HTML']['qs']);
		$qs = preg_replace('/&amp;amp;/','&',$qs);
		$qs = preg_replace('/&amp;/','&',$qs);
	}
	else {
		$qs = "";
	}
}

//Login processing:
if (isset($engine->cleanPost['HTML']['loginSubmit'])) {
	if (!isset($engine->cleanPost['HTML']['username']) || !isset($engine->cleanPost['HTML']['password'])) {
		$authFail  = TRUE;
		$loginFail = TRUE;
	}
	else {

		global $engineVars;
		if ($engine->login($loginType)) {
			log::insert("Login");
            if(isset($engine->cleanGet['HTML']['url'])) {
				header("Location: ".$engine->cleanGet['HTML']['URL'] ) ;
			}
			else {
				if (isset($page)) {
					header("Location: ".$page."?".$qs );
				}
				else {
					header("Location: ".$engineVars['WEBROOT'] );
				}

			}
		}
		else {
			log::insert("Login Failure");
			$loginFail = TRUE;
		}

	}

}

?>
<!-- nagios check -->
<html>
<head>
	<title>MFCS Login</title>
	<link rel="stylesheet" type="text/css" href="/css/login.css"/>
</head>

<body>

<!-- <header>
	<h1>Metadata Form Creation System</h1>
</header> -->


<div id="loginBox">

	<form name="loginForm" action="{phpself query="false"}<?php if(isset($page)){ echo "?page=".$page; if(isset($qs)) { echo "&qs=".(urlencode($qs)); } } ?>" method="post">
		<div class="formHeader">
			<h2> MFCS Login</h2>
		</div>
		{engine name="insertCSRF"}
		<?php
	if($loginFail) {
		print "<div class='error'><p>Login Failed. User name or Password is incorrect.</p></div>";
	}
	if(isset($page)) {
		print "<div class='error'><p>You are either not logged in or do not have access to the requested page.</p></div>";
	}
	?>


		<label for="username" class="hidden">Username:</label>
		<input type="text" name="username" id="username" class="styledInput" value="" autofocus="autofocus" placeholder="username" /> <br /><br />
		<label for="password" class="hidden">Password:</label>
		<input type="password" name="password" id="password" placeholder="password" class="styledInput"  value="" onkeypress="capsLockCheck(event);"/> <span id="capsLock" style="display:none;">Caps Lock is On</span>
		<br/>
		<div class="alignSubmit">
			<input type="submit" class="styledInput" name="loginSubmit" id="submitButton" value="Login" />
		</div>
	</form>
</div>

<div class="mfcs-logo">
	<img src="/images/mfcs.png" alt="Metadata Form Creation System" />
</div>

</body>
</html>
