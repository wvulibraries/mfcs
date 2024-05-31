<?php

session_save_path('/tmp');

include "../public_html/header.php";

function prepare_object( $item ) {
  $utf_encoded = mb_convert_encoding( $item, 'UTF-8' );
  return strip_tags($utf_encoded, '<b><strong><i><u><em>');
}

// Set Table name
$table = 'objects';

if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

$forms = forms::get();

$count = 0;
//foreach ($forms as $form) {
  $objects = objects::getAllObjectsForForm(102);

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
        file_put_contents('./' . $table . '/'  . $object["formID"] . '/' . $object["ID"] .'.json', print_r(json_encode($object), true));
    }
  }
//}

print "Exported " . $count . " Objects.\n";

?>
