<?php

class users {

	public static function loadProjects() {

		$engine = EngineAPI::singleton();

		$currentProjects = array();
		$sql = sprintf("SELECT projects.ID,projectName FROM `projects` LEFT JOIN users_projects ON users_projects.projectID=projects.ID WHERE users_projects.userID=%s",
			$engine->openDB->escape(mfcs::user('ID'))
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