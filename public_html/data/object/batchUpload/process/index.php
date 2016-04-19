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

try {
    // make sure files are set
    if(isset($engine->cleanPost['MYSQL'])){

        // get the form id
        if(isset($engine->cleanPost['MYSQL']['selectedFormID'])){
            $formID = $engine->cleanPost['MYSQL']['selectedFormID'];

            if(isnull($formID) || is_empty($formID)){
                throw new Exception('The form id is null or empty, we can not process a batch upload without an id');
            }
        }

        // Extract Form Fields for the selected Form
        $formPost = array_intersect_key($engine->cleanPost['MYSQL'], array_flip(preg_grep('/^form_/', array_keys($engine->cleanPost['MYSQL']))));

        // This exception may not be needed, but could be a good test case
        if(isnull($formPost) || is_empty($formPost)){
            throw new Exception('There are no form Fields associated with data, we need something such as IDNO');
        }

        //  File upload information adn upload directory
        if(isset($engine->cleanPost['MYSQL']['FileUploadBox'])){
            $fileUploadID    = $engine->cleanPost['MYSQL']['FileUploadBox'];
            $uploadDirectory = "/home/mfcs.lib.wvu.edu/data/working/uploads/$fileUploadID/";
        } else {
            throw new Exception('There is no file directory, something went wrong with the file upload or the page has been accessed another way');
        }

        // loop through directory to get file information
        $directory       = new DirectoryIterator($uploadDirectory);

        foreach ($directory as $file) {
            // valid legit file and not a hidden system file
            if($file->isFile() && !$file->isDot()){
                $fileinfo = array(
                    'filename' => $file->getFilename(),
                    'filesize' => $file->getSize(),
                    'filetype' => mime_content_type($file->getPathname())
                );


            }
        }
    }


} catch (Exception $e) {
    errorHandle::newError("Batch Upload Processing - :".$e, errorHandle::DEBUG);
}

function formatStringVariables($formFields, $fileinfo, $regEX = null){ 

}


// $testArray =  array(
//     "selectedFormID"  => 68,
//     "exampleFileName" => '',
//     "regEx"           => '',
//     "file"            => '',
//     "FileUploadBox"   => "43ded3c9fa068f041d1402531781650b",
//     "form_idno"       => "%%fileName%%",
//     "form_title"      => "%%fileName%%__%%filesize%%__%%filetype%%"
// );
//
// function stringVariables($input){
//     return preg_replace('')
// }

// insert the file into a new object

// make sure that the object exsists and is searchable

// should there be a check to make sure that fixity is being run

// should there be something that puts the file into the file processing (is done on insert?)

?>


<?php
//$engine->eTemplate("include","footer");
?>
