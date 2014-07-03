<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/css/mfcs.css">
<script type="text/javascript" src="{local var="siteRoot"}includes/js/mfcs.js"></script>

<script type="text/javascript" src="{local var="siteRoot"}includes/js/jquery-ui-1.10.1.custom.min.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/js/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="{local var="siteRoot"}includes/js/jquery.tablesorter.min.js"></script>

<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2.css">
<link rel="stylesheet" type="text/css" href="{local var="siteRoot"}includes/select2-3.5.0/select2-bootstrap.css">
<script type="text/javascript" src="{local var="siteRoot"}includes/select2-3.5.0/select2.min.js"></script>

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
</script>
