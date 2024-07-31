<?php
session_save_path('/tmp');
include "../public_html/header.php";

// Function to create objects
function createObject($formID, $values) {
    // Get the current Form
    if (($form = forms::get($formID)) === FALSE) {
        errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
        file_put_contents('/tmp/log/form_error.log', "Failed to retrieve form for formID: $formID", FILE_APPEND);
        return FALSE;
    }

    // Log the form details
    file_put_contents('/tmp/log/form_details.log', "Form details: " . print_r($form, true), FILE_APPEND);

    // Log the values being used to create the object
    file_put_contents('/tmp/log/values.log', "Values: " . print_r($values, true), FILE_APPEND);

    // Create the object
    $item = objects::create($formID, $values, $form['metadata'], "0", null, null, "0");

    if ($item === FALSE) {
        errorHandle::newError(__METHOD__."() - failed to create object", errorHandle::DEBUG);
        // Log detailed error information
        file_put_contents('/tmp/log/create_error.log', "Failed to create object with values: " . print_r($values, true), FILE_APPEND);
        return FALSE;
    }

    // Log the created item details
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents('/tmp/log/item.log', "Created item at $timestamp: " . print_r($item, true) . PHP_EOL, FILE_APPEND);

    return $item;
}

function findChoicesFormID($formID, $formFieldName) {
    $form = forms::get($formID);

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
    // check and insure both values are strings
    if (!is_string($val1) || !is_string($val2)) {
        return false;
    }

    $val1 = strtolower($val1);
    $val2 = strtolower($val2);
    return strcmp(trim($val1), trim($val2)) === 0;
}

function getAndCheckObject($objectID, $value) {
    $item = objects::get($objectID);

    if ($item === null) {
        return null;
    }

    foreach ($item['data'] as $key => $val) {
        if (trimAndCompare($val, $value)) {
            return $item['ID'];
        }
    }

    return null;
}

function find($formID, $value, $fieldName) {
    if (($form = forms::get($formID)) === FALSE) {
        errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
        return FALSE;
    }

    $choices = objects::getAllObjectsForForm($formID);

    if ($choices === false) {
        throw new Exception("Failed to retrieve objects for form ID $formID");
    }

    foreach ($choices as $choice) {               
        foreach ($choice['data'] as $key => $val) {
            if (trimAndCompare($val, $value)) {
                return $choice['ID'];
            }
        }
    }

    return null;
}

function findOldObject($value, $fieldName = 'idno', $formID = 135) {
    // if $value is a string then convert to lowercase
    if (is_string($value)) {
        $value = strtolower($value);
    }

    if (($form = forms::get($formID)) === FALSE) {
        errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
        return FALSE;
    }

    $choices = objects::getAllObjectsForForm($formID);

    if ($choices === false) {
        throw new Exception("Failed to retrieve objects for form ID $formID");
    }

    foreach ($choices as $choice) {               
        foreach ($choice['data'] as $key => $val) {
            // if $val is a string then convert to lowercase
            if (is_string($val)) {
                $val = strtolower($val);
            }

            if (trimAndCompare($val, $value)) {
                return $choice['ID'];
            }
        }
    }

    return null;
}

function buildLanguageArray($formID, $value) {
    $createArray = array();
    // $createArray['language'] = "";
    $createArray['languageCode'] = $value;

    return $createArray;
}

function isUri($value) {
    return filter_var($value, FILTER_VALIDATE_URL) ? true : false;
}

function buildUriArray($formID, $fieldName, $value) {
    $createArray = array();

    if (isUri($value)) {
        // $createArray[$fieldName] = "";
        $createArray['uri'] = $value;
    } else {
        $createArray[$fieldName] = $value;
        // $createArray['uri'] = "";
    }

    return $createArray;
}

function buildChoicesArray($choicesFormID, $value, $fieldName) {
    if (empty($value)) {
        return null;
    }

    switch ($fieldName) {
        case 'creator':
        case 'subjectTopical':
        case 'subjectCorpName':
            return buildUriArray($choicesFormID, 'title', $value);
        case 'rights':
            return buildUriArray($choicesFormID, 'rights', $value);
        case 'language':
            return buildLanguageArray($choicesFormID, $value);
        default:
            return null;
    }
}

function findOrCreate($formID, $value, $fieldName) {
    // trim leading and trailing whitespace
    $value = trim($value);

    $choicesFormID = findChoicesFormID($formID, $fieldName);
    $objectID = find((int)$choicesFormID, $value, $fieldName);

    if ($objectID === null) {
        // echo "Failed to find object for value '$value' in form ID '$choicesFormID' with field name '$fieldName'" . PHP_EOL;

        $data = buildChoicesArray($choicesFormID, $value, $fieldName);

        if ($data === null) {
            return null;
        }

        if (createObject($choicesFormID, $data) === FALSE) {
            // Log the failure if object creation fails
            file_put_contents('/tmp/log/create.log', "Failed to create object for value '$value' in form ID '$choicesFormID' with field name '$fieldName'" . PHP_EOL, FILE_APPEND);
            return null;
        }
        
        $objectID = find($choicesFormID, $value, $fieldName);
        file_put_contents('/tmp/log/create.log', "Created object for value '$value' in form ID '$choicesFormID' with field name '$fieldName'" . PHP_EOL, FILE_APPEND);
    }

    return $objectID;
}

