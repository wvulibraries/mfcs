<?php
// header
include("../../../../header.php");

$error_objects   = array();
$success_objects = array();
$result          = array("objects"=>array());

try {

  if(isset($engine->cleanPost['MYSQL']['objects']) && isset($engine->cleanPost['MYSQL']['formID'])) {

    // make sure the receiving form's IDNO is managed by the user
    if (forms::IDNO_is_managed($engine->cleanPost['MYSQL']['formID'])) {
      throw new Exception("Form IDNO must be managed by user", 1);
    }

    foreach ($engine->cleanPost['MYSQL']['objects'] as $objectID) {

      $result['objects'][] = $objectID;

      if (($object_formID = objects::get_form_id($objectID)) === FALSE) {
        $error_objects[] = array( "objectID" => $objectID, "message" => "Could not retrieve object form.");
        continue;
      }

      // make sure that the forms are compatible
      if (!forms::formsAreCompatible($engine->cleanPost['MYSQL']['formID'],$object_formID)) {
        $error_objects[] = array( "objectID" => $objectID, "message" => "Forms are not compatible.");
        continue;
      }

      // make sure that the IDNO will not conflict
      if (($object_idno = objects::get_idno($objectID)) === FALSE) {
        $error_objects[] = array( "objectID" => $objectID, "message" => "Could not get Object IDNO.");
        continue;
      }

      if (!objects::idno_is_unique($object_idno)) {
        $error_objects[] = array( "objectID" => $objectID, "message" => "IDNO Conflict.");
        continue;
      }

  		if (($trans_result = mfcs::$engine->openDB->transBegin("objects")) !== TRUE) {
  			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
  			throw new Exception("Unable to start transations", 1);
  		}

      if (objects::move($objectID,$engine->cleanPost['MYSQL']['formID']) === FALSE) {
        mfcs::$engine->openDB->transRollback();
        mfcs::$engine->openDB->transEnd();

        throw new Exception("Error Moving $objectID", 1);

      }

      mfcs::$engine->openDB->transCommit();
      mfcs::$engine->openDB->transEnd();
      $success_objects[] = array("objectID" => $objectID);

    }

    $result['formID']  = $engine->cleanPost['MYSQL']['formID'];
  }
  else {
      throw new Exception("No data provided", 1);
      ;
  }

  $result["errors"]  = $error_objects;
  $result["success"] = $success_objects;

} catch (Exception $e) {
  $result['message'] = $e->getMessage();
}

header('Content-Type: application/json');
print json_encode($result);

?>
