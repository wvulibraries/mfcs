<?php
include("../../../header.php");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
	header('Location: /index.php?permissionFalse');
}

try {

	$error = FALSE;

	if (objects::validID() === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}
}
catch(Exception $e) {
	log::insert("Data View: Object History: Error",0,0,$e->getMessage());
	errorHandle::errorMsg($e->getMessage());
	$error = TRUE;
}

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Object History -- </h1>
	</header>

	<nav id="breadcrumbs">
		<ul class="breadcrumb">
			<li><a href="{local var="siteRoot"}">Home</a></li>
			<li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>
			<!-- FLoat Right -->
			<?php if(mfcsPerms::isAdmin($engine->cleanGet['MYSQL']['formID'])){ ?>
			<li class="pull-right noDivider"><a href="{local var="siteRoot"}formCreator/index.php?id={local var="formID"}">Edit Form</a></li>
			<?php
			}
			if (!isnull($engine->cleanGet['MYSQL']['objectID']) and $revisions->hasRevisions($engine->cleanGet['MYSQL']['objectID'])) { ?>
				<li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/revisions/index.php?objectID={local var="objectID"}">Revisions</a></li>
			<?php } ?>
			<li class="pull-right noDivider"><a href="{phpself query="true"}&unlock=cancel">Cancel Edit &amp; Unlock object</a></li>
			<li class="pull-right noDivider"><a href="/data/object/history/">History</a></li>
		</ul>
	</nav>

	<div>

	</div>
</section>

<?php
$engine->eTemplate("include","footer");
?>