<?php

// Easier to read, but more database calls

		$tmp = array("selectedViewUsers"   => mfcs::AUTH_VIEW,
			         "selectedEntryUsers"  => mfcs::AUTH_ENTRY,
			         "selectedUsersAdmins" => mfcs::AUTH_ADMIN
			         );

		foreach ($tmp as $I => $K) {
			if (!isset($engine->cleanPost['MYSQL'][$I]) || !is_array($engine->cleanPost['MYSQL'][$I])) continue;

			foreach ($engine->cleanPost['MYSQL'][$I] as $userID) {
				if (mfcsPerms::add($userID,$formID,$K) === FALSE) {
					throw new Exception("Error adding Permissions");
				}
			}
		}

// less database calls, but more complicated

		$permissionValueGroups = array();
		if (isset($engine->cleanPost['MYSQL']['selectedViewUsers']) && is_array($engine->cleanPost['MYSQL']['selectedViewUsers'])) {
			foreach($engine->cleanPost['MYSQL']['selectedViewUsers'] as $value) {
				$permissionValueGroups[] = sprintf("('%s','%s','%s')",
					$engine->openDB->escape($value),
					$formID,
					mfcs::AUTH_VIEW
				);
			}
		}
		if (isset($engine->cleanPost['MYSQL']['selectedEntryUsers']) && is_array($engine->cleanPost['MYSQL']['selectedEntryUsers'])) {
			foreach($engine->cleanPost['MYSQL']['selectedEntryUsers'] as $value) {
				$permissionValueGroups[] = sprintf("('%s','%s','%s')",
					$engine->openDB->escape($value),
					$formID,
					mfcs::AUTH_ENTRY
				);
			}
		}
		if (isset($engine->cleanPost['MYSQL']['selectedUsersAdmins']) && is_array($engine->cleanPost['MYSQL']['selectedUsersAdmins'])) {
			foreach($engine->cleanPost['MYSQL']['selectedUsersAdmins'] as $value) {
				$permissionValueGroups[] = sprintf("('%s','%s','%s')",
					$engine->openDB->escape($value),
					$formID,
					mfcs::AUTH_ADMIN
				);
			}
		}

		if (sizeof($permissionValueGroups)) {
			$sql = sprintf("INSERT INTO `%s` (userID,projectID,type) VALUES %s",
				$engine->openDB->escape($engine->dbTables("permissions")),
				implode(',', $permissionValueGroups)
			);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				throw new Exception("MySQL Error - Insert Permissions ({$sqlResult['error']} -- $sql)");
			}
		}

?>