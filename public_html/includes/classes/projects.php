<?php

class projects {

	public static function validID($id) {
		if (!validate::integer($id)) return FALSE;
			return TRUE;
	}

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
	public static function getProjects($orderBy='projectName ASC'){

		// Clean and process $orderBy
		$orderBy = !is_empty($orderBy) ? "ORDER BY ".mfcs::$engine->openDB->escape($orderBy) : '';

		// Build SQL
		$sql = sprintf('SELECT `ID` FROM `projects` %s',
			$orderBy
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if(!$sqlResult['result']){
			errorHandle::newError(__METHOD__."() - MySQL Error ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$projects = array();
		while($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {

			if (($projects[] = self::get($row['ID'])) === FALSE) {
				return FALSE;
			}

		}
		return $projects;
	}


	/**
	 * returns the database object for the project ID. If no projectID is provided,
	 * returns an array of all the projects, using getProject method defaults
	 * we need to add caching to this, once caching is moved from EngineCMS to EngineAPI
	 *
	 * @author Michael Bond
	 * @param integer $projectID MySQL ID of the project to get
	 * @return array
	 */
	public static function get($projectID=NULL) {

		if (isnull($projectID)) {
			return self::getProjects();
		}

		$sql       = sprintf("SELECT * FROM `projects` WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($projectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$project = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC);
		if (!isempty($project['forms']) && ($project['forms'] = decodeFields($project['forms'])) === FALSE) {
			return FALSE;
		}
		if (!isempty($project['groupings']) && ($project['groupings'] = decodeFields($project['groupings'])) === FALSE) {
			return FALSE;
		}

		return $project;

	}

	// $selected is an array of projectIDs
	public static function generateProjectCheckList($selected=array(),$formID=NULL) {

		if (!is_array($selected)) {
			return(FALSE);
		}

		$allProjects      = projects::getProjects();

		if (!isnull($formID)) {
			$forms_projects   = forms::getProjects($formID);
		}

		$output = "";
		foreach ($allProjects as $project) {

			if (!isnull($formID) && !in_array($project['ID'], $forms_projects)) {
				continue;
			}

			$output .= sprintf('<li><label class="checkbox" for="%s"><input type="checkbox" id="%s" name="projects[]" value="%s"%s> %s</label></li>',
				htmlSanitize("project_".$project['ID']),                           // for=
				htmlSanitize("project_".$project['ID']),                           // id=
				htmlSanitize($project['ID']),                                      // value=
				(in_array($project['ID'], $selected)) ? " checked" : "", // checked or not
				 htmlSanitize($project['projectName'])                              // label text
			);
		}

		return "<ul class='checkboxList'>$output</ul>";

	}

	// returns an array with all the projects that an object belongs too
	public static function getAllObjectProjects($objectID) {

		$engine = EngineAPI::singleton();

		$sql       = sprintf("SELECT projectID FROM `objectProjects` WHERE `objectID`='%s'",
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$projects = array();
		while ($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
			if (($projects[] = self::get($row['projectID'])) === FALSE) {
				return FALSE;
			}
		}

		return $projects;

	}

	// get all the formIDs that belong to a project
	// if $form = TRUE, returns the form instead.
	public static function getForms($projectID,$form=FALSE) {

		$sql       = sprintf("SELECT `formID` FROM `forms_projects` WHERE `projectID`='%s'",
			mfcs::$engine->openDB->escape($projectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$formIDs = array();
		while($row       = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
			if ($form === TRUE) {
				if (($formIDs[$row['formID']] = forms::get($row['formID'])) === FALSE) {
					return FALSE;
				}
			}
			else {
				$formIDs[] = $row['formID'];
			}
		}

		return $formIDs;

	}

	public static function title($projectID) {

		$sql       = sprintf("SELECT `projectName` from `projects` WHERE `ID`='%s' LIMIT 1",mfcs::$engine->openDB->escape($projectID));
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$row       = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC);

		return (isset($row['projectName']))?$row['projectName']:"Project Not Found";

	}

  public static function get_project_idnos($projectID) {

    $sql = sprintf("select `objects`.`idno` from `objectProjects` left join `objects` on `objects`.`ID`=`objectProjects`.`objectId` WHERE `objects`.`metadata`='0' AND `objectProjects`.`projectId`='%s'",
        mfcs::$engine->openDB->escape($projectID)
    );
    $sqlResult = mfcs::$engine->openDB->query($sql);

    if (!$sqlResult['result']) {
      errorHandle::newError(__METHOD__."() - getting all object IDNOs for project: ".$projectID, errorHandle::DEBUG);
      return FALSE;
    }

    $idnos = array();
    while($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
      $idnos[] = $row['idno'];
    }
    return ($idnos);
  }

}

?>