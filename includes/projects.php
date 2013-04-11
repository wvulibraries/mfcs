<?php

class projects {

	/**
	 * Get an array of available projects
	 *
	 * This method returns an array of all available projects from the database
	 *
	 * @author David Gersting
	 * @param string|array $fields An array or CSV of fields to include
	 * @param string $orderBy
	 * @return array
	 */
	public static function getProjects($fields='ID,projectName',$orderBy='projectName ASC'){
		// Clean and process $fields
		$fields = is_string($fields) ? explode(',', $fields) : $fields;
		foreach($fields as $k => $field){
			$fields[$k] = '`'.self::$engine->openDB->escape($field).'`';
		}

		// Clean and process $orderBy
		$orderBy = !is_empty($orderBy) ? "ORDER BY ".self::$engine->openDB->escape($orderBy) : '';

		// Build SQL
		$sql = sprintf('SELECT %s FROM `projects` %s',
			implode(',', $fields),
			$orderBy);
		$sqlResult = self::$engine->openDB->query($sql);
		if(!$sqlResult['result']){
			errorHandle::newError(__METHOD__."() - MySQL Error ".$sqlResult['error'], errorHandle::DEBUG);
			return array();
		}

		$results = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)){
			$results[] = $row;
		}
		return $results;
	}


	/**
	 * returns the database object for the project ID. If no projectID is provided,
	 * returns an array of all the projects, using getProject method defaults
	 *
	 * @todo Add caching to this, once caching is moved from EngineCMS to EngineAPI
	 * @author Michael Bond
	 * @param integer $projectID MySQL ID of the project to get
	 * @return array
	 */
	public static function get($projectID=NULL) {
		if (isnull($projectID)) {
			return self::getProjects();
		}

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s'",
			$engine->openDB->escape($projectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
	}

	/**
	 * returns the database object for the project ID. If no projectID is provided,
	 * returns an array of all the projects, using getProject method defaults
	 *
	 * @todo Add caching to this, once caching is moved from EngineCMS to EngineAPI
	 * @author Michael Bond
	 * @param integer $id MySQL ID of the project to check permissions
	 * @param  string $username If provided checks against $username, otherwise current logged in user
	 * @return boolean
	 */
	public static function checkPermissions($id,$username = NULL) {
		$engine = EngineAPI::singleton();

		if (isnull($username)) {
			$username = sessionGet("username");
		}

		$sql       = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.projectID='%s' AND users.username='%s'",
			$engine->openDB->escape($id),
			$engine->openDB->escape($username)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		if ((int)$row['COUNT(permissions.ID)'] > 0) {
			return TRUE;
		}

		return FALSE;
	}

}

?>