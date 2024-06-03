<?php

session_save_path('/tmp');

include "../public_html/header.php";

$sql       = sprintf("SELECT * FROM `filesChecks` WHERE `checksum` IS NOT NULL");
$sqlResult = mfcs::$engine->openDB->query($sql);

$fields = array('Identifier', 'Object ID', 'File', 'Checksum');

$fp = fopen('file.csv', 'w');
fputcsv($fp, $fields);

while($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
  # var_dump($row);

  $data = array(
    $row['ID'],
    $row['objectID'],
    basename($row['location']).PHP_EOL,
    $row['checksum'],
  );
  fputcsv($fp, $data);
}

fclose($fp);
?>
