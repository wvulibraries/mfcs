<?php

class objects {

	public static function get($objectID) {
		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT * FROM `objects` WHERE `ID`='%s'",
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
			return FALSE;
		}

		return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
	}

	public static function getAllObjectsForForm($formID) {
		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT * FROM `objects` WHERE `formID`='%s'",
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

			$row['data'] = decodeFields($row['data']);
			$objects[] = $row;

		}

		return $objects;
	}

	public static function checkObjectInForm($formID,$objectID) {
		$object = getObject($objectID);

		if ($object['formID'] == $formID) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Retrieve a list of projects that a given object has been added to
	 *
	 * @param string $objectID The ID of the object
	 * @return array
	 * @author Scott Blake
	 **/
	public static function getProjects($objectID) {
		$engine = EngineAPI::singleton();
		$return = array();

		$sql = sprintf("SELECT `projectID` FROM `%s` WHERE objectID='%s'",
			$engine->openDB->escape($engine->dbTables("objectProjects")),
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return array();
		}

		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$return[] = $row['projectID'];
		}

		return $return;
	}

}

?>