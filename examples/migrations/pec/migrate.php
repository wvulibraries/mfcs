<?php

session_save_path('/tmp');

include("../../header.php");

function errorHandler($errno, $errmsg, $filename, $linenum, $vars) {

	$errorReporting = ini_get('error_reporting'); 
        if($errorReporting === 0 || !($errorReporting & $errno)) return FALSE;

	$errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );

		$err = sprintf("%s -- ",
			$errortype[$errno],
			wddx_serialize_value($vars,"Variables")
			);

		print "<pre>";
		var_dump($vars);
		print "</pre>";
		error_log($err);
}
// set_error_handler("errorHandler");

function convertString($string) {

	// Formatting
	$string = preg_replace('/%Oitalic%/',   '<em>',      $string);
	$string = preg_replace('/%Citalic%/',   '</em>',     $string);
	$string = preg_replace('/%Obold%/',     '<strong>',  $string);
	$string = preg_replace('/%Cbold%/',     '</strong>', $string);
	$string = preg_replace('/%underline%/', '<u>',       $string);
	$string = preg_replace('/%underline%/', '</u>',      $string);
	$string = preg_replace('/\|\|\|/',      '<br />',    $string);

	// Links
	$string = preg_replace('/%link url="(.+?)"%(.+?)%\/link%/', '<a href="$1"><u>$2</u></a>', $string);

	// Fonts
	$string = preg_replace('/&#x2026;/', "…", $string);
	$string = preg_replace('/&iexcl;/', "¡", $string);
	$string = preg_replace('/&pound;/', "£", $string);
	$string = preg_replace('/&yen;/', "¥", $string);
	$string = preg_replace('/&iquest;/', "¿", $string);
	$string = preg_replace('/&frac34;/', "¾", $string);
	$string = preg_replace('/&frac12;/', "½", $string);
	$string = preg_replace('/&frac14;/', "¼", $string);
	$string = preg_replace('/&#x2018;/', "‘", $string);
	$string = preg_replace('/&#x2019;/', "’", $string);

	// Punctuation
	$string = preg_replace('/&gt;/',">",$string);
	$string = preg_replace('/&lt;/',"<",$string);
	$string = preg_replace('/&quot;/','"',$string);

	return $string;
}

function parseHeadings($table,$record) {

	switch ($table) {
		case "creatorCorpName":
			$metaTable = "creator_CorpName";
			break;
		case "creatorMeetName":
			$metaTable = "creator_MeetingName";
			break;
		case "creatorPersName":
			$metaTable = "creator_PersName";
			break;
		case "creatorUniformTitle":
			$metaTable = "creator_UniformName";
			break;
		case "subjectUniformTitle":
			$metaTable = "subject_uniformtitle";
			break;
		case "subjectTopical":
			$metaTable = "subject_topical";
			break;
		case "subjectPersName":
			$metaTable = "subject_persname";
			break;
		case "subjectMeetingName":
			$metaTable = "subject_meetname";
			break;
		case "subjectGeoName":
			$metaTable = "subject_geoname";
			break;
		case "subjectCorpName":
			$metaTable = "subject_corpname";
			break;
		default:
			print "Error getting table setup for ".$table;
			exit;
	}

	global $metadata;

	$return = array();
	if (!is_empty($record[$table])) {
		$items = explode("|",$record[$table]);

		foreach ($items as $item) {
			if (is_empty($item)) continue;

			if (isset($metadata[$metaTable][$item]['objID'])) {

				$return[] = (string)$metadata[$metaTable][$item]['objID'];
			}
			else {
				// print "Deleted: <pre>";
				// var_dump($metaTable);
				// print "</pre>";

				// print "<pre>";
				// var_dump($item);
				// print "</pre>";
			}
		}
	}

	return $return;

}

errorHandle::errorReporting(errorHandle::E_ALL);

$engine->dbConnect("server","dlxs.lib.wvu.edu");
$engine->dbConnect("username","remote");
$engine->dbConnect("password",'My$QLnb.UP??');

$remoteDB = $engine->dbConnect("database","AdminPEC",FALSE);

// Reset the values for the local database
$engine->openDB = NULL;
$engine->dbConnect("server","localhost");
$engine->dbConnect("username","systems");
$engine->dbConnect("password",'Te$t1234');
$engine->dbConnect("database","mfcs",TRUE);

$metadataSQL                         = array();
$metadataSQL['creator_CorpName']     = sprintf("SELECT * FROM `creator_CorpName`");
$metadataSQL['creator_MeetingName']  = sprintf("SELECT * FROM `creator_MeetingName`");
$metadataSQL['creator_PersName']     = sprintf("SELECT * FROM `creator_PersName`");
$metadataSQL['creator_UniformName']  = sprintf("SELECT * FROM `creator_UniformName`");
$metadataSQL['subject_uniformtitle'] = sprintf("SELECT * FROM `subject_uniformtitle`");
$metadataSQL['subject_topical']      = sprintf("SELECT * FROM `subject_topical`");
$metadataSQL['subject_persname']     = sprintf("SELECT * FROM `subject_persname`");
$metadataSQL['subject_meetname']     = sprintf("SELECT * FROM `subject_meetname`");
$metadataSQL['subject_geoname']      = sprintf("SELECT * FROM `subject_geoname`");
$metadataSQL['subject_corpname']     = sprintf("SELECT * FROM `subject_corpname`");
$metadataSQL['types']                = sprintf("SELECT * FROM `types`");


$sql       = sprintf("SELECT * FROM `records`");
// $sql = sprintf("SELECT * FROM `records` WHERE `identifier`='P31'");
$sqlResult = $remoteDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	print "Error retrieving records.";
	print "<pre>";
	var_dump($sqlResult);
	print "</pre>";
	exit;
}

