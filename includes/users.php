<?php

class users {

	/**
     * Get a user field
     *
     * @author David Gersting
     * @param string $name The name of the user field
     * @param mixed $default If no field value found, return this
     * @return mixed
     */
    public static function user($name,$default=NULL){
        return (isset(mfcs::$user[$name]) and !empty(mfcs::$user[$name]))
            ? mfcs::$user[$name]
            : $default;
    }

	public static function loadProjects() {

		$engine = EngineAPI::singleton();

		$currentProjects = array();
		$sql = sprintf("SELECT projects.ID,projectName FROM `projects` LEFT JOIN users_projects ON users_projects.projectID=projects.ID WHERE users_projects.userID=%s",
			$engine->openDB->escape(self::user('ID'))
			);
		$sqlResult = $engine->openDB->query($sql);
		if (!$sqlResult['result']) {
			errorHandle::newError("Failed to load user's projects ({$sqlResult['error']})", errorHandle::HIGH);
			errorHandle::errorMsg("Failed to load your current projects.");
		}
		else {
			while ($row = mysql_fetch_assoc($sqlResult['result'])) {
				$currentProjects[ $row['ID'] ] = $row['projectName'];
			}
		}

		return $currentProjects;

	}

}

?>