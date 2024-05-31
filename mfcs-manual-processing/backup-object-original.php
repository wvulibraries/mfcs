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

    // save the object to a file
    $file = "/home/mfcs.lib.wvu.edu/data/exports/" + $objectID + ".json";

    // JSON_PRETTY_PRINT doesn't work on the server with PHP 5.3.3
    $json = json_encode($object, JSON_PRETTY_PRINT);
 
    file_put_contents($file, $json);
?>