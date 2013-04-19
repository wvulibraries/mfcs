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
			while ($row = mysql_fetch_assoc($sqlResult['result'])) {
				$currentProjects[ $row['ID'] ] = $row['projectName'];
			}
		}

		return $currentProjects;

	}

	public static function processUser() {

		$engine = EngineAPI::singleton();

		$username  = sessionGet('username');
        $sqlSelect = sprintf("SELECT * FROM users WHERE username='%s' LIMIT 1", 
        	$engine->openDB->escape($username)
        	);
        $sqlResult = $engine->openDB->query($sqlSelect);
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - Failed to lookup user ({$sqlResult['error']})", errorHandle::HIGH);
            return FALSE;
        }
        else {
            if (!$sqlResult['numRows']) {
                // No user found, add them!
                $sqlInsert = sprintf("INSERT INTO users (username) VALUES('%s')", 
                	$engine->openDB->escape($username)
                	);
                $sqlResult = $engine->openDB->query($sqlInsert);
                if (!$sqlResult['result']) {
                    errorHandle::newError(__METHOD__."() - Failed to insert new user ({$sqlResult['error']})", errorHandle::DEBUG);
                    return FALSE;
                }
                else {
                    $sqlResult = $engine->openDB->query($sqlSelect);
                    self::$user = mysql_fetch_assoc($sqlResult['result']);
                }
            }
            else {
                self::$user = mysql_fetch_assoc($sqlResult['result']);
            }

        }

        return TRUE;
	}

    // userID can be mysql ID or username
    public static function get($userID) {

        if (validate::integer($userID)) {
            $whereClause = sprintf("WHERE `ID`='%s'",
                mfcs::$engine->openDB->escape($userID)
                );
        }
        else {
            $whereClause = sprintf("WHERE `username`='%s'",
                mfcs::$engine->openDB->escape($userID)
                );
        }

        $sql       = sprintf("SELECT * FROM `users` %s LIMIT 1",
            $whereClause
            );
        $sqlResult = mfcs::$engine->openDB->query($sql);
        
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
            return FALSE;
        }
        
        return mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

    }

    public static function getUsers() {

        $sql       = sprintf("SELECT `ID` FROM `users`");
        $sqlResult = mfcs::$engine->openDB->query($sql);
        
        if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
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

}

?>