<?php 
/** 
 * Gets the headings from the linked form by ID
 *
 * @param int $id - identification of the record
 * @param string $type - the form field name identified in the metadata form defaults to 'title'
 * @author David J. Davis
 */
  function getHeadingByID($id,$type='title') {
    $object = objects::get($id);

    // echo "ID: $id\n";
    // var_dump($object);

    return($object['data'][$type]);
    
    // when testing export to see what is in the object use this line instead
    //return($object['data']);
  }

  /** 
  * Takes an array and implodes it from the heading ID
  * ex: array_to_string((array)$object['data']['subject']) 
  *
  * @param array $obj_array - comes from the object in the export script
  * @param type $type - used in the getHeadingByID statements [[ OPTIONAL || DEFAULTS TO TITLE ]]
  * @author David J. Davis
  * @return string - imploded string of subject names 
  */
  function array_to_string($obj_array, $type='title'){ 
    $temp = array();
    
    if (is_array($obj_array)) {
        foreach ($obj_array as $headingID) {
            $temp[] = getHeadingByID($headingID, $type);
        }
    } else {
        // Optionally handle the case where $obj_array is not an array
        // For example, log an error or set $temp to an empty array
        error_log('array_to_string expects an array as the first parameter.');
    }

    sort($temp);
    return implode("|||", array_filter($temp));
  }

  /** 
  * Removes excess spacing and tags from a string 
  *
  * @param string $str 
  * @author David J. Davis
  * @return the cleaned string 
  */
  function clean_tags_spaces($str){
    $str = html_entity_decode($str);  # removes ascii typings for spacing and special chars
    $str = strip_tags($str);  # removes html 
    $str = str_replace(array("\n", "\r", "\t"), '', $str); # removes line returns and newlines and tabs
    $str = trim($str); # removes spaces at beginning and end of string 
    $str = preg_replace( '/[^[:print:]]/', '',$str);
    
    return $str;
  }

  /** 
  * Return name of object instead of id 
  *
  * @param string array of id's
  * @author Tracy A. McCormick
  * @return array of names
  */
  function getNamesById($obj_array, $type){
    $temp = array();
    foreach ($obj_array as $id) {
      $temp[] = getHeadingByID($id,$type);
    }
    sort($temp);    
    return $temp;
  }

?> 