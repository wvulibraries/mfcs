<!DOCTYPE html>
<html lang="en">
<head>

	<title> {local var="pageHeader"} | WVU Libraries | West Virginia University</title>

    <!-- Meta Information -->
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="HandheldFriendly" content="True">

    <!-- Author, Description, Favicon, and Keywords -->
        <meta name="author" content="WVU Libraries | {local var="meta_authors"}">
        <meta name="description" content="{local var="meta_description"}">
        <meta name="keywords" content="{local var="meta_keywords"}">


		<!-- Favicon Information -->
				<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
				<link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
				<link rel="icon" type="image/png" href="/images/favicon-16x16.png" sizes="16x16">
				<link rel="manifest" href="/images/manifest.json">
				<link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
				<link rel="shortcut icon" href="/images/favicon.ico">
				<meta name="msapplication-config" content="/images/browserconfig.xml">
				<meta name="theme-color" content="#ffffff">


	 <!-- Project Specific Head Includes -->
		<?php recurseInsert("headerIncludes.php","php") ?>

</head>

<body>

 <!-- WebApp Header -->
    <?php recurseInsert("includes/appHeader.php","php") ?>

 <!-- Navigation -->
    <?php recurseInsert("includes/nav.php","php") ?>
