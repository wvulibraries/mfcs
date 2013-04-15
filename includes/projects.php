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
    public static function getProjects($fields='ID,projectName',$orderBy='projectName ASC'){
        // Clean and process $fields
        $fields = is_string($fields) ? explode(',', $fields) : $fields;
        foreach($fields as $k => $field){
            $fields[$k] = '`'.mfcs::$engine->openDB->escape($field).'`';
        }

        // Clean and process $orderBy
        $orderBy = !is_empty($orderBy) ? "ORDER BY ".mfcs::$engine->openDB->escape($orderBy) : '';

        // Build SQL
        $sql = sprintf('SELECT %s FROM `projects` %s',
            implode(',', $fields),
            $orderBy);
        $sqlResult = mfcs::$engine->openDB->query($sql);
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

        return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

    }

    /**
     * returns the database object for the project ID. If no projectID is provided, 
     * returns an array of all the projects, using getProject method defaults
     * we need to add caching to this, once caching is moved from EngineCMS to EngineAPI
     *
     * @author Michael Bond
     * @param integer $id MySQL ID of the project to check permissions
     * @param  string $username If provided checks against $username, otherwise current logged in user
     * @return boolean
     */
    public static function checkPermissions($id,$username = NULL) {

    	if (isnull($username)) {
    		$username = sessionGet("username");
    	}

    	$sql       = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.projectID='%s' AND users.username='%s'",
            mfcs::$engine->openDB->escape($id),
            mfcs::$engine->openDB->escape($username)
    		);
    	$sqlResult = mfcs::$engine->openDB->query($sql);

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

}

?>