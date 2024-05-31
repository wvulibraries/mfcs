<?php

session_save_path('/tmp');

include "../public_html/header.php";

function prepare_object( $item ) {
  $utf_encoded = mb_convert_encoding( $item, 'UTF-8' );
  return strip_tags($utf_encoded, '<b><strong><i><u><em>');
}

$id = 161701;

$object = objects::get($id);

var_dump($object);
die();

// ensure all fields are utf8
array_walk_recursive( $object, function (&$entry) { $entry = prepare_object( $entry ); } );

// create json file
file_put_contents('./' . 'objects' . '/'. $id .'.json', print_r(json_encode($object), true));


?>