$records   = array();
$metadata = array();

while ($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
	foreach ($row as $I=>$V) {
		$records[$row['identifier']][$I] = $V; 
	}
}

$types = array();
foreach ($metadataSQL as $I=>$sql) {
	// if ($I != "types") continue;

	$sqlResult2 = $remoteDB->query($sql);
	
	if (!$sqlResult2['result']) {
		print "Error retrieving records.";
		print "<pre>";
		var_dump($sqlResult2);
		print "</pre>";
		exit;
	}
	
	while($row       = mysql_fetch_array($sqlResult2['result'],  MYSQL_ASSOC)) {

		switch ($I) {
			case "creator_CorpName":
				$formID = "3";
				break;
			case "creator_MeetingName":
				$formID = "4";
				break;
			case "creator_PersName":
				$formID = "1";
				break;
			case "creator_UniformName":
				$formID = "5";
				break;
			case "subject_uniformtitle":
				$formID = "11";
				break;
			case "subject_topical":
				$formID = "12";
				break;
			case "subject_persname":
				$formID = "6";
				break;
			case "subject_meetname":
				$formID = "10";
				break;
			case "subject_geoname":
				$formID = "13";
				break;
			case "subject_corpname":
				$formID = "9";
				break;
			case "types":
				$formID = "14";
				break;
			default:
				print "Error getting FormID for ".$I;
				exit;
		}

		$submitArray = array();
		$submitArray['name'] = $row['title'];

		if (objects::add($formID,$submitArray) !== TRUE) {
			print "error submiting formID ".$formID;
			print "<pre>";
			var_dump($submitArray);
			print "</pre>";

			errorHandle::prettyPrint();

			exit;
		}

		$metadata[$I][$row['ID']]['title'] = $row['title'];
		$metadata[$I][$row['ID']]['objID'] = localvars::get("newObjectID");

	}

}

foreach ($records as $identifier=>$record) {

	$submitArray = array();
	
	$submitArray['idno']                 = $record['identifier'];
	$submitArray['identifier']           = $record['identifier'];
	$submitArray['publicRelease']        = ($record['publicRelease'] == "1")?"Yes":"No"; // "No" | "Yes"
	$submitArray['mediaRelease']         = "Yes";
	$submitArray['hasMedia']             = ($record['hasMedia']      == "1")?"Yes":"No"; // "No" | "Yes""
	$submitArray['title']                = $record['title']; //
	$submitArray['date']                 = $record['date']; //
	$submitArray['extent']               = $record['extent']; //
	$submitArray['description']          = $record['description']; //
	$submitArray['scopeAndContentsNote'] = $record['scopeAndContentsNote']; //
	$submitArray['type']                 = $metadata["types"][$record['type']]['objID']; //
	$submitArray['format']               = $record['format']; //
	$submitArray['itemCount']            = $record['itemCount']; //
	
	$submitArray['creatorPersName']      = parseHeadings('creatorPersName',$record);
	$submitArray['creatorCorpName']      = parseHeadings('creatorCorpName',$record); //
	$submitArray['creatorMeetName']      = parseHeadings('creatorMeetName',$record); //
	$submitArray['creatorUniformTitle']  = parseHeadings('creatorUniformTitle',$record); //
	$submitArray['subjectPersName']      = parseHeadings('subjectPersName',$record); //
	$submitArray['subjectCorpName']      = parseHeadings('subjectCorpName',$record); //
	$submitArray['subjectMeetingName']   = parseHeadings('subjectMeetingName',$record); //
	$submitArray['subjectUniformTitle']  = parseHeadings('subjectUniformTitle',$record); //
	$submitArray['subjectTopical']       = parseHeadings('subjectTopical',$record); //
	$submitArray['subjectGeoName']       = parseHeadings('subjectGeoName',$record); //


	// manipulate data
	$submitArray['description']          = convertString($submitArray['description']);
	$submitArray['scopeAndContentsNote'] = convertString($submitArray['scopeAndContentsNote']);
	$submitArray['title']                = convertString($submitArray['title']);
	$submitArray['extent']               = convertString($submitArray['extent']);

	// print "<pre>";
	// var_dump($submitArray);
	// print "</pre>";

	// exit;

	// check to see if we have a digital item for object
	if (file_exists("/home/mfcs.lib.wvu.edu/data/working/uploads/".$submitArray['idno'])) {

		$submitArray['digitalFiles'] = $submitArray['idno'];
		$submitArray['mediaRelease'] = "Yes";
	}

	if (objects::add("2",$submitArray) !== TRUE) {
		print "error adding object ".$submitArray['idno'];
		print "<pre>";
		var_dump($submitArray);
		print "</pre>";

		errorHandle::prettyPrint();

		exit;
	}

	// add the item to the pec project
	if ((objects::addProject(localvars::get("newObjectID"),"1")) === FALSE) {
		print "error -- add Project: \n";
		print "<pre>";
		var_dump($submitArray);
		print "</pre>";

		errorHandle::prettyPrint();

		exit;
	}


	mfcs::$engine->cleanPost['MYSQL'] = array();
	mfcs::$engine->cleanPost['HTML']  = array();
	mfcs::$engine->cleanPost['RAW']   = array();

	// make certain we don't have any data cache
	unset($submitArray);

}




print "Records: <pre>";
var_dump(count($records));
print "</pre>";

$total = 0;
foreach ($metadata as $table=>$records) {
	print "$table: <pre>";
	var_dump(count($records));
	print "</pre>";

	$total += count($records);
}

print "total Metadata: <pre>";
var_dump($total);
print "</pre>";

?>