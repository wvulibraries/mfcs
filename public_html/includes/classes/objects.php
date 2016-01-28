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

	// $range is an array. $range[0] is the start, $range[1], is the length. 
	// Applies LIMIT $start,$length to SQL query
	public static function getAllObjectsForForm($formID,$sortField=NULL,$metadata=TRUE,$range=NULL) {
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
 
 		// If a range is provided, and the range is an array, and the first 2 indexes are valid integers
 		// and start is >= 0, and length is greater than 0
		if (!isnull($range)              && 
			is_array($range)             && 
			validate::integer($range[0]) && 
			validate::integer($range[1]) &&
			$range[0] >= 0               &&
			$range[1] > 0
			) {

			$range_clause = sprintf(" LIMIT %s,%s",$range[0],$range[1]);

		}
		else {
			$range_clause = "";
		}

		$sql       = sprintf("SELECT * FROM `objects` WHERE `formID`='%s'%s%s",
			$engine->openDB->escape($formID),
			$sortField,
			$range_clause
			);

		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - getting all objects for form: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$objects = array();
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::buildObject($row,TRUE,$metadata);  
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

	// $sql is a complete sql statement, already sanitized. 
	public static function getObjectsForSQL($sql,$metadata=TRUE) {

		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		$objects = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$objects[] = self::buildObject($row,TRUE,$metadata);
		}

		return $objects;
		
	}

	public static function buildObject($row,$ignoreCache=FALSE,$metadata=TRUE) {

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

		if ($metadata !== FALSE) {

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
		}
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
				$tmp[] = strtolower(&$object['data'][$sortField]); 
			}
		}

		// @TODO after we can stop supporting 5.3 we should use the natural
		// sort flag to this (first available in 5.4) ... remove the strtolower
		// when switching to nat sort
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
		$sql       = sprintf("INSERT INTO `objects` (parentID,formID,data,metadata,modifiedTime,createTime,modifiedBy,createdBy) VALUES('%s','%s','%s','%s','%s','%s','%s','%s')",
			isset(mfcs::$engine->cleanPost['MYSQL']['parentID'])?mfcs::$engine->cleanPost['MYSQL']['parentID']:"0",
			mfcs::$engine->openDB->escape($formID),
			encodeFields($data),
			mfcs::$engine->openDB->escape($form['metadata']),
			time(),
			time(),
			mfcs::$engine->openDB->escape(users::user('ID')),
			mfcs::$engine->openDB->escape(users::user('ID'))
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
		if (self::insertObjectData($objectID,$data,$formID) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - inserting objects", errorHandle::DEBUG);
			return FALSE;
		}
		
	
		// if it is an object form (not a metadata form)
		// do the IDNO stuff
		if ($form['metadata'] == "0") {

			// the form is an object form, make sure that it has an ID field defined.
			if (($idnoInfo = forms::getFormIDInfo($formID)) === FALSE) {
				errorHandle::newError(__METHOD__."() - no IDNO field for object form.", errorHandle::DEBUG);
				return FALSE;
			}
		
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

			if (!self::updateIDNO($objectID,$idno)) {

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
		if (($currentProjects = users::loadProjects()) === FALSE) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();
			return FALSE;
		}
		foreach ($currentProjects as $projectID => $projectName) {
			if (forms::checkFormInProject($projectID,$formID) === TRUE) {
				if ((objects::addProject($objectID,$projectID)) === FALSE) {
					mfcs::$engine->openDB->transRollback();
					mfcs::$engine->openDB->transEnd();
					return FALSE;
				}
			}
		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;
	}

	public static function getIDNOForObjectID($objectID) {

		$object = self::get($objectID);

		return ($object)?$object['idno']:FALSE;

	}

	public static function hasFiles($objectID,$fieldName = NULL) {

		$object = self::get($objectID);

		if (isnull($fieldName)) {
			$form   = forms::get($object['formID']);

			foreach ($form['fields'] as $field) {
				if ($field['type'] == "file") {
					$fieldName = $field['name'];
				}
			}

		}

		// if the field name is null at this point, the form has no File objects,
		// therefore the object doesn't (as far as we care about. It may still have them
		// if the field was removed from the object after files had been uploaded.)
		if (isnull($fieldName)) {
			return FALSE;
		}

		if (isset($object['data'][$fieldName]['files']['archive']) && is_array($object['data'][$fieldName]['files']['archive']) && count($object['data'][$fieldName]['files']['archive']) > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	public static function update($objectID,$formID,$data,$metadata,$parentID=0,$modifiedTime=NULL) {

		if (!is_array($data)) {
			errorHandle::newError(__METHOD__."() - : data is not array", errorHandle::DEBUG);
			return FALSE;
		}

		// Get the current Form
		if (($form = forms::get($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
			return FALSE;
		}

		// the form is an object form, make sure that it has an ID field defined.
		if (($idnoInfo = forms::getFormIDInfo($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - no IDNO field for object form.", errorHandle::DEBUG);
			return FALSE;
		}

		// begin transactions
		$result = mfcs::$engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		// place old version into revision control
		// excluding metadata objects
		if ($metadata == 0) {
			$rcs    = revisions::create();
			$return = $rcs->insertRevision($objectID);

			if ($return !== TRUE) {

				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - unable to insert revisions", errorHandle::DEBUG);
				return FALSE;
			}
		}

		// insert new version
		$sql = sprintf("UPDATE `objects` SET `parentID`='%s', `data`='%s', `formID`='%s', `metadata`='%s', `modifiedTime`='%s', `modifiedBy`='%s' WHERE `ID`='%s'",
			isset(mfcs::$engine->cleanPost['MYSQL']['parentID'])?mfcs::$engine->cleanPost['MYSQL']['parentID']:mfcs::$engine->openDB->escape($parentID),
			encodeFields($data),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($metadata),
			(isnull($modifiedTime))?time():$modifiedTime,
			mfcs::$engine->openDB->escape(users::user('ID')),
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
		if (self::insertObjectData($objectID,$data,$formID) === FALSE) {
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

		// if it is an object form (not a metadata form)
		// do the IDNO stuff
		// We only have to do this if the IDNO is managed by the user
		if ($form['metadata'] == "0" && $idnoInfo['managedBy'] != "system") {

			// the form is an object form, make sure that it has an ID field defined.
			if (($idnoInfo = forms::getFormIDInfo($formID)) === FALSE) {
				errorHandle::newError(__METHOD__."() - no IDNO field for object form.", errorHandle::DEBUG);
				return FALSE;
			}

			$idno = (isset(mfcs::$engine->cleanPost['MYSQL']['idno']) && !isempty(mfcs::$engine->cleanPost['MYSQL']['idno']))?mfcs::$engine->cleanPost['MYSQL']['idno']:self::getIDNOForObjectID($objectID);

			if ($idno === FALSE || isempty($idno)) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				return FALSE;
			}

			if (!self::updateIDNO($objectID,$idno)) {

				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - updating the IDNO: ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;

			}

		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;

	}

	private static function updateIDNO($objectID,$idno) {
	// update the object with the new idno
		$sql       = sprintf("UPDATE `objects` SET `idno`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($idno),
			mfcs::$engine->openDB->escape($objectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - updating the IDNO: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

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

		// check locks. 
		if (!locks::check_for_update($objectID,"object")) {
			return FALSE;
		}

		if ($engine->openDB->transBegin("objectProjects") !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		if (self::deleteAllProjects($objectID) === FALSE) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			throw new Exception("Error removing all projects from Object.");
		}

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

	public static function insertObjectData($objectID,$data,$formID) {

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

			$sql = sprintf("INSERT INTO `objectsData` (formID,objectID,fieldName,value,encoded) VALUES('%s','%s','%s','%s','%s')",
				mfcs::$engine->openDB->escape($formID),
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

	/**
	 * Checks to see if an object is already being edited. 
	 * @param  INT $objectID objectID of object to check lock status of
	 * @return BOOL  TRUE if object is locked, false otherwise. 
	 */
	public static function is_locked($objectID) {

		return locks::is_locked($objectID,"object");

	}

	/**
	 * Lock the current file
	 * @param  INT $objectID ID of object to Lock
	 * @return mixed           BOOL false if lock fails, otherwise lock ID. 
	 */
	public static function lock($objectID) {

		return locks::lock($objectID,"object");

	}

	/**
	 * Removes all locks on object
	 * @param  int $objectID object to unlock
	 * @return Book  true on success, false otherwise
	 */
	public static function unlock($objectID) {

		return locks::unlock($objectID,"object");

	}

}

?>