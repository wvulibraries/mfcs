<?php

class users {

	private static $user   = array();

	/**
     * Get a user field
     *
     * @author David Gersting
     * @param string $name The name of the user field
     * @param mixed $default If no field value found, return this
     * @return mixed
     */
    public static function user($name,$default=NULL){
        return (isset(self::$user[$name]) and !empty(self::$user[$name]))
            ? self::$user[$name]
            : $default;
    }

    /**
     * Set a user field
     *
     * @author Michael Bond
     * @param string $name The name of the user field
     * @param string $value The value of the user field
     * @return bool TRUE on success
     */
    public static function setField($name,$value) {
        $sql       = sprintf("UPDATE `users` SET `%s`='%s' WHERE `ID`='%s'",
            mfcs::$engine->openDB->escape($name),
            mfcs::$engine->openDB->escape($value),
            mfcs::$engine->openDB->escape(users::user('ID'))
            );
        $sqlResult = mfcs::$engine->openDB->query($sql);
        
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }
        
        return TRUE;
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
            return FALSE;
		}
		else {
			while ($row = mysqli_fetch_array($sqlResult['result'])) {
				$currentProjects[ $row['ID'] ] = $row['projectName'];
			}
		}

		return $currentProjects;

	}

    /**
     * Process the user
     *
     * This function retrieves the user data from the database and inserts a new user if necessary.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function processUser() {
        $engine = EngineAPI::singleton();
        $username = sessionGet('username');

        $sqlSelect = sprintf("SELECT * FROM users WHERE username='%s' LIMIT 1", $engine->openDB->escape($username));
        $sqlResult = $engine->openDB->query($sqlSelect);

        if (!$sqlResult) {
            errorHandle::newError(__METHOD__ . "() - Failed to execute SELECT query: " . $engine->openDB->error, errorHandle::DEBUG);
            return false;
        }


        // Check if any rows were returned
        if ($sqlResult['result']->num_rows == 0) {
            // No user found, add them!
            $sqlInsert = sprintf("INSERT INTO users (username) VALUES('%s')", $engine->openDB->escape($username));
            $insertResult = $engine->openDB->query($sqlInsert);

            if (!$insertResult) {
                errorHandle::newError(__METHOD__ . "() - Failed to insert new user: " . $engine->openDB->error, errorHandle::DEBUG);
                return false;
            }

            // Re-run the select query to fetch the newly inserted user
            $sqlResult = $engine->openDB->query($sqlSelect);
        }

        // Check again if we have a valid result
        if ($sqlResult && isset($sqlResult['result']) && $sqlResult['result']->num_rows > 0) {
            // Fetch the user data
            $userData = $sqlResult['result']->fetch_assoc();
            if ($userData) {
                // Assign the fetched user data to a separate variable
                $userDataResult = $userData;
                return true;
            }
        }

        errorHandle::newError(__METHOD__ . "() - Failed to fetch user data", errorHandle::DEBUG);
        return false;
    }
    
    // userID can be mysql ID or username
    /**
     * Get user data by ID or username
     *
     * @param mixed $userID The ID or username of the user
     * @return mixed|null The user data as an associative array, or null if not found
     */
    public static function get($userID) {
        $whereClause = '';
        if (validate::integer($userID)) {
            $whereClause = sprintf("WHERE `ID`='%s'",
                mfcs::$engine->openDB->escape($userID)
            );
        } else {
            $whereClause = sprintf("WHERE `username`='%s'",
                mfcs::$engine->openDB->escape($userID)
            );
        }
    
        $sql = sprintf("SELECT * FROM `users` %s LIMIT 1",
            $whereClause
        );
        $sqlResult = mfcs::$engine->openDB->query($sql);
    
        if ($sqlResult === false) {
            // Check for SQL error
            errorHandle::newError(__METHOD__."() - SQL Error: ".mysqli_error(mfcs::$engine->openDB), errorHandle::DEBUG);
            return null;
        }
    
        // Check if $sqlResult is a mysqli_result object
        if (!($sqlResult instanceof mysqli_result)) {
            errorHandle::newError(__METHOD__."() - Query result is not a valid mysqli_result object", errorHandle::DEBUG);
            return null;
        }
    
        // Fetch the result as an associative array
        $row = mysqli_fetch_assoc($sqlResult);
    
        if (!$row) {
            // No rows found
            errorHandle::newError(__METHOD__."() - No rows found for userID: $userID", errorHandle::DEBUG);
            return null;
        }
    
        return $row;
    }
    

    public static function getUsers() {

        $sql       = sprintf("SELECT `ID` FROM `users` ORDER BY `lastname`");
        $sqlResult = mfcs::$engine->openDB->query($sql);
        
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }
        
        $users = array();
        while($row = mysqli_fetch_array($sqlResult['result'])) {
            if (($user = self::get($row['ID'])) == FALSE) {
                return FALSE;
            }
            $users[] = $user;
        }

        return $users;

    }

    public static function updateUserProjects() {
        $currentProjectsIDs   = array_keys(sessionGet('currentProject'));
        $submittedProjectsIDs = isset(mfcs::$engine->cleanPost['MYSQL']['selectedProjects'])? mfcs::$engine->cleanPost['MYSQL']['selectedProjects']: array();

        try{
            // Delete project IDs that disappeared
            $deletedIDs = array_diff($currentProjectsIDs,$submittedProjectsIDs);
            if(sizeof($deletedIDs)){
                $deleteSQL = sprintf("DELETE FROM users_projects WHERE userID='%s' AND projectID IN (%s)",
                    users::user('ID'),
                    implode(',', $deletedIDs));
                $deleteSQLResult = mfcs::$engine->openDB->query($deleteSQL);
                if(!$deleteSQLResult['result']){
                    throw new Exception("MySQL Error - ".$deleteSQLResult['error']);
                }
            }

            // Add project IDs that appeared
            $addedIDs = array_diff($submittedProjectsIDs,$currentProjectsIDs);
            if(sizeof($addedIDs)){
                $keyPairs=array();
                foreach($addedIDs as $addedID){
                    $keyPairs[] = sprintf("('%s','%s')", users::user('ID'), $addedID);
                }
                $insertSQL = sprintf("INSERT INTO  users_projects (userID,projectID) VALUES %s", implode(',', $keyPairs));
                $insertSQLResult = mfcs::$engine->openDB->query($insertSQL);
                if(!$insertSQLResult['result']){
                    throw new Exception("MySQL Error - ".$insertSQLResult['error']);
                }
            }

            // If we get here either nothing happened, or everything worked (no errors happened)
            $result = array(
                'success'    => TRUE,
                'deletedIDs' => $deletedIDs,
                'addedIDs'   => $addedIDs
                );

        }catch(Exception $e){
            $result = array(
                'success'  => FALSE,
                'errorMsg' => $e->getMessage()
                );
        }

        return $result;
    }

}

?>