function changeFieldToAssociativeArray($formID, $values, $fieldName) {
    // check and make sure array key exists in $values
    if (!array_key_exists($fieldName, $values)) {
        return $values;
    }

    // check and see if $values[$fieldName] contains multiple values separated by a ;
    if (strpos($values[$fieldName], ';') !== false) {
        $values[$fieldName] = explode(';', $values[$fieldName]);
    }

    $fieldValue = $values[$fieldName];
    unset($values[$fieldName]);

    // if $fieldValue is an array, then we need to loop over each value and create an object for each value
    if (is_array($fieldValue)) {
        $values[$fieldName] = array();
        foreach ($fieldValue as $value) {
            // trim leading and trailing whitespace
            $value = trim($value);
            
            $values[$fieldName][] = findOrCreate($formID, $value, $fieldName);
        }
        return $values;
    }

    $values[$fieldName] = array(findOrCreate($formID, $fieldValue, $fieldName));

    return $values;
}

function changeFieldToAssociativeObject($formID, $values, $fieldName) {
    $fieldValue = $values[$fieldName];
    unset($values[$fieldName]);
    $values[$fieldName] = findOrCreate($formID, $fieldValue, $fieldName);

    if ($values[$fieldName] === null) {
        // Log the failure if object creation fails
        file_put_contents('/tmp/log/create.log', "Failed to create object for value '$fieldValue' in form ID '$formID' with field name '$fieldName'" . PHP_EOL, FILE_APPEND);
    }

    return $values;
}

function importData($csvFile, $formID) {
    $importDir = "/home/mfcs.lib.wvu.edu/data/imports/";
    $fp = fopen($importDir . $csvFile, 'r');

    $bom = pack('CCC', 0xef, 0xbb, 0xbf);
    if (0 !== strcmp(fread($fp, 3), $bom)) {
        fseek($fp, 0);
    }

    $head = fgetcsv($fp, 4096, ',', '"');
    $dc_terms = fgetcsv($fp, 4096, ',', '"');

    // set the count to 3 to account for the header and dc_terms rows
    $count = 3;
    while ($column = fgetcsv($fp, 4096, ',', '"')) {
        $values = array_combine($head, $column);

        foreach ($values as $key => $value) {
            if (empty($value)) {
                unset($values[$key]);
            }
        }

        $fieldsArray = array('creator', 'language', 'recordType', 'policyArea', 'subjectTopical', 'subjectCorpName', 'location');
        foreach ($fieldsArray as $field) {
            $values = changeFieldToAssociativeArray($formID, $values, $field);
        }

        $fieldsObject = array('congress', 'collectionTitle', 'findingAid', 'rights', 'type');
        foreach ($fieldsObject as $field) {
            $result = changeFieldToAssociativeObject($formID, $values, $field);
            $values = $result;
        }

        // Add provenanceDpla field using a static value
        if (!array_key_exists("provenanceDpla", $values)) {
            $values["provenanceDpla"] = "West Virginia & Regional History Center";
        } 

        // Find old object and retrieve the file field
        $oldObjectID = findOldObject($values['idno']);

        if ($oldObjectID !== null) {
            $oldObject = objects::get($oldObjectID);
            if ($oldObject !== null) {
                // the original form had 4 upload fields, so we need to check each one
                // documentUpload, imageUpload, videoUpload, audioUpload
                // the new form only has file
                $fileFields = array('documentUpload', 'imageUpload', 'videoUpload', 'audioUpload');
                $values['file'] = array();
                foreach ($fileFields as $fileField) {
                    if (isset($oldObject['data'][$fileField])) {
                        // merge the file field into the file array
                        $values['file'] = array_merge($values['file'], $oldObject['data'][$fileField]);
                        break;
                    }
                }
                // var_dump($values);
                // die();
            }
        }

        unset($values['Available At']);
        unset($values['Preview']);
        // $values['idno'] = $values['idno'] . '_' . rand(1000, 9999);

        // confirm that $values has all the required fields
        $requiredFields = requiredFormFields($formID);

        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $values)) {
                echo "Required field $requiredField not found in row $count. Please check the CSV file.". PHP_EOL;
                die();
            }
        }

        try {
            $parentObject = createObject($formID, $values);
            if ($parentObject === FALSE) {
                throw new Exception("Failed to create parent object for row $count");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (isset($parentObject)) {
                // Delete the partially created object
                objects::delete($parentObject['ID'], $formID);
                // Display message 
                echo "Failed to create object for row $count. Please check the logs for more information.";
            }
            continue;
        }

        $count++;
    }

    fclose($fp);
}

function formFieldNames($formID) {
    if (($form = forms::get($formID)) === FALSE) {
        errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
        return FALSE;
    }

    $fieldNames = array();
    foreach ($form['fields'] as $field) {
        $fieldNames[] = $field['name'];
    }
    return $fieldNames;
}

function requiredFormFields($formID) {
    if (($form = forms::get($formID)) === FALSE) {
        errorHandle::newError(__METHOD__."() - retrieving form by formID", errorHandle::DEBUG);
        return FALSE;
    }

    $requiredFields = array();
    foreach ($form['fields'] as $field) {
        // it appears that the values are stored as strings not boolean values
        if ($field['required'] === "true") {
            $requiredFields[] = $field['name'];
        }
    }
    return $requiredFields;
}

// Example usage
$csvFile = "mcppc.csv";
$formID = 151;

importData($csvFile, $formID);

?>

