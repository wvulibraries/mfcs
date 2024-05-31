<?php

session_save_path('/tmp');

include "../public_html/header.php";

function prepare_object( $item ) {
  // encoding the object this way caused issues in the importing 
  // process, in mfcs ruby code.
  //$utf_encoded = mb_convert_encoding( $item, 'UTF-8' );
  return strip_tags($item, '<b><strong><i><u><em>');
}

// Set Table name
$table = 'objects';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

$forms = forms::get();

$count = 0;
foreach ($forms as $form) {
  echo "Form: " . $form['ID'] . "\n";
  $objects = objects::getAllObjectsForForm($form["ID"]);

  foreach ($objects as $object) {
    $count++;
    if ($object != null) {
        if (!is_dir('./' . $table . '/' . $object["formID"])) {
          // dir doesn't exist, make it
          mkdir('./' . $table . '/' . $object["formID"]);
        }

        // get object projects associations
        $projects = projects::getAllObjectProjects($object["ID"]);
        if (count($projects) > 0) {
          $object["objectProject"] = $projects[0];
        }

        // ensure all fields are utf8
        array_walk_recursive( $object, function (&$entry) { $entry = prepare_object( $entry ); } );

        // create json file
        // file_put_contents('./' . $table . '/'  . $object["formID"] . '/' . $object["ID"] .'.json', print_r(json_encode($object), true));
        file_put_contents('./' . $table . '/'  . $object["formID"] . '/' . $object["ID"] .'.json', json_encode($object));

        // $fp = fopen('./' . $table . '/'  . $object["formID"] . '/' . $object["ID"] .'.json', 'w');
        // fwrite($fp, json_encode(jsonRemoveUnicodeSequences($object)));
        // fclose($fp);
    }
  }
}

print "Exported " . $count . " Objects.\n";

?>