<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2023-09-25
     * Description: Reset public release to yes or the orcid_id_registry form
     *              This is a one time script to fix the public release flag
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    // test values
    $engine               = mfcs::$engine;
    $formID               = 15; 
    
    $count = 0;

    $objects = objects::getAllObjectsForForm($formID);
    
    foreach ($objects as $object) {
        $count++;
        if ($object != null) {
            echo "Updating Object ID: " . $object["ID"] . "\n";
            // objects::update($object["ID"],$formID,$object['data'],$object['metadata'],$object['parentID'],NULL,"1");
            // die();
        }
        sleep(1);
    }
    
    print "Updated " . $count . " Objects.\n";    

?>