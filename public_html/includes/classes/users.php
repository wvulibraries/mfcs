<?php

class users {

	private static $user   = array();

    /**
     * Get a user field
     *
     * @param string $name The name of the user field
     * @param mixed $default If no field value found, return this
     * @return mixed
     */
    public static function user($name, $default = NULL) {
        return isset(self::$user[$name]) && !empty(self::$user[$name]) ? self::$user[$name] : $default;
    }

    /**
     * Set a user field
     *
     * @param string $name The name of the user field
     * @param string $value The value of the user field
     * @return bool TRUE on success
     */
    public static function setField($name, $value) {
        $sql = sprintf("UPDATE `users` SET `%s`='%s' WHERE `ID`='%s'",
            mfcs::$engine->openDB->escape($name),
            mfcs::$engine->openDB->escape($value),
            mfcs::$engine->openDB->escape(users::user('ID'))
        );
        $sqlResult = mfcs::$engine->openDB->query($sql);

        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - : " . $sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Load user's projects
     *
     * @return array|bool Returns an array of user's projects or FALSE if an error occurred
     */
    public static function loadProjects() {
        $engine = EngineAPI::singleton();
        $currentProjects = array();
        $sql = sprintf("SELECT projects.ID,projectName FROM `projects` LEFT JOIN users_projects ON users_projects.projectID=projects.ID WHERE users_projects.userID='%s'",
            $engine->openDB->escape(self::user('ID'))
        );
        $sqlResult = $engine->openDB->query($sql);
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - Failed to load user's projects ({$sqlResult['error']})", errorHandle::HIGH);
            errorHandle::errorMsg("Failed to load your current projects.");
            return FALSE;
        } else {
            while ($row = mysql_fetch_assoc($sqlResult['result'])) {
                $currentProjects[$row['ID']] = $row['projectName'];
            }
        }
        return $currentProjects;
    }

    /**
     * Process the user
     *
     * @return bool TRUE on success
     */
    public static function processUser() {
        $engine = EngineAPI::singleton(); // Ensure $engine is properly initialized
    
        $username = sessionGet('username');

        if (empty($username)) {
            // errorHandle::newError(__METHOD__ . "() - No username found in session", errorHandle::DEBUG);
            // return FALSE;
            $username = "root";
        }

        $escapedUsername = $engine->openDB->escape($username);
    
        $sqlSelect = sprintf("SELECT * FROM users WHERE username='%s' LIMIT 1", $escapedUsername);
        $sqlResult = $engine->openDB->query($sqlSelect);
    
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - Failed to lookup user ({$sqlResult['error']})", errorHandle::HIGH);
            return FALSE;
        }
    
        if (!$sqlResult['numRows']) {
            // No user found, add them!
            $sqlInsert = sprintf("INSERT INTO users (username) VALUES('%s')", $escapedUsername);
            $sqlResult = $engine->openDB->query($sqlInsert);
    
            if (!$sqlResult['result']) {
                errorHandle::newError(__METHOD__ . "() - Failed to insert new user ({$sqlResult['error']})", errorHandle::DEBUG);
                return FALSE;
            }
    
            // Retrieve user after insertion
            $sqlResult = $engine->openDB->query($sqlSelect);
            self::$user = mysql_fetch_assoc($sqlResult['result']);
        } else {
            // User found
            self::$user = mysql_fetch_assoc($sqlResult['result']);
        }
    
        return TRUE;
    }
    
    /**
     * Get a user by ID or username
     *
     * @param mixed $userID The ID or username of the user
     * @return mixed|array|bool Returns the user data as an array or FALSE if not found
     */
    public static function get($userID) {
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

        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - : " . $sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }

        return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
    }

    /**
     * Get all users
     *
     * @return array|bool Returns an array of user data or FALSE if an error occurred
     */
    public static function getUsers() {
        $sql = sprintf("SELECT `ID` FROM `users` ORDER BY `lastname`");
        $sqlResult = mfcs::$engine->openDB->query($sql);

        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - : " . $sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }

        $users = array();
        while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
            if (($user = self::get($row['ID'])) == FALSE) {
                return FALSE;
            }
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Update user projects
     *
     * @return array Returns an array with the result of the update operation
     */
    public static function updateUserProjects() {
        $currentProjectsIDs = array_keys(sessionGet('currentProject'));
        $submittedProjectsIDs = isset(mfcs::$engine->cleanPost['MYSQL']['selectedProjects']) ? mfcs::$engine->cleanPost['MYSQL']['selectedProjects'] : array();

        try {
            // Delete project IDs that disappeared
            $deletedIDs = array_diff($currentProjectsIDs, $submittedProjectsIDs);
            if (sizeof($deletedIDs)) {
                $deleteSQL = sprintf("DELETE FROM users_projects WHERE userID='%s' AND projectID IN (%s)",
                    implode(',', $deletedIDs));
                $deleteSQLResult = mfcs::$engine->openDB->query($deleteSQL);
                if (!$deleteSQLResult['result']) {
                    throw new Exception("MySQL Error - " . $deleteSQLResult['error']);
                }
            }

            // Add project IDs that appeared
            $addedIDs = array_diff($submittedProjectsIDs, $currentProjectsIDs);
            if (sizeof($addedIDs)) {
                $keyPairs = array();
                foreach ($addedIDs as $addedID) {
                    $keyPairs[] = sprintf("('%s','%s')", users::user('ID'), $addedID);
                }
                $insertSQL = sprintf("INSERT INTO  users_projects (userID,projectID) VALUES %s", implode(',', $keyPairs));
                $insertSQLResult = mfcs::$engine->openDB->query($insertSQL);
                if (!$insertSQLResult['result']) {
                    throw new Exception("MySQL Error - " . $insertSQLResult['error']);
                }
            }

            // If we get here either nothing happened, or everything worked (no errors happened)
            $result = array(
                'success' => true,
                'deletedIDs' => $deletedIDs,
                'addedIDs' => $addedIDs
            );

        } catch (Exception $e) {
            $result = array(
                'success' => false,
                'errorMsg' => $e->getMessage()
            );
        }

        return $result;
    }

}

?>