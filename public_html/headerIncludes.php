<!-- <link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/css/mfcs.css">
 -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}css/main.css">

<!-- <script type="text/javascript" src="{local var="siteRoot"}includes/js/mfcs.js"></script> -->

<!-- Refactored Managable JS -->
<script type="text/javascript" src="{local var="siteRoot"}includes/js/build/production.min.js"></script>
<!--  <script type="text/javascript" src="{local var="siteRoot"}includes/js/build/production.js"></script> -->


<?php
// Pages that need fine uploader
$path = parse_url(localVars::get("siteRoot"),PHP_URL_PATH);
$fineuploader = array(
	$path."dataEntry/object.php",
	$path."dataView/object.php",
	);
?>

<script>
	var siteRoot  = '{local var="siteRoot"}';
	var csrfToken = '{engine name="csrf" insert="false"}';
	var userCurrentProjects = '{local var="userCurrentProjectsJSON"}';
</script>
