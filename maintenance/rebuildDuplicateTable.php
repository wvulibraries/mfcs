<?php

session_save_path('/tmp');

include "../public_html/header.php";
include "../public_html/includes/functions.php";
include "../public_html/includes/validator.php";

$objects = objects::get();

// Begin the transaction
if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
	print __METHOD__."() - unable to start database transactions";
	exit;
}

foreach ($objects as $object) {

	// only rebuild the objects
	if ($object['metadata'] != '0') continue;

	// Build cleanPost
	// @TODO this should be stripped when updateDupeTable is fixed to not require cleanPost
	
	// Reset cleanPost
	mfcs::$engine->cleanPost['MYSQL'] = array();
	mfcs::$engine->cleanPost['HTML']  = array();
	mfcs::$engine->cleanPost['RAW']   = array();

	foreach ($object['data'] as $name=>$raw) {
		http::setPost($name,$raw);
	}


	if (duplicates::updateDupeTable($object['formID'],$object['ID'],$object['data']) === FALSE) {
		mfcs::$engine->openDB->transRollback();
		mfcs::$engine->openDB->transEnd();

		print __METHOD__."() - updating dupe matching";

		exit;
	}

}

mfcs::$engine->openDB->transCommit();
mfcs::$engine->openDB->transEnd();

?>