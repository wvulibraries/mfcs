<?php

include "../../../header.php";

try {

  if (!isset($engine->cleanGet['MYSQL']['formID']) ) {
    throw new Exception("Error Processing Request");
  }

  $form = forms::get($engine->cleanGet['MYSQL']['formID']);
  if (($form_fields = forms::rebuild_form_fields($form['fields'])) === false) {
    die('Error rebuilding form fields');
  }

  // $dc_fields['field_name'] = array(); // options broken out
  $dc_fields = array();
  foreach ($form['fields'] as $field) {
    if (($return = exporting::determine_metadataStandard($field,"DC")) !== false) {
      $dc_fields[$field['name']] = $return;
    }

  }

  $time = time();

  // setup the file system
  $filesExportBaseDir = "/home/mfcs.lib.wvu.edu/public_html/exports/Dublin_Core/files/".$time;
  if (!mkdir($filesExportBaseDir)) {
  	  throw new Exception("Couldn't Make Directory : Base : ".$filesExportBaseDir);
  }
  if (!mkdir($filesExportBaseDir."/records")) {
  	throw new Exception("Couldn't Make Directory : records");
  }
  if (!mkdir($filesExportBaseDir."/jpg")) {
  	throw new Exception("Couldn't Make Directory : JPG");
  }
  if (!mkdir($filesExportBaseDir."/thumbs")) {
  	throw new Exception("Couldn't Make Directory : Thumbs");
  }

  // Output Files:
  $outFileName        = "dublin-core_".($time).".xml";
  $outFile            = sprintf("%s/%s",$filesExportBaseDir,$outFileName);

  $outDigitalFileName = "holt-files_".($time).".tar.gz";
  $outDigitalFile     =  sprintf("%s/%s",$filesExportBaseDir,$outDigitalFileName);

  $outFileURL         = sprintf("/exports/Dublin_Core/files/%s",$time);

  localvars::add("outFile",           $outFile);
  localvars::add("outFileName",       $outFileName);
  localvars::add("outDigitalFile",    $outDigitalFile);
  localvars::add("outDigitalFileName",$outDigitalFileName);

  $exportFilenames = array();

  $objects = objects::getAllObjectsForForm($engine->cleanGet['MYSQL']['formID']);
  foreach ($objects as $object) {

    // if the object is not set for public release skip it
    if (isset($object['data']['publicRelease']) && strtolower($object['data']['publicRelease']) == "no") continue;

    $filename = preg_replace("/\W/","_",$object['idno']).".xml";
    $filepath = $filesExportBaseDir."/records";

    $lines = '<?xml version="1.0"?>'."\n";
    $lines .= '<record'."\n";
    $lines .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
    $lines .= 'xsi:schemaLocation="http://example.org/myapp/ http://example.org/myapp/schema.xsd"'."\n";
    $lines .= 'xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n\n";

    foreach ($object['data'] as $name => $data) {

      // if the field isn't set to public release, skip the field.
      if (strtolower($form_fields[$name]["publicRelease"]) == "false") continue;

      // if the data schema options are set to discard, don't do anything with it.
      if (isset($dc_fields[$name]['options']['discard']) && $dc_fields[$name]['options']['discard'] == "true") continue;

      if (isset($dc_fields[$name])) {

        // this handles selects from another form
        if (is_array($data) && isset($form_fields[$name]['choicesField'])) {
          $headings = get_multiselect_by($data,$form_fields[$name]['choicesField']);

          foreach ($headings as $heading) {
            $lines .= sprintf("<dc:%s>%s</dc:%s>\n",
              strtolower($dc_fields[$name]['predicate']),
              $heading,
              strtolower($dc_fields[$name]['predicate'])
            );
          }

        }
        else {

          // Handle Combines
          if (isset($dc_fields[$name]["options"]["combine"])) {

            $temp_data = array();
            foreach ($dc_fields[$name]["options"]["combine"] as $combine_field) {
              $temp_data[] = exporting::get_data_value($object,$form_fields,$dc_fields,$combine_field);
            }

            $data = implode((isset($dc_fields[$name]["options"]['delimiter']))?$dc_fields[$name]["options"]['delimiter']:" ",$temp_data);

          }
          else {

            // if the field is blank, and the field is read only, and there is a default value
            $data = exporting::get_data_value($object,$form_fields,$dc_fields,$name);

          }

          $lines .= sprintf("<dc:%s>%s</dc:%s>\n",
            strtolower($dc_fields[$name]['predicate']),
            $data,
            strtolower($dc_fields[$name]['predicate'])
          );
        }

      }

    }

    $lines .= '</record>'."\n";


    if(!file_put_contents($filepath."/".$filename, $lines)) {
      errorHandle::newError(__METHOD__."() - Error creating file: ".$filepath."/".$filename, errorHandle::DEBUG);
    }

    $exportFilenames[$object['idno']] = $filename;

  }

  natsort($exportFilenames);

}
catch (Exception $e) {
  log::insert("Exporting: Dublin Core: Error",0,0,$e->getMessage());
  die($e->getMessage());
}

?>

<ul>
<?php foreach ($exportFilenames as $filename) {

  printf('<li><a href="%s/records/%s">%s</a></li>',$outFileURL,$filename,$filename);

} ?>
</ul>
