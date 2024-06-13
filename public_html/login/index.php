<?php
require_once("/home/mfcs.lib.wvu.edu/phpincludes/engine/engineAPI/latest/engine.php");
$engine = EngineAPI::singleton();

errorHandle::errorReporting(errorHandle::E_ALL);

$engine->dbConnect("database", "mfcs", TRUE);

require_once "../includes/index.php";
mfcs::singleton();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Login Type
$loginType = "ldap";

// Domain for ldap login
$engine->localVars("domain", "wvu-ad");

$authFail = FALSE; // Authorization to the current resource .. we may end up not using this
$loginFail = FALSE; // Login Success/Failure

if (isset($engine->cleanGet['HTML']['page'])) {
    $page = $engine->cleanGet['HTML']['page'];
    if (isset($engine->cleanGet['HTML']['qs'])) {
        $qs = urldecode($engine->cleanGet['HTML']['qs']);
        $qs = preg_replace('/&amp;amp;/', '&', $qs);
        $qs = preg_replace('/&amp;/', '&', $qs);
    } else {
        $qs = "";
    }
}

// Login processing:
if (isset($engine->cleanPost['HTML']['loginSubmit'])) {
    if (!isset($engine->cleanPost['HTML']['username']) || !isset($engine->cleanPost['HTML']['password'])) {
        $authFail = TRUE;
        $loginFail = TRUE;
    } else {
        global $engineVars, $loginFunctions;

        // Validate login function exists
        if (isset($loginFunctions[$loginType])) {
            $loginFunction = $loginFunctions[$loginType];
            $username = trim($engine->cleanPost['HTML']['username']);
            $password = $engine->cleanPost['HTML']['password'];

            // Call the appropriate login function
            if ($loginFunction($username, $password)) {
                log::insert("Login");
                if (isset($engine->cleanGet['HTML']['url'])) {
                    header("Location: " . $engine->cleanGet['HTML']['url']);
                    exit; // Ensure no further code is executed
                } else {
                    if (isset($page)) {
                        header("Location: " . $page . "?" . $qs);
                        exit; // Ensure no further code is executed
                    } else {
                        header("Location: " . $engineVars['WEBROOT']);
                        exit; // Ensure no further code is executed
                    }
                }
            } else {
                log::insert("Login Failure");
                $loginFail = TRUE;
            }
        } else {
            $loginFail = TRUE; // Login function not found
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
<div id="loginBox">
    <form name="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); if (isset($page)) { echo "?page=" . $page; if (isset($qs)) { echo "&qs=" . urlencode($qs); } } ?>" method="post">
        <div class="formHeader">
            <h2> MFCS Login</h2>
        </div>
        {engine name="insertCSRF"}
        <?php
        if ($loginFail) {
            echo "<div class='error'><p>Login Failed. Username or Password is incorrect.</p></div>";
        }
        if (isset($page)) {
            echo "<div class='error'><p>You are either not logged in or do not have access to the requested page.</p></div>";
        }
        ?>
        <label for="username" class="hidden">Username:</label>
        <input type="text" name="username" id="username" class="styledInput" value="" autofocus="autofocus" placeholder="username" /> <br /><br />
        <label for="password" class="hidden">Password:</label>
        <input type="password" name="password" id="password" placeholder="password" class="styledInput" value="" onkeypress="capsLockCheck(event);" /> <span id="capsLock" style="display:none;">Caps Lock is On</span>
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



