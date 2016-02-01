
<?php
include("../header.php");

// Turn off and kill engine's output buffer
$engine->obCallback = FALSE;
ob_end_clean();

recurseInsert("acl.php","php");

$permissions      = TRUE;

$type = "zip";

try {

	$error = FALSE;

	if (!isset($engine->cleanGet['MYSQL']['objectID'])) {
		throw new Exception("No ObjectID Provided.");
	}

	// If we have an objectID and no formID, lookup the formID from the object and set it back into the GET
	if(isset($engine->cleanGet['MYSQL']['objectID']) and !isset($engine->cleanGet['MYSQL']['formID'])){
		$object = objects::get($engine->cleanGet['MYSQL']['objectID']);
		http::setGet('formID', $object['formID']);
	}

	// Object ID Validation
	if (objects::validID(TRUE,$engine->cleanGet['MYSQL']['objectID']) === FALSE) {
		throw new Exception("ObjectID Provided is invalid.");
	}

	if (mfcsPerms::isViewer($engine->cleanGet['MYSQL']['formID']) === FALSE) {
		$permissions = FALSE;
		throw new Exception("Permission Denied to view objects created with this form.");
	}

	if (isset($engine->cleanGet['MYSQL']['type']) && $engine->cleanGet['MYSQL']['type'] == "tar") {
		$type = "tar";
	}

	//determine the field name
	$field_name = "";
	$form = forms::get($object['formID']);
	foreach ($form['fields'] as $field) {
		if ($field['type'] != "file") continue;
		$field_name = $field['name'];
	}

	if (is_array($object['data'][$field_name])) {

		$files = array();

		foreach ($object['data'][$field_name]['files']['archive'] as $file) {

			$files[] = sprintf("%s",$file['name']);

		}

		$files           = implode(" ",$files);
		$destinationFile = sys_get_temp_dir()."/".time().".".$type;

		if ($type == "zip") {
			$cmdLine = sprintf("zip -j %s %s",$destinationFile,$files);
		}
		else if ($type == "tar") {
			$cmdLine = sprintf("tar -cf %s %s",$destinationFile,$files);
		}
		else {
			throw new Exception("invalid type.");
		}

		ini_set('memory_limit',-1);
		set_time_limit(0);

		chdir(mfcs::config('archivalPathMFCS')."/".$file['path']);
		exec($cmdLine);

		$fi = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fi->file($destinationFile);

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=\"".$object['idno'].".".$type."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($destinationFile));
		ob_end_clean(); 
		@readfile($destinationFile);


		// header(sprintf("Content-Disposition: attachment; filename='%s.%s'",
		// 	$object['idno'],
		// 	$type
		// 	)
		// );
		// header("Content-Type: application/octet-stream");

		// ob_end_clean(); 
		// flush(); 

		// print file_get_contents($destinationFile);

		unlink($destinationFile);

		// print "<pre>";
		// var_dump($cmdLine);
		// print "</pre>";

	}
	else {
		throw new Exception("No digital Files");
	}


}
catch(Exception $e) {
	print $e->getMessage();
	exit;
}
