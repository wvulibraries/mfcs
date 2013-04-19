<?php

class navigation {

	public static function updateFormNav($groupings) {

		$groupings = json_decode($groupings, TRUE);

		if (!is_empty($groupings)) {
			foreach ($groupings as $I => $grouping) {
				$positions[$I] = $grouping['position'];
			}

			array_multisort($positions, SORT_ASC, $groupings);
		}

		$groupings = encodeFields($groupings);

		$sql = sprintf("UPDATE `forms` SET `navigation`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($engine->dbTables("projects")),
			mfcs::$engine->openDB->escape($groupings),
			mfcs::$engine->cleanGet['MYSQL']['id']
		);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

}

?>