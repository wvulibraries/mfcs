
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
	// FIXME: this will only grab the LAST file field, if a form has Multiple
	// file fields this will not download all of the files.
	$field_name = "";
	$form = forms::get($object['formID']);
	foreach ($form['fields'] as $field) {
		if ($field['type'] != "file") continue;
		$field_name = $field['name'];
	}

	if (is_array($object['data'][$field_name])) {

		$files = array();

		foreach ($object['data'][$field_name]['files']['archive'] as $file) {
			$files[] = $file['name'];
		}

		$files           = implode(" ",$files);
		$destinationFile = sprintf("%s/%s.%s",mfcs::config('mfcsDownloadAllDir'),time(),$type);

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
		header(sprintf('Content-Disposition: attachment; filename="%s.%s"',$object['idno'],$type));
		header("Content-Transfer-Encoding: binary");
		header(sprintf("Content-Length: %s",filesize($destinationFile)));
		@readfile($destinationFile);

		unlink($destinationFile);
	}
	else {
		throw new Exception("No digital Files");
	}


}
catch(Exception $e) {
	print $e->getMessage();
	exit;
}
