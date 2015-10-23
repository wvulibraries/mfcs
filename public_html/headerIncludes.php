<!-- <link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/css/mfcs.css">
 -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}css/main.css">

<!-- <script type="text/javascript" src="{local var="siteRoot"}includes/js/mfcs.js"></script> -->

<!-- Refactored Managable JS -->
<script type="text/javascript" src="{local var="siteRoot"}includes/js/build/production.min.js"></script>

<!-- Test Committ -->


<?php
// Pages that need fine uploader
$path = parse_url(localVars::get("siteRoot"),PHP_URL_PATH);
$fineuploader = array(
	$path."dataEntry/object.php",
	$path."dataView/object.php",
	);

if (in_array($_SERVER['SCRIPT_NAME'], $fineuploader)) {
	?>
	<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/css/fineuploader.css" />
	<script type="text/javascript" src="{local var="siteRoot"}includes/js/jquery.fineuploader.min.js"></script>
	<style>
		/* Fine Uploader
		-------------------------------------------------- */
		.qq-upload-list {
			text-align: left;
		}

		li.alert-success {
			background-color: #DFF0D8;
		}

		li.alert-error {
			background-color: #F2DEDE;
		}

		.alert-error .qq-upload-failed-text {
			display: inline;
		}
	</style>
	<?php
}
?>

<script>
	var siteRoot  = '{local var="siteRoot"}';
	var csrfToken = '{engine name="csrf" insert="false"}';
	var userCurrentProjects = '{local var="userCurrentProjectsJSON"}';
</script>
