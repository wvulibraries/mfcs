<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: restore oject from a file.
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    $objectID = '192379';

    // create new json file using the $objectID
    $file = "/home/mfcs.lib.wvu.edu/data/exports/${$objectID}.json";

    // restore the object from the file
    $json = file_get_contents($file);
    $object = json_decode($json, true);

    // Update the object
    objects::update($objectID, $object['formID'], $object['data'], $object['metadata'], $object['parentID'], null, $object['publicRelease']);
?>