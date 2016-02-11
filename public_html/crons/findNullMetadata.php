<?php
    include("../header.php");

    $forms = forms::getForms(false);

    foreach ($forms as $form) {
        // Get ID and FieldID
        $formID  = $form['ID'];
        $fieldID = $form['fields']['id'];

        foreach (forms::retrieveData($formID, $fieldID) as $data){

            if(isnull($data['value'])){
                print "Null Data Found -------------------------\r\n";
                print "   Data Id  - " . $data['ID'] . "\r\n";
                print "   FormID   - " . $data['formID'] . "\r\n";
                print "   ObjectID - " . $data['objectID'] . "\r\n";
            }
        }
    }
?>