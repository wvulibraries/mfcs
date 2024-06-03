<?php

session_save_path('/tmp');

include "../public_html/header.php";

$count = 0;

function decode($data) {
    $decoded_data = base64_decode($data);
    $unserialized = unserialize($decoded_data);

    // if unserialize was unsuccessful we will attempt to repair it
    if ($unserialized === false) {
        // $decoded_data = preg_replace_callback(
        //     '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
        //     function($m){
        //         return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
        //     },
        //     $decoded_data
        // );

        //$decoded_data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $decoded_data);
        $decoded_data = preg_replace_callback('!s:\d+:"(.*?)";!s', function($m) { return "s:" . strlen($m[1]) . ':"'.$m[1].'";'; }, $decoded_data);

        $unserialized = unserialize($decoded_data);

        // var_dump($unserialized);

        // $GLOBALS['count'] = $GLOBALS['count'] + 1;
        // if ($GLOBALS['count'] > 10) {
        //     die();
        // }
    }
    return $unserialized;
}

function save_object_revisions($table, $objectID) {
    // $revisions = new revisionControlSystem('objects','revisions','ID','modifiedTime');
    // $history = revisions::history_revision_history('1000');
    // var_dump($history);
    // die();

    // $sql       = sprintf("SELECT * FROM " . $table . " WHERE ID = 92292");
    // $sqlResult = mfcs::$engine->openDB->query($sql);
    // while ($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
    //     var_dump($row);
    //     die();
    // }

    $sql       = sprintf("SELECT * FROM " . $table . " WHERE primaryID = " . $objectID);
    $sqlResult = mfcs::$engine->openDB->query($sql);
    $revisions = array();
    $formID = null;
    while ($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
        $metadata = is_array($row['metadata']) ? $row['metadata'] : decode($row['metadata']);
        if ($metadata === false) {
            print ('Error: Decoding metadata - Record ' . $row['ID'] . ' For Object ' . $objectID . PHP_EOL);
            var_dump($row);
        }
        else {
            $row['metadata'] = $metadata;
            $data = is_array($row['metadata']['data']) ? $row['metadata']['data'] : decode($row['metadata']['data']);
            if ($data === false) {
                print (' Error: Decoding data - Record ' . $row['ID'] . ' For Object ' . $objectID . PHP_EOL);
            }
            else {
                $row['metadata']['data'] = $data;
                if ($formID === null) {
                    $formID = $row['metadata']['formID'];
                }
                elseif ($formID != $row['metadata']['formID']) {
                    print (' Error: formIDs do not match previous revision ' . $row['ID'] . ' For Object ' . $objectID . PHP_EOL);
                    $formID = $row['metadata']['formID'];
                }
            }
        }
        array_push( $revisions, $row );
    }

    if ((file_exists('./' . $table . '/' . $objectID .'.json') === false) && (count($revisions) > 0 )) {
        file_put_contents('./' . $table . '/' . $objectID .'.json', print_r(json_encode($revisions), true));
    }
}

// Set Table name
$table = 'revisions';
if (!file_exists($table)) {
    mkdir($table, 0777, true);
}

// Save each object revisions
$sql       = sprintf("SELECT * FROM objects");
$sqlResult = mfcs::$engine->openDB->query($sql);
$count = 0;
while ($row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC)) {
    $filename = './' . $table . '/' . $row['ID'] .'.json';

    if (file_exists($filename) && (filesize($filename) === 0)) {
        // delete empty files 
        // can happen if job stalls or is canceled
        unlink($filename);
    }

    if (file_exists($filename) === false) {
        save_object_revisions($table, $row['ID']);
        $count = $count + 1;
        // if ($count > 10000) {
        //     die();
        // }
    }
}

print "Done." . PHP_EOL;
?>
