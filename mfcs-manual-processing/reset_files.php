<?php
    /**
     * @author Tracy A. McCormick <tam0013@mail.wvu.edu>
     * @created  2024-04-16
     * Description: Reindexes a Object's files
     */

    session_save_path('/tmp');

    include "../public_html/header.php";

    $objectID = '192379';
    $object = objects::get($objectID);

    // $originalFiles = $object['data']['file']['files'];
    // var_dump($originalFiles);
    // die();

    // $objectID = '192878';
    //$objectID = '192379';
    $object = objects::get($objectID);
    $uploadField = 'file';
    // uuid without -'s
    $uuidclean = str_replace('-', '', $object['data']['file']['uuid']);
    // add / between each character
    $uuidclean = implode('/', str_split($uuidclean));
    // add the original uuid to the end of the new string
    $uuidpath = $uuidclean . '/' . $object['data']['file']['uuid'];
    $path = "/home/mfcs.lib.wvu.edu/data/";
   
    $archives = $path . "archives/mfcs/" . $uuidpath;
    $exports = $path . "exports/" . $uuidpath;
    
    // Check if the directory exists
    // if (is_dir($path)) {
    //     // Initialize an empty array to store file information
    //     $filesArray = [];
    
    //     // Get an array of files and directories in the specified directory
    //     $files = scandir($path);
    
    //     // Get subdirectories excluding . and ..
    //     $subdirs = array_diff($files, array('..', '.'));
    
    //     // Loop through subdirectories to gather file information
    //     foreach ($subdirs as $subdir) {
    //         $subdirpath = $path . '/' . $subdir;
    //         echo "Subfolder: $subdir\n";
    
    //         $subfiles = scandir($subdirpath);
    //         $subfiles = array_diff($subfiles, array('..', '.'));
    
    //         foreach ($subfiles as $subfile) {
    //             $filepath = $subdirpath . '/' . $subfile;
    //             $fileInfo = [
    //                 "name" => $subfile,
    //                 "path" => $uuidpath,
    //                 "size" => filesize($filepath),
    //                 "type" => mime_content_type($filepath),
    //                 "errors" => "" // You can add error handling logic here if needed
    //             ];
    //             $filesArray[$subdir][] = $fileInfo;
    //         }
    //     }
    
    //     // Output the filesArray for demonstration
    //     // var_dump($filesArray);
    // } else {
    //     echo "The directory does not exist.";
    // }

    // setup the archives section of the $filesArray
    $filesArray = array();
    $filesArray["archive"] = getDirectory($archives, $uuidpath);
    $filesArray["processed"] = getDirectory($exports."/processed", $uuidpath);
    $filesArray["combine"] = getDirectory($exports."/combine", $uuidpath);
    $filesArray["thumbs"] = getDirectory($exports."/thumbs", $uuidpath);
    $filesArray["ocr"] = getDirectory($exports."/ocr", $uuidpath);
    
    // var_dump($filesArray);
    // die();

    // testing $originalFiles should match $filesArray
    
    // Compare arrays
    // $differences = array_diff_assoc($originalFiles, $filesArray);

    // echo "Original Files:";
    // var_dump($originalFiles);
    // echo "New Files:";
    // var_dump($filesArray);

    // if (empty($differences)) {
    //     echo "Arrays match.";
    // } else {
    //     echo "Arrays do not match. Differences found:";
    //     print_r($differences);
    // }

    // var_dump($filesArray);
    // die();

    // $files['files'] = array_merge($files['files'], $processedFiles);

    // Update the object with the new files array
    $object['data'][$uploadField]['files'] = $filesArray;

    // Update the object
    objects::update($objectID, $object['formID'], $object['data'], $object['metadata'], $object['parentID'], null, $object['publicRelease']);

    // $object = objects::get($objectID);
    // var_dump($object);  
    // var_dump($filesArray);

    // Function to get file details
    function getFileDetails($filePath, $displayPath)
    {
        $fileInfo = pathinfo($filePath);
        $size = filesize($filePath);

        return array(
            'name' => $fileInfo['basename'],
            'path' => $displayPath,
            'size' => $size,
            'type' => mime_content_type($filePath),
            'errors' => '' // You can handle errors if needed
        );
    }

    // Function to process files in a directory using getFiles function
    function processFolder($folderPath)
    {
        $filesArray = array();
        $filesArray = getFiles($filesArray, $folderPath);
        return $filesArray;
    }

    // Function to get files using provided logic
    function getFiles($filesArray, $path, $displayPath)
    {
        // Check if the directory exists
        if (is_dir($path)) {
            // Get an array of files and directories in the specified directory
            $files = scandir($path);
        
            // Filter out . and ..
            $files = array_diff($files, array('..', '.'));

            // Loop through files to gather file information
            foreach ($files as $file) {
                $filePath = $path . '/' . $file;
                $fileInfo = getFileDetails($filePath, $displayPath);
                $filesArray[] = $fileInfo;
            }
        } else {
            echo "The directory does not exist.";
        }
        return $filesArray;
    }

    // Function to process files in archives directory
    function getDirectory($path, $displayPath)
    {
        $filesArray = array();
        $filesArray = getFiles($filesArray, $path, $displayPath);
        return $filesArray;
    }  
?>