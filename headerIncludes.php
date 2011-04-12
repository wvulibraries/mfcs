<?php
global $engine;
?>

<script type="text/javascript" src="{engine var="jquery"}"></script>
<script type="text/javascript" src="{engine var="jqueryDate"}"></script>
<script type="text/javascript" src="{engine var="selectBoxJS"}"></script>
<script type="text/javascript" src="{engine var="convert2TextJS"}"></script>
<script type="text/javascript" src="{engine var="engineListObjJS"}"></script>
<script type="text/javascript" src="{engine var="engineWYSIWYGJS"}"></script>
<script type="text/javascript" src="{engine var="tiny_mce_JS"}"></script>

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
