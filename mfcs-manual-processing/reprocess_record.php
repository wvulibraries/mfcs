<?php
     /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: Reprocesses a Object's files
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    $objectID = '192268';
    // $objectID = '192379';
    $object = objects::get($objectID);
    $uploadField = 'file';

    $files = $object['data'][$uploadField];

    $assetsID = $files['uuid'];
    $fieldOptions = forms::getField($object['formID'], $uploadField);

    $processedFiles = files::processObjectFiles($assetsID, $fieldOptions);

    // var_dump($processedFiles);
    // die();

    $files['files'] = array_merge($files['files'], $processedFiles);
    $object['data'][$uploadField] = $files;

    objects::update($objectID, $object['formID'], $object['data'], $object['metadata'], $object['parentID'], null, $object['publicRelease']);



    $files['files'] = array_merge($files['files'], $processedFiles);
    $object['data'][$row['fieldName']] = $files;

    $return = objects::update($objectID, $object['formID'], $object['data'], $object['metadata'], $object['parentID'], null, $object['publicRelease']);

    // $object = objects::get($objectID);
    // var_dump($object);
?>