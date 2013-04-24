<?php

class projects {

    public static function validID($id) {

        if (!validate::integer($id)) {
            return FALSE;
        }

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
        while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)){

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

        $project = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
        if (!isempty($project['forms']) && ($project['forms'] = decodeFields($project['forms'])) === FALSE) {
            return FALSE;
        }
        if (!isempty($project['groupings']) && ($project['groupings'] = decodeFields($project['groupings'])) === FALSE) {
            return FALSE;
        }

        return $project;

    }

    // $selected is an array of projectIDs
    public static function generateProjectCheckList($selected=array()) {

    	if (!is_array($selected)) {
    		return(FALSE);
    	}

        $allProjects      = projects::getProjects();

        $output = "";
        foreach ($allProjects as $project) {

            $output .= sprintf('<label class="checkbox" for="%s"><input type="checkbox" id="%s" name="projects[]" value="%s"%s> %s</label>',
            	htmlSanitize("project_".$project['ID']),                           // for=
            	htmlSanitize("project_".$project['ID']),                           // id=
            	htmlSanitize($project['ID']),                                      // value=
            	(in_array($project['ID'], $selected)) ? " checked" : "", // checked or not
           		 htmlSanitize($project['projectName'])                              // label text
            );
        }

        return $output;

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
        while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
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
        while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
            if ($form === TRUE) {
                if (($formIDs[$row['formID']] = forms::get($row['formID'])) === FALSE) {
                    return(FALSE);
                }
            }
            else {
                $formIDs[] = $row['formID'];
            }
        }

        return $formIDs;

    }

}

?>