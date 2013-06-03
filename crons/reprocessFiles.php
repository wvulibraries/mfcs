#!/usr/bin/php
<?php
require_once '/home/dev.systems.lib.wvu.edu/public_html/mfcs/header.php';
require_once '/home/dev.systems.lib.wvu.edu/public_html/mfcs/includes/functions.php';

$pid = getmypid();

do {

	$sql = sprintf("SELECT `ID`, `formID` FROM `fileProcessQueue` WHERE `pid` IS NULL ORDER BY `ID` ASC LIMIT 1");
	$sqlResult = $engine->openDB->query($sql);
	$numRows   = $sqlResult['numRows'];

	if (!$sqlResult['result'] || $sqlResult['affectedRows'] == 0) {
		errorHandle::newError("No forms queued.",errorHandle::DEBUG);
		break;
	}

	$engine->openDB->transBegin();

	$row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC);

	$sql = sprintf("UPDATE `fileProcessQueue` SET `pid`='%s' WHERE `ID`='%s' AND `pid` IS NULL LIMIT 1",
		$engine->openDB->escape($pid),
		$engine->openDB->escape($row['ID'])
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result'] || $sqlResult['affectedRows'] == 0) {
		errorHandle::newError("Failed to update PID.",errorHandle::DEBUG);
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
		break;
	}


	$form    = forms::get($row['formID']);
	$objects = objects::getAllObjectsForForm($form['ID']);

	// Loop though each object, then each field looking for file types
	foreach ($objects as $object) {
		foreach ($form['fields'] as $field) {
			if ($field['type'] == 'file') {
				// Stored file information
				$fileArray = $object['data'][ $field['name'] ];

				// Re-Process files
				try {
					files::processObjectFiles($fileArray['uuid'], $field);
				}
				catch (Exception $e) {
					errorHandle::newError($e->getMessage(),errorHandle::DEBUG);
					$engine->openDB->transRollback();
					$engine->openDB->transEnd();
					break;
				}
			}
		}
	}

	$sql = sprintf("DELETE FROM `fileProcessQueue` WHERE ID='%s' AND `pid`='%s' LIMIT 1",
		$engine->openDB->escape($row['ID']),
		$engine->openDB->escape($pid)
		);
	$sqlResult = $engine->openDB->query($sql);

	if (!$sqlResult['result'] || $sqlResult['affectedRows'] == 0) {
		errorHandle::newError("Failed to delete queued form: ".$row['formID'],errorHandle::DEBUG);
		$engine->openDB->transRollback();
		$engine->openDB->transEnd();
		continue;
	}

	$engine->openDB->transCommit();
	$engine->openDB->transEnd();

} while ($numRows > 0);
?>
