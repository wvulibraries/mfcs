<?php 
  /** 
   * Gets the headings from the linked form by ID
   *
   * @param int $id - identification of the record
   * @param string $type - the form field name identified in the metadata form defaults to 'title'
   * @author David J. Davis
   */
  function getHeadingByID($id, $type = 'title') {
    $object = objects::get($id);
    if (isset($object['data'][$type])) {
        return $object['data'][$type];
    } else {
        return ''; // Return an empty string or handle the missing index case appropriately
    }
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
      if (is_array($obj_array) && !empty($obj_array)) {
          foreach ($obj_array as $headingID) {
              $temp[] = getHeadingByID($headingID, $type);
          }
          sort($temp);
          return implode("|||", array_filter($temp));
      } else {
          return ''; // or handle the case accordingly
      }
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

?> 