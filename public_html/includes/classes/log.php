<?php

class log {

    // Insert method with manual query construction
    public static function insert($action, $objectID = 0, $formID = 0, $info = null)
    {
        $db = mfcs::$engine->openDB;

        // Escaping input manually (as in original)
        $username = $db->escape(users::user('username'));
        $ip = $db->escape($_SERVER['REMOTE_ADDR']);
        $action = $db->escape($action);
        $objectID = ($objectID !== null) ? $db->escape($objectID) : 'NULL';
        $formID = $db->escape($formID);
        $info = $db->escape($info);
        $date = time();

        $sql = "INSERT INTO `logs` (`username`, `IP`, `action`, `objectID`, `formID`, `info`, `date`) 
                VALUES ('$username', '$ip', '$action', $objectID, '$formID', '$info', '$date')";
        
        $sqlResult = $db->query($sql);

        // Handling the response and ensuring an array structure
        if (!is_array($sqlResult) || !$sqlResult['result']) {
            errorHandle::newError(__METHOD__ . "() - SQL Insert Failed: " . $sqlResult['error'], errorHandle::DEBUG);
            return false;
        }

        return true;
    }

    // pull_actions method with manual query construction
    public static function pull_actions($actions, $objectID)
    {
        if (!is_array($actions)) {
            return array();
        }

        $db = mfcs::$engine->openDB;
        $blame = array();

        foreach ($actions as $action) {
            $sql = "SELECT `username`, `date` FROM `logs` WHERE `objectID` = '" . $db->escape($objectID) . "' AND `action` = '" . $db->escape($action) . "'";
            $sqlResult = $db->query($sql);

            // Ensure correct structure of the result
            if (!is_array($sqlResult) || !$sqlResult['result']) {
                errorHandle::newError(__METHOD__ . "() - SQL Query Failed: " . $sqlResult['error'], errorHandle::DEBUG);
                return array();
            }

            // Fetch rows from result
            while ($row = mysqli_fetch_array($sqlResult['result'], MYSQLI_ASSOC)) {
                $blame[] = [$row['username'], date('D, d M Y H:i', $row['date'])];
            }
        }

        return $blame;
    }
}

?>
