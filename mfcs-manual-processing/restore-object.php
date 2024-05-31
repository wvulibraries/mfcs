<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: restore oject from a file.
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    $objectID = '192379';

    // Corrected file path using ${objectID}
    $file = "${objectID}";

    // Check if the file exists before attempting to read it
    if (file_exists($file)) {
        // Restore the object from the file
        $json = file_get_contents($file);
        $object = json_decode($json, true);

        // Verify that $object is an array and contains 'data'
        if (is_array($object) && isset($object['data']) && is_array($object['data'])) {
            // Update the object
            objects::update($objectID, $object['formID'], $object['data'], $object['metadata'], $object['parentID'], null, $object['publicRelease']);
        } else {
            // Handle error or log a message
            echo "Error: Invalid object data format.";
        }
    } else {
        // Handle file not found error
        echo "Error: File not found at $file";
    }
?>