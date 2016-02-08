<?php 

class checksum {

	public static function parse_uploaded_checksums($file) {

		if (strtolower(pathinfo($_FILES['checksum']['name'], PATHINFO_EXTENSION)) != "txt") {
			return false;
		}

		if (($contents = file_get_contents($file)) === FALSE) {
			return FALSE;
		}

		$contents = explode("\n",$contents);

		$checksums = array();
		foreach ($contents as $line) {
			if (mb_detect_encoding($line, 'ASCII') != "ASCII") {
				return FALSE;
			}

			$file = explode(",",$line);
			$checksums[$file[0]] = $file[1];
		}

		return $checksums;

	}

	public static function apply_checksum_to_files($objectID,$uploaded_checksums) {

		$object = objects::get($objectID);
		$form   = forms::get($object['formID']);

		foreach ($form['fields'] as $field) {
			if ($field['type'] == "file") {
				foreach ($object['data'][$field['name']]['files']['archive'] as $file) {
					foreach ($uploaded_checksums as $filename=>$checksum) {
						if ($file['name'] == $filename) {
							$filepath = sprintf("%s/%s",$file['path'],$file["name"]);

							$sql       = sprintf("UPDATE `filesChecks` set `checksum`='%s', `userProvided`='1' WHERE `location`='%s'",
								mfcs::$engine->openDB->escape($checksum),
								mfcs::$engine->openDB->escape($filepath)
								);
							$sqlResult = mfcs::$engine->openDB->query($sql);
							
							if (!$sqlResult['result']) {
								errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
								return FALSE;
							}

						}
					}
				}
			}
		}

		return TRUE;
	}

}

?>