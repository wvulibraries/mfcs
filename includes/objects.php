<?php

class objects {

	public static function get($objectID=NULL) {

		if (isnull($objectID)) {
			return self::getObjects();
		}

		$mfcs      = mfcs::singleton();
		$cachID    = "getObject:".$objectID;
		$cache     = $mfcs->cache("get",$cachID);

		if (!isnull($cache)) {
			return($cache);
		}

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT * FROM `objects` WHERE `ID`='%s'",
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ", errorHandle::DEBUG);
			return FALSE;
		}

		$object = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
		$object['data'] = decodeFields($object['data']);

		if ($object['data'] === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return FALSE;
		}

		$cache = $mfcs->cache("create",$cachID,$object);
		if ($cache === FALSE) {
			errorHandle::newError(__METHOD__."() - unable to cache object", errorHandle::DEBUG);
		}

		return $object;
	}

	public static function getObjects($start=0,$length=NULL) {

		if (!validate::integer($start)) {
			errorHandle::newError(__METHOD__."() - start point not an integer", errorHandle::DEBUG);
			errorHandle::errorMsg("Not a valid Range");
			return(FALSE);
		}

		if (!isnull($length) && !validate::integer($length)) {
			errorHandle::newError(__METHOD__."() - length not an integer", errorHandle::DEBUG);
			errorHandle::errorMsg("Not a valid Range");
			return(FALSE);
		}

		$engine = EngineAPI::singleton();

		if (!isnull($length)) {
			$start = $engine->openDB->escape($start);
			$lengt = $engine->openDB->escape($length);
		}

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT `ID` FROM `objects` %s",
			(!isnull($length))?sprintf("LIMIT %s,%s",$start,$length):""
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::get($row['ID']);
		}

		return $objects;

	}

	public static function getChildren($objectID) {
		if (!validate::integer($objectID)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT `ID` FROM `objects` WHERE `parentID`='%s'",
			$engine->openDB->escape($objectID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$children = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$children[] = self::get($row['ID']);
		}

		return($children);
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

			if ($row['data'] === FALSE) {
				errorHandle::errorMsg("Error retrieving objects.");
				return FALSE;
			}

			$objects[] = $row;

		}

		return $objects;
	}

	public static function checkObjectInForm($formID,$objectID) {
		$object = self::get($objectID);

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