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
        if(!isset($engine->cleanPost['MYSQL']['selectedFormID']) || !($form = forms::get($engine->cleanPost['MYSQL']['selectedFormID']))){
          throw new Exception('The form id is null or empty, we can not process a batch upload without an id');
        }

        // Extract Form Fields for the selected Form
        $formPost = array_intersect_key($engine->cleanPost['MYSQL'], array_flip(preg_grep('/^form_/', array_keys($engine->cleanPost['MYSQL']))));

        // remove all fields that aren't single line text and text area (paragraph text)
        foreach ($form['fields'] as $field) {
          if ($field['type'] != "textarea" && $field['type'] != "text") {
            unset($formPost['form_'.$field['name']."_"]);
          }
        }

        // if the objectTitleField is blank, fill it with the filename
        if (is_empty($formPost['form_'.$form['objectTitleField'].'_'])) {
          $formPost['form_'.$form['objectTitleField'].'_'] = "%%filename%%";
        }

        // This exception may not be needed, but could be a good test case
        if(isnull($formPost) || is_empty($formPost)){
          throw new Exception('There are no form Fields associated with data, we need something such as IDNO');
        }

        if (isset($formPost['form_idno']) && is_empty($formPost['form_idno'])) {
          throw new Exception('IDNO has been left blank.');
        }

        //  File upload information and upload directory
        if(isset($engine->cleanPost['MYSQL']['batch_upload_id'])){
            $uploadDirectory = sprintf("%s/%s/",
                                       mfcs::config("uploadPath"),
                                       $engine->cleanPost['MYSQL']['batch_upload_id']
                                     );
        }
        else {
            throw new Exception('There is no file directory, something went wrong with the file upload or the page has been accessed another way');
        }

        // loop through directory to get file information
        $directory       = new DirectoryIterator($uploadDirectory);

        // $form_data is the data for each file that has been uploaded
        // index will be file name
        $form_data = array();

        if(!isnull($directory)){
          foreach ($directory as $file) {
            // valid legit file and not a hidden system file
            if($file->isFile() && !$file->isDot()){
              $fileinfo = array(
                'filename' => $file->getFilename(),
                'filesize' => $file->getSize(),
                'filetype' => mime_content_type($file->getPathname())
              );

              $form_data[$fileinfo['filename']] = array();

              if(!isnull($formPost) || !is_empty($formPost)){
                // replace the formPost with the Filenames and Other Values

                $matches = array();
                if (!is_empty($engine->cleanPost['RAW']['regEx'])) {
                  preg_match_all($engine->cleanPost['RAW']['regEx'],$fileinfo['filename'],$matches);
                }

                foreach ($formPost as $I=>$V) {

                  $V = preg_replace('/\%\%filename\%\%/i', $fileinfo['filename'], $V);
                  $V = preg_replace('/\%\%filesize\%\%/i', $fileinfo['filesize'], $V);
                  $V = preg_replace('/\%\%mimetype\%\%/i', $fileinfo['filetype'], $V);

                  $form_data[$fileinfo['filename']][$I] = $V;

                  // handle regular expression matches
                  foreach ($matches as $match_no=>$match) {
                    $pattern = sprintf('/\{%s\}/i',$match_no);
                    $form_data[$fileinfo['filename']][$I] = preg_replace($pattern, $match[0], $V);
                  }

                }
              }

            print "<pre>";
            var_dump($form_data);
            print "</pre>";

            // build $data, which is the data array passed to the object::create method
            // We are calling the object::create method directly to avoid validation checks.

            // if (objects::create($formID,$values,0) === FALSE) {
            // 	$engine->openDB->transRollback();
            // 	$engine->openDB->transEnd();
            // 	errorHandle::newError(__METHOD__."() - Error inserting new object.", errorHandle::DEBUG);
            // 	return FALSE;
            // }

            // Grab the objectID of the new object
            $objectID = localvars::get("newObjectID");
          }
        }
        }
    }


}
catch (Exception $e) {
    errorHandle::newError("Batch Upload Processing - :".$e, errorHandle::DEBUG);
}

localVars::add("results",displayMessages());

// insert the file into a new object

// make sure that the object exsists and is searchable

// should there be a check to make sure that fixity is being run

// should there be something that puts the file into the file processing (is done on insert?)

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1> Batch Submission </h1>
	</header>
	<ul class="breadcrumbs">
		{local var="breadcrumbs"}
	</ul>

	{local var="results"}


	{local var="list"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
