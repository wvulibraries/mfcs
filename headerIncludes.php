<?php
global $engine;
?>

<script type="text/javascript" src="/engineIncludes/jquery.ui.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-expose.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-validate.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/jquery-ui.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/functions.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/dynamic.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/validate.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="{local var="siteRoot"}includes/date_input.css" />
<link rel="stylesheet" type="text/css" media="screen" href="{local var="siteRoot"}includes/stylesheet.css" />

<?php
recurseInsert("includes/showField.php","php");
recurseInsert("includes/phpFunctions.php","php");
?>
