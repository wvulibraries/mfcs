<?php
include("../header.php");

$ID = isset($engine->cleanGet['MYSQL']['id']) ? $engine->cleanGet['MYSQL']['id'] : NULL;

if (isset($engine->cleanPost['MYSQL']["insert"])) {
	if (!isset($engine->cleanPost['MYSQL']['name']) || is_empty($engine->cleanPost['MYSQL']['name'])) {
		errorHandle::errorMsg("Name field is required.");
	}
	else {
		$sql = sprintf("INSERT INTO `watermarks` (`name`,`data`) VALUES ('%s','%s')",
			$engine->cleanPost['MYSQL']['name'],
			addslashes(file_get_contents($_FILES['image']['tmp_name']))
			);
		$sqlResult = $engine->openDB->query($sql);

		if ($sqlResult['result']) {
			$ID = $sqlResult['id'];
			header("Location: ".$_SERVER['PHP_SELF'].'?id='.$ID);
		}

		errorHandle::errorMsg("Failed to add watermark.");
	}
}
else if (isset($engine->cleanPost['MYSQL']["update"])) {
	if (!isset($engine->cleanPost['MYSQL']['name']) || is_empty($engine->cleanPost['MYSQL']['name'])) {
		errorHandle::errorMsg("Name field is required.");
	}
	else {
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
			errorHandle::errorMsg("Failed to update watermark.");
		}
	}
}
else if (isset($engine->cleanPost['MYSQL']["delete"])) {
	$sql = sprintf("DELETE FROM `watermarks` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($ID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		header("Location: ".$_SERVER['PHP_SELF']);
	}
	errorHandle::errorMsg("Failed to delete watermark.");
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

            $tmp .= sprintf('<li><a href="?id=%s">%s<br><img src="data:image/%s;base64,%s"></a></li>',
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
	localVars::add("submitBtn",'<button type="submit" name="update" class="btn">Update</button><button type="submit" name="delete" class="btn">Delete</button>');

	$sql = sprintf("SELECT * FROM `%s` WHERE ID='%s' LIMIT 1",
		$engine->openDB->escape($engine->dbTables("watermarks")),
		$engine->openDB->escape($ID)
		);
	$sqlResult = $engine->openDB->query($sql);

	if ($sqlResult['result']) {
		$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

		localVars::add("nameVal",$row['name']);
	}

}
else {
	localVars::add("headerText","Add Watermark");
}

localVars::add("results",displayMessages());

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Manage Watermarks</h1>
	</header>

	{local var="results"}

	<section>
		<header>
			<h2>{local var="headerText"}</h2>
		</header>

		<form action="{phpself query="true"}" method="post" class="form-horizontal" enctype="multipart/form-data">
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

			<button type="submit" name="insert" class="btn">Insert</button>
			{local var="submitBtn"}
		</form>
	</section>

	<hr />

	<section>
		<header>
			<h2>Edit Watermarks</h2>
		</header>

		{local var="existingWatermarks"}
	</section>
</section>

<?php
$engine->eTemplate("include","footer");
?>