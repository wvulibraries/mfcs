<?php

class objects {

	public static function validID($required = FALSE,$objectID=NULL) {
		$engine = EngineAPI::singleton();

		// Validates $objectID if it is passed in
		if (!isnull($objectID) && validate::integer($objectID)) {
			return TRUE;
		}
		// Handles validation from query string
		else if (isset($engine->cleanGet['MYSQL']['objectID'])) {
			if (!is_empty($engine->cleanGet['MYSQL']['objectID'])
				&& validate::integer($engine->cleanGet['MYSQL']['objectID'])) {

				return TRUE;

			}
 
		}
		else if (!isset($engine->cleanGet['MYSQL']['objectID']) && $required === FALSE) {
			$engine->cleanGet['MYSQL']['objectID'] = NULL;
			return TRUE;
		}
		else {
			return FALSE;
		}

		return FALSE;

	}

	public static function get($objectID=NULL,$ignoreCache=FALSE) {
  
		if (isnull($objectID)) {
			return self::getObjects();
		}

		$mfcs      = mfcs::singleton();
		$cachID    = "getObject:".$objectID;
		$cache     = !$ignoreCache ? $mfcs->cache("get",$cachID) : NULL;

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

		if (($object['data'] = decodeFields($object['data'])) === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return FALSE;
		}

		$cache = $mfcs->cache("create",$cachID,$object);
		if ($cache === FALSE) {
			errorHandle::newError(__METHOD__."() - unable to cache object", errorHandle::DEBUG);
		}

		return $object;
	}

	public static function getObjects($start=0,$length=NULL,$metadata=TRUE) {

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
			$start  = $engine->openDB->escape($start);
			$length = $engine->openDB->escape($length);
		}

		$sql       = sprintf("SELECT `ID` FROM `objects` %s %s",
			($metadata === FALSE)?"WHERE `metadata`='0'":"",
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

		$sql       = sprintf("SELECT `ID` FROM `objects` WHERE `formID`='%s'",
			$engine->openDB->escape($formID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::get($row['ID']);
		}

		return $objects;
	}

	public static function getAllObjectsForProject($projectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT `objects`.`ID` as `ID` FROM `objects` LEFT JOIN `objectProjects` ON `objectProjects`.`objectID`=`objects`.`ID` WHERE `objectProjects`.`projectID`='%s'",
			$engine->openDB->escape($projectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::get($row['ID']);
		}

		return $objects;

	}

	// objects is an array of objects
	// sorts based on the title field defined for the form that created the objects
	// objects are assumed to be from the same form
	// 
	//  if $sortField is blank, will sort on the title field. sortField overrides that. must be a valid 
	//  field in the data index
	
	public static function sort($objects,$sortField=NULL) {

		if (isnull($sortField)) {
			$formID    = $objects[0]['formID'];
			$sortField = forms::getObjectTitleField($formID);
		}

		$tmp = Array(); 

		foreach($objects as &$object) {
			$tmp[] = &$object['data'][$sortField]; 
		}

		array_multisort($tmp, $objects); 

		return $objects;

	}

	public static function checkObjectInForm($formID,$objectID) {
		$object = self::get($objectID);

		if ($object === FALSE) {
			return(FALSE);
		}

		if (isset($object['formID']) && $object['formID'] == $formID) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	// $metadata needs to be an associative array that contains key value pairs that 
	// match what a cleanPost would give. Data is expected to be RAW, will be sanitized 
	// when it gets put into place.
	// 
	// puts all the needed stuff into $cleanPost, then submits it using forms::submit
	public static function add($formID,$metadata,$objectID = NULL) {

		if (!is_array($metadata)) {
			errorHandle::newError(__METHOD__."() - : metedata is not array", errorHandle::DEBUG);
			return FALSE;
		}

		if (!self::validID(FALSE,$objectID)) {
			errorHandle::newError(__METHOD__."() - : invalid objectID provided", errorHandle::DEBUG);
			return FALSE;
		}

		// populate cleanPost
		foreach ($metadata as $I=>$V) {
			http::setPost($I,$V);
		}

		// submit to forms::submit 
		return forms::submit($formID,$objectID,TRUE);

	}

	public static function update($formID,$metadata,$objectID) {

		if (!is_array($metadata)) {
			errorHandle::newError(__METHOD__."() - : metedata is not array", errorHandle::DEBUG);
			return FALSE;
		}

		return self::add($formID,$metadata,$objectID);
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

		$sql = sprintf("SELECT `projectID` FROM `objectProjects` WHERE objectID='%s'",
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

	public static function deleteAllProjects($objectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("DELETE FROM `objectProjects` WHERE `objectID`='%s'",
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function addProject($objectID,$projectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("INSERT INTO `objectProjects` (`objectID`,`projectID`) VALUES('%s','%s')",
			$engine->openDB->escape($objectID),
			$engine->openDB->escape($projectID)
			);
		$sqlResult = $engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

	public static function addProjects($objectID,$projects) {

		if (!is_array($projects)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton();

		$result = $engine->openDB->transBegin("objectProjects");

		foreach ($projects as $projectID) { 
			if (self::addProject($objectID,$projectID) === FALSE) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();
				return FALSE;
			}
		}

		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		return TRUE;

	}

}

?>