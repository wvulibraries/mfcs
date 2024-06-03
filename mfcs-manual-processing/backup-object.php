<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: dump record file so we can restore it.
     */

    session_save_path('/tmp');

    include "../public_html/header.php";


    $objectID = '192379';

    $object = objects::get($objectID);

    // dump the object to screen
    var_dump($object['data']['file']);
    die();

    // Encode the record to JSON
    $json_encoded = json_encode($object);

    // save the object to a file
    $file = $objectID + ".json";

    // Write the JSON to the file
    file_put_contents($file, $json_encoded);

    // Verify that the file was written
    if (file_exists($file)) {
        echo "File written successfully.";
    } else {
        echo "Error: File not written.";
    }
?>