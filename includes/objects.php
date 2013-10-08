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

		if (!$ignoreCache && !isnull($objectID)) {
			$mfcs      = mfcs::singleton();
			$cachID    = "getObject:".$objectID;
			$cache     = $mfcs->cache("get",$cachID);

			if (!isnull($cache)) {
				return($cache);
			}
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

		$object = self::buildObject($object,$ignoreCache);

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

		$sql       = sprintf("SELECT * FROM `objects` %s %s",
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
			$objects[] = self::buildObject($row);
		}

		return $objects;

	}

	public static function getChildren($objectID) {
		if (!validate::integer($objectID)) {
			return FALSE;
		}

		$engine = EngineAPI::singleton(); 

		$sql       = sprintf("SELECT * FROM `objects` WHERE `parentID`='%s'",
			$engine->openDB->escape($objectID)
		);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$children = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$children[] = self::buildObject($row);;
		}

		return($children);
	}

	public static function getAllObjectsForForm($formID,$sortField=NULL) {
		$engine = EngineAPI::singleton();

		if (!isnull($sortField)) {
			// $sortField = sprintf(" ORDER BY `objects`.`ID`, LENGTH(%s), %s",
			$sortField = sprintf(" ORDER BY LENGTH(%s), %s",
				$sortField,
				$sortField
				);
		}
		else {
			// $sortField = " ORDER BY `objects`.`ID`";
			$sortField = " ORDER BY `objects`.`ID`";
		} 
 
		$sql       = sprintf("SELECT * FROM `objects` WHERE `formID`='%s'%s",
			$engine->openDB->escape($formID),
			$sortField
			);
		
		// $sql       = sprintf("SELECT * FROM `objects`, `objectsData` WHERE `objects`.`formID`='%s' AND `objects`.`ID`=`objectsData`.`objectID` %s",
		// 	$engine->openDB->escape($formID), 
		// 	$sortField
		// 	);

		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		// $objects = array();
		// $temp    = array();
		// $prevID  = "";
		// while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

		// 	if ($row['objectID'] != $prevID && $prevID != "") {
		// 		$objects[] = self::buildObject($temp,TRUE);
		// 		$temp = array();
		// 	} 

		// 	$temp[] = $row;
		// 	$prevID = $row['objectID'];

		// }
		// 
		$objects = array();
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::buildObject($row,TRUE);
		}

		return $objects;
	}

	public static function getAllObjectsForProject($projectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT `objects`.* FROM `objects` LEFT JOIN `objectProjects` ON `objectProjects`.`objectID`=`objects`.`ID` WHERE `objectProjects`.`projectID`='%s' ORDER BY LENGTH(`idno`), `idno`",
			$engine->openDB->escape($projectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::buildObject($row,TRUE);
		}

		return $objects;

	}

	public static function buildObject($row,$ignoreCache=FALSE) {

		if (!is_array($row)) {
			return FALSE;
		}


		if (!$ignoreCache) {
			$mfcs      = mfcs::singleton();
			$cachID    = "getObject:".$row['ID'];
			$cache     = $mfcs->cache("get",$cachID);

			if (!isnull($cache)) {
				return($cache);
			}
		}

		// @TODO sanity checking
		// we might want to do a little more sanity cheecking here.
		// does $data['data'] exist?
		// does it have the proper structure?
		// etc ...

		// **** Original way of getting data
		if (($row['data'] = decodeFields($row['data'])) === FALSE) {
			errorHandle::errorMsg("Error retrieving object.");
			return FALSE;
		} 

		// **** objectsData table method for getting data
		// if (($row['data'] = self::retrieveObjectData($row['ID'])) === FALSE) {
		// 	errorHandle::errorMsg("Error retrieving object.");
		// 	return FALSE;
		// }
		
		// **** objectsData, single query
		// $object = $row[0]; 
		// $data = array();
		// foreach ($row as $fragment) {
		// 	$data[$fragment['fieldName']] = ($fragment['encoded'] == "1")?decodeFields($fragment['value']):$fragment['value'];
		// }
		// $object['data'] = $data;
		// unset($object['fieldName']);
		// unset($object['value']);
		// $row = $object;

		if (!$ignoreCache) {
			$cache = $mfcs->cache("create",$cachID,$row);
			if ($cache === FALSE) {
				errorHandle::newError(__METHOD__."() - unable to cache object", errorHandle::DEBUG);
			}
		}

		return $row;

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

			if ($sortField == "idno") {
				$tmp[] = &$object['idno'];
			}
			else {
				$tmp[] = &$object['data'][$sortField]; 
			}
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

	// creates a new object and puts it in the database
	// we are assuming that all data is valid at this point
	public static function create($formID,$data,$metadata,$parentID=0,$modifiedTime=NULL,$createTime=NULL) {

		if (!is_array($data)) {
			errorHandle::newError(__METHOD__."() - : data is not array", errorHandle::DEBUG);
			return FALSE;
		}

		// Get the current Form
		if (($form = forms::get($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
			return FALSE;
		}

		// begin transactions
		$result = mfcs::$engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}
		
		// Insert into the database
		$sql       = sprintf("INSERT INTO `objects` (parentID,formID,data,metadata,modifiedTime,createTime) VALUES('%s','%s','%s','%s','%s','%s')",
			isset(mfcs::$engine->cleanPost['MYSQL']['parentID'])?mfcs::$engine->cleanPost['MYSQL']['parentID']:"0",
			mfcs::$engine->openDB->escape($formID),
			encodeFields($data),
			mfcs::$engine->openDB->escape($form['metadata']),
			time(),
			time()
			);

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		// Set the new object ID in a local variable
		$objectID = $sqlResult['id'];
		localvars::add("newObjectID",$objectID);

		// Insert into the new data table
		if (self::insertObjectData($objectID,$data) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - inserting objects", errorHandle::DEBUG);
			return FALSE;
		}
		
	
		// if it is an object form (not a metadata form)
		// do the IDNO stuff
		if ($form['metadata'] == "0") {

			// if the idno is managed by the system get a new idno
			if ($idnoInfo['managedBy'] == "system") {
				$idno = mfcs::$engine->openDB->escape(mfcs::getIDNO($formID));
			}
			// the idno is managed manually
			else {
				$idno = mfcs::$engine->cleanPost['MYSQL']['idno'];
			}

			if (isempty($idno)) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				return FALSE;
			}

			// update the object with the new idno
			$sql       = sprintf("UPDATE `objects` SET `idno`='%s' WHERE `ID`='%s'",
				$idno, // Cleaned above when assigned
				mfcs::$engine->openDB->escape($objectID)
			);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - updating the IDNO: ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

			// increment the project counter
			$sql       = sprintf("UPDATE `forms` SET `count`=`count`+'1' WHERE `ID`='%s'",
				mfcs::$engine->openDB->escape($form['ID'])
			);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - Error incrementing form counter: ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

		}

		// Update duplicate matching table
		if (duplicates::updateDupeTable($formID,$objectID,$data) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();
			errorHandle::newError(__METHOD__."() - updating dupe matching", errorHandle::DEBUG);
			return FALSE;
		}

		// Add it to the users current projects
		if ($newObject === TRUE) {
			if (($currentProjects = users::loadProjects()) === FALSE) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();
				return FALSE;
			}
			foreach ($currentProjects as $projectID => $projectName) {
				if (self::checkFormInProject($projectID,$formID) === TRUE) {
					if ((objects::addProject($objectID,$projectID)) === FALSE) {
						mfcs::$engine->openDB->transRollback();
						mfcs::$engine->openDB->transEnd();
						return FALSE;
					}
				}
			}
		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;
	}

	public static function update($objectID,$formID,$data,$metadata,$parentID=0,$modifiedTime=NULL) {

		if (!is_array($data)) {
			errorHandle::newError(__METHOD__."() - : data is not array", errorHandle::DEBUG);
			return FALSE;
		}

		// begin transactions
		$result = mfcs::$engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		// place old version into revision control
		$rcs = revisions::new();
		$return = $rcs->insertRevision($objectID);


		if ($return !== TRUE) {

			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - unable to insert revisions", errorHandle::DEBUG);
			return FALSE;
		}

		// insert new version
		$sql = sprintf("UPDATE `objects` SET `parentID`='%s', `data`='%s', `formID`='%s', `metadata`='%s', `modifiedTime`='%s' WHERE `ID`='%s'",
			isset(mfcs::$engine->cleanPost['MYSQL']['parentID'])?mfcs::$engine->cleanPost['MYSQL']['parentID']:"0",
			encodeFields($data),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($metadata),
			time(),
			mfcs::$engine->openDB->escape($objectID)
			); 

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}		

		// Insert into the new data table
		if (self::insertObjectData($objectID,$data) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - inserting objects", errorHandle::DEBUG);
			return FALSE;
		}

		// Update duplicate matching table
		if (duplicates::updateDupeTable($formID,$objectID,$data) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();
			errorHandle::newError(__METHOD__."() - updating dupe matching", errorHandle::DEBUG);
			return FALSE;
		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;

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

	public static function update_external($formID,$metadata,$objectID) {

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

	public static function retrieveObjectData($objectID) {



		$sql       = sprintf("SELECT * FROM `objectsData` WHERE `objectID`='%s'",
			mfcs::$engine->openDB->escape($objectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$data = array();
	
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$data[$row['fieldName']] = ($row['encoded'] == "1")?decodeFields($row['value']):$row['value'];
		}

		return($data);
	}

	public static function insertObjectData($objectID,$data) {

		if (!is_array($data)) {
			return FALSE;
		}

		if (mfcs::$engine->openDB->transBegin("objectsData") !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		// remove old data

		$sql       = sprintf("DELETE FROM `objectsData` WHERE `objectID`='%s'",
			$objectID
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		// insert new data
		foreach ($data as $I=>$V) {

			$encoded = 0;
			if (is_array($V)) {
				// encode it
				$V = encodeFields($V);
				$encoded = 1;
			}

			$sql = sprintf("INSERT INTO `objectsData` (objectID,fieldName,value,encoded) VALUES('%s','%s','%s','%s')",
				mfcs::$engine->openDB->escape($objectID),
				mfcs::$engine->openDB->escape($I),
				mfcs::$engine->openDB->escape($V),
				mfcs::$engine->openDB->escape($encoded)
				);

			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - ".$sql." -- ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}

		}

		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;
	}

}

?>