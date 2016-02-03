<?php
include("../../header.php");
//Permissions Access
if(!mfcsPerms::evaluatePageAccess(2)){
	header('Location: /index.php?permissionFalse');
}

$ID = isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : NULL;

try {
	if (isset($engine->cleanPost['MYSQL']["insert"])) {

		log::insert("Admin: Insert Watermark");

		if (!isset($engine->cleanPost['MYSQL']['name']) || is_empty($engine->cleanPost['MYSQL']['name'])) {
			throw new Exception("Name field is required.");
		}

		$sql = sprintf("INSERT INTO `watermarks` (`name`,`data`) VALUES ('%s','%s')",
			$engine->cleanPost['MYSQL']['name'],
			addslashes(file_get_contents($_FILES['image']['tmp_name']))
			);
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			$ID = $sqlResult['id'];
			header("Location: ".$_SERVER['PHP_SELF'].'?id='.$ID);
		}

		throw new Exception("Failed to add watermark.");

	}
	else if (isset($engine->cleanPost['MYSQL']["update"])) {

		log::insert("Admin: Update Watermark");

		if (!isset($engine->cleanPost['MYSQL']['name']) || is_empty($engine->cleanPost['MYSQL']['name'])) {
			throw new Exception("Name field is required.");
		}

		$sql = sprintf("UPDATE `watermarks` SET `name`='%s'%s WHERE ID='%s' LIMIT 1",
			$engine->cleanPost['MYSQL']['name'],
			($_FILES['image']['size'] > 0) ? (", `data`='".addslashes(file_get_contents($_FILES['image']['tmp_name']))."'") : NULL,
			$engine->openDB->escape($ID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			errorHandle::successMsg("Successfully updated watermark.");
		}
		else {
			throw new Exception("Failed to update watermark.");
		}

	}
	else if (isset($engine->cleanPost['MYSQL']["delete"])) {

		log::insert("Admin: Delete Watermark");

		$sql = sprintf("DELETE FROM `watermarks` WHERE ID='%s' LIMIT 1",
			$engine->openDB->escape($ID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			header("Location: ".$_SERVER['PHP_SELF']);
		}
		throw new Exception("Failed to delete watermark.");
	}
}
catch (Exception $e) {
	errorHandle::errorMsg($e->getMessage());
}

// Get List of existing watermarks
$sql = sprintf("SELECT * FROM `watermarks` ORDER BY `name`");
$sqlResult = $engine->openDB->query($sql);

if ($sqlResult['result']) {
	$tmp = NULL;
	while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
		try{
			$i = new Imagick();
			$i->readImageBlob($row['data']);

			$tmp .= sprintf('<li><a href="?id=%s">
									<div class="imgOwner"> %s </div> <br/>
									<div class="watermark"> <img src="data:image/%s;base64,%s"> </div>
							  </a></li>',
				htmlSanitize($row['ID']),
				htmlSanitize($row['name']),
				strtolower($i->getImageFormat()),
				base64_encode($row['data'])
			);
		}catch (Exception $e){
			errorHandle::newError("readImageBlob failed - {$e->getMessage()}", errorHandle::HIGH);
			errorHandle::errorMsg("Failed to load watermark.");
		}
	}
	localVars::add("existingWatermarks",$tmp);
	unset($tmp);
}
// Get List of existing watermarks


if (!isnull($ID)) {
	localVars::add("headerText","Update Watermark");
	localVars::add("submitBtn",'<button type="submit" name="update" class="btn btn-primary">Update Watermark</button><button type="submit" name="delete" class="btn btn-danger">Delete Watermark</button>');

	$sql = sprintf("SELECT * FROM `watermarks` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($ID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

		localVars::add("nameVal",$row['name']);
	}

}
else {
	localVars::add("submitBtn",'<button type="submit" name="insert" class="btn btn-primary">Insert Watermark</button>');
	localVars::add("headerText","Add Watermark");
}

localVars::add("results",displayMessages());

log::insert("Admin: View Watermark Page");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1 class="page-title">Manage Watermarks</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}/admin/index.php">Admin Home</a></li>
	</ul>

	{local var="results"}

	<section>
		<header>
			<h2>{local var="headerText"}</h2>
		</header>

		<form action="{phpself query="true"}" method="post" class="form-horizontal watermarkForm" enctype="multipart/form-data">
			{engine name="csrf"}
			<input type="hidden" name="ID" value="{local var="employeeID"}" />

			<div class="control-group">
				<label class="control-label" for="name"><b>Name:</b></label>
				<div class="controls">
					<input type="text" name="name" value="{local var="nameVal"}">
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="image">Image:</label>
				<div class="controls">
					<input type="file" name="image" id="image" />
				</div>
			</div>

			{local var="submitBtn"}
		</form>
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Watermarks</h2>
			<p> Click on the watermark to edit it or name to edit it. </p>
		</header>
		<ul class="watermarks">
			{local var="existingWatermarks"}
		</ul>
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>
