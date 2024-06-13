<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2023-09-25
     * Description: Import ORCID ID Registry CSV into MFCS
     */

    session_save_path('/tmp');
    include "../public_html/header.php";

    // create new mfcs object
    function create($formID, $values) {
		// Get the current Form
		if (($form = forms::get($formID)) === FALSE) {
			errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
			return FALSE;
		}

        $item = objects::create($formID,$values,$form['metadata'],"0",null,null,"1");
    }

    function findChoicesFormID($formID, $formFieldName) {
        // Get the MFCS form from its ID
        $form = forms::get($formID);
    
        // Check if form is found
        if ($form === null) {
            throw new Exception("Form with ID $formID not found.");
        }

        foreach($form['fields'] as $field) {
            if($field['name'] == $formFieldName) { 
                return $field['choicesForm'];
            }
        }
        return null;
    }

    function trimAndCompare($val1, $val2) {
        // convert both values to lowercase and trim whitespace
        $val1 = strtolower($val1);
        $val2 = strtolower($val2);
        return strcmp(trim($val1), trim($val2)) === 0;
    }

    function getAndCheckObject($objectID, $value) {
        // check and see if it is a valid id for a object
        $item = objects::get($objectID);

        // returned item should not be null it should be an array
        if ($item === null) {
            return null;
        }

        // loop over each entry in the $item['data'] array
        foreach ($item['data'] as $key => $val) {
            if (trimAndCompare($val, $value)) {
                return $choice['ID'];
            }
        }

        // return null if no match is found
        return null;
    }

    function find($formID, $value, $fieldName) {
        // Get the current Form
        if (($form = forms::get($formID)) === FALSE) {
            errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
            return FALSE;
        }
    
        // Get all objects for the choicesFormID
        $choices = objects::getAllObjectsForForm($formID);
    
        if ($choices === false) {
            throw new Exception("Failed to retrieve objects for form ID $formID");
        }

        foreach ($choices as $choice) {   
            // loop over each entry in the $choice['data'] array to find a match
            foreach ($choice['data'] as $key => $val) {
                // var_dump($val);
                if (trimAndCompare($val, $value)) {
                    var_dump($choice['ID']);
                    return $choice['ID'];
                }
            }
        }

        // // Loop through the choices to find a match
        // foreach ($choices as $choice) {    
        //     if (isset($choice['data'][$fieldName])) {   
        //         // check and see if $choice['data'][$fieldName] is an array
        //         if (is_array($choice['data'][$fieldName]) && is_numeric($choice['data'][$fieldName][0])) {
        //             $objectID = getAndCheckObject($choice['data'][$fieldName][0], $value);

        //             // returned item should not be null it should be an array
        //             if ($objectID === null) {
        //                 continue;
        //             }

        //             return $objectID;
        //         }
        //     }
        // }

        // Return null if no match is found
        return null;
    }

    function isUri($value) {
        // check and see if $value is a uri
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    function buildCreatorArray($formID, $value) {
        // Create the array to hold the values
        $createArray = array();

        if (isUri($value)) {
            // add new array with current $field['name'] and $value
            $createArray['uri'] = $value;
            $createArray['title'] = "";
        }
        else {
            $createArray['title'] = $value;
            $createArray['uri'] = "";
        }

        return $createArray;
    }

    function buildRightsArray($formID, $value) {
        // Create the array to hold the values
        $createArray = array();

        if (isUri($value)) {
            // add new array with current $field['name'] and $value
            $createArray['rights'] = "";
            $createArray['uri'] = $value;
        }
        else {
            $createArray['rights'] = $value;
            $createArray['uri'] = "";
        }

        return $createArray;
    }

    function buildLanguageArray($formID, $value) {
        // Create the array to hold the values
        $createArray = array();

        // add new array with current $field['name'] and $value
        $createArray['language'] = "";
        $createArray['languageCode'] = $value;

        return $createArray;
    }

    function buildCreateArray($choicesFormID, $value, $fieldName) {
        // Create the array to hold the values
        switch ($fieldName) {
        case 'creator':
            return buildCreatorArray($choicesFormID, $value);
            break;
        case 'rights':
            return buildRightsArray($choicesFormID, $value);
            break;
        case 'language':
            return buildLanguageArray($choicesFormID, $value);
            break;
        // case 'recordType':
        //     // Code for 'recordType' field
        //     break;
        // case 'type':
        //     // Code for 'type' field
        //     break;
        // case 'policyArea':
        //     // Code for 'policyArea' field
        //     break;
        case 'subjectTopical':
            // creator array and the topical array are the same
            // so we can use the same function to create the array
            return buildCreatorArray($choicesFormID, $value);
            break;
        // case 'subjectCorpName':
        //     // Code for 'subjectCorpName' field
        //     break;
        // case 'location':
        //     // Code for 'location' field
        //     break;
        // case 'congress':
        //     // Code for 'congress' field
        //     break;
        // case 'collectionTitle':
        //     // Code for 'collectionTitle' field
        //     break;
        // case 'findingAid':
        //     // Code for 'findingAid' field
        //     break;
        default:
            echo "No match found for field name '$fieldName'" . PHP_EOL;
            // Default code if none of the above cases match
            // var_dump("No match found for field name '$fieldName'");
            // var_dump($formID);
            // var_dump($value);
            // var_dump($fieldName);
            // die();
            break;
        }

        return null;
    }
           
    // find or create linked objects
    function findOrCreate($formID, $value, $fieldName) {
        // Get the choicesFormID
        $choicesFormID = findChoicesFormID($formID, $fieldName);

        // Find the object
        $objectID = find($choicesFormID, $value, $fieldName);

        // Create the object if it doesn't exist
        if ($objectID === null) {
            // var_dump($formID);
            // var_dump($value);
            // var_dump($fieldName);

            // die();

            // Build the data array
            $data = buildCreateArray($choicesFormID, $value, $fieldName);

            if ($data === null) {
                return null;
            }

            // if (objects::create($choicesFormID, $data, "1", null, null, "0") === FALSE) {
            //     errorHandle::newError(__METHOD__."() - creating object", errorHandle::DEBUG);
            //     return FALSE;
            // }

            create($choicesFormID, $data);

            // Get the objectID after creating the object
            $objectID = find($choicesFormID, $value, $fieldName);

            if ($objectID) {
                // Write to log file
                $logMessage = "Created object for value '$value' in form ID '$choicesFormID' with field name '$fieldName'".PHP_EOL;
                file_put_contents('create.log', $logMessage, FILE_APPEND);
            }
            else {
                // Write to log file
                $logMessage = "Failed to create object for value '$value' in form ID '$choicesFormID' with field name '$fieldName'".PHP_EOL;
                file_put_contents('create.log', $logMessage, FILE_APPEND);
            }
        }

        // Return the objectID
        return $objectID;
    }

    function changeFieldToAssociativeArray($formID, $values, $fieldName) {
        // unset the current creator in the array it needs to be handled differently
        $fieldValue = $values[$fieldName];
        unset($values[$fieldName]);

        // var_dump($values);
        // var_dump($fieldName);

        // find or create the entry
        $values[$fieldName] = array(findOrCreate($formID, $fieldValue, $fieldName));

        return $values;
    }

    function changeFieldToAssociativeObject($formID, $values, $fieldName) {
        // unset the current creator in the array it needs to be handled differently
        $fieldValue = $values[$fieldName];
        unset($values[$fieldName]);

        // find or create the creator
        $values[$fieldName] = findOrCreate($formID, $fieldValue, $fieldName);

        return $values;
    }

    $csv_file = "mcppc.csv";
    $import_dir = "/home/mfcs.lib.wvu.edu/data/imports/";

    // test values
    $engine               = mfcs::$engine;
    $formID               = 151; 
    
    // Opening the file for reading...
    $fp = fopen($import_dir.$csv_file, 'r');

    // fix to remove BOM from UTF-8
    // we were having issues with the BOM being included in the first column
    // this is a fix to remove the BOM
    // https://stackoverflow.com/questions/22529854/php-undefined-index-even-if-it-exists
    $bom = pack('CCC', 0xef, 0xbb, 0xbf);

    if (0 !== strcmp(fread($fp, 3), $bom)) {
        fseek($fp, 0);
    }

    // Headrow
    $head = fgetcsv($fp, 4096, ',', '"');
    $dc_terms = fgetcsv($fp, 4096, ',', '"');

    // Rows
    $count = 1;
    while($column = fgetcsv($fp, 4096, ',', '"'))
    {
        // combine headrow and row
        $values = array_combine($head, $column);

        // drop fields that are not in the form
        $fields = array("Available At", "Preview");
        foreach($fields as $field) {
            unset($values[$field]);
        }

        // var_dump($values);
        // die();

        // these fields can contain multiple values
        $fields = array('creator', 'rights', 'language', 'recordType', 'type', 'policyArea', 'subjectTopical', 'subjectCorpName', 'location');
        foreach ($fields as $field) {
            $values = changeFieldToAssociativeArray($formID, $values, $field);
        }

        // these fields are associative objects single value items
        $fields = array('congress', 'collectionTitle', 'findingAid');
        foreach ($fields as $field) {
            $values = changeFieldToAssociativeObject($formID, $values, $field);
        }
        
        // var_dump($values);
        // die();

        // create record
        create($formID, $values);

        // increment count
        $count++;

        // to avoid deadlocks we needed a pause after every record
        // we tried after every 100 but we kept getting deadlocks.
        // So we added a pause after every record.
        // this wasn't a issue locally only on the server.
        // Tracy A. McCormick 2021-09-25

        sleep(1);
    }    

?>