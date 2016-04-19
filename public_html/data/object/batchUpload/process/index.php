<?php
// header
include("../../../../header.php");
//$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

// Batch Uploading Logs
log::insert("BatchUpload",0,0, "Processing upload");


print "<pre>";
var_dump($engine->cleanPost['MYSQL']);
print "</pre>";


// get file ID and Path
if(isset($engine->cleanPost['MYSQL']['FileUploadBox'])){

    $fileUploadID    = $engine->cleanPost['MYSQL']['FileUploadBox'];
    $uploadDirectory = "/home/mfcs.lib.wvu.edu/data/working/uploads/$fileUploadID/";

    // loop through directory to get file information
    $directory       = new DirectoryIterator($uploadDirectory);

    foreach ($directory as $file) {
        if($file->isFile() && !$file->isDot()){
            print "<pre>";
            var_dump($file->getFilename() . "\n");
            print "</pre>";

            print "<pre>";
            var_dump($file->getSize() . "\n");
            print "</pre>";
        }
    }
}

// insert the file into a new object

// make sure that the object exsists and is searchable

// should there be a check to make sure that fixity is being run

// should there be something that puts the file into the file processing (is done on insert?)

?>


<?php
//$engine->eTemplate("include","footer");
?>
