<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: Reset upload field "file" to "documentUpload" for the mcppc_upload form
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    // test values
    $engine               = mfcs::$engine;
    $formID               = 151; 
    
    $count = 0;

    $objects = objects::getAllObjectsForForm($formID);
    
    foreach ($objects as $object) {
        $count++;
        if ($object != null) {
            // var_dump($object);
            // die();

            // display id of the object being updated
            print "Updating Object ID: " . $object["ID"] . "\n";

            // Rename $object["data"]["file"] to $object["data"]["documentUpload"]
            $object["data"]["documentUpload"] = $object["data"]["file"];
            unset($object["data"]["file"]);
            
            // save the object
            objects::update($object["ID"],$object['formID'],$object['data'],$object['metadata'],$object['parentID'],NULL,$object['publicRelease']);
        }
        sleep(1);
    }
    
    print "Updated " . $count . " Objects.\n";    

?>