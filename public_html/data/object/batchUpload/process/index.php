<?php
// header
include("../../../../header.php");
//$engine->eTemplate("include","header");

$error = "";

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
  header('Location: /index.php?permissionFalse');
}

// Batch Uploading Logs
log::insert("BatchUpload",0,0, "Processing upload");

$changed_idnos = array();

try {
  // make sure files are set
  if(isset($engine->cleanPost['MYSQL'])) {

    // get the form id
    if(!isset($engine->cleanPost['MYSQL']['selectedFormID']) || !($form = forms::get($engine->cleanPost['MYSQL']['selectedFormID']))){
      throw new Exception('The form id is null or empty, we can not process a batch upload without an id');
    }

    // Extract Form Fields for the selected Form
    $formPost = array_intersect_key($engine->cleanPost['MYSQL'], array_flip(preg_grep('/^form_/', array_keys($engine->cleanPost['MYSQL']))));

    // remove all fields that aren't single line text and text area (paragraph text)
    // $file_upload_field_name is the name of the last upload field in the form
    $file_upload_field_name = "";
    foreach ($form['fields'] as $field) {
      if ($field['type'] != "textarea" && $field['type'] != "text" && $field['type'] != "idno") {
        if ($field['type'] == "file") $file_upload_field_name = $field['name'];
        unset($formPost['form_'.$field['name']."_"]);
      }
    }

    // make sure the form has upload fields
    if (is_empty($file_upload_field_name)) {
      throw new Exception("Form has no upload fields", 1);
    }

    // if the objectTitleField is blank, fill it with the filename
    if (is_empty($formPost['form_'.$form['objectTitleField'].'_'])) {
      $formPost['form_'.$form['objectTitleField'].'_'] = "%%filename%%";
    }

    // This exception may not be needed, but could be a good test case
    if(isnull($formPost) || is_empty($formPost)){
      throw new Exception('There are no form Fields associated with data, we need something such as IDNO');
    }

    //  File upload information and upload directory
    if (isset($engine->cleanPost['MYSQL']['fileDirectorySelector']) && !is_empty($engine->cleanPost['MYSQL']['fileDirectorySelector'])) {
      $uploadDirectory = sprintf("%s/%s",mfcs::config('ftpUploadDirectory'),$engine->cleanPost['MYSQL']['fileDirectorySelector']);
    }
    else if(isset($engine->cleanPost['MYSQL']['batch_upload_id'])){
      $uploadDirectory = sprintf("%s/%s/", mfcs::config("uploadPath"), $engine->cleanPost['MYSQL']['batch_upload_id']);
    }
    else {
      throw new Exception('There is no file directory, something went wrong with the file upload or the page has been accessed another way');
    }

  // loop through directory to get file information
  $directory       = new DirectoryIterator($uploadDirectory);

  // $form_data is the data for each file that has been uploaded
  // index will be file name
  $form_data = array();

  if(!isnull($directory)) { // If directory is not null open
    foreach ($directory as $file) { // for each file open
      // valid legit file and not a hidden system file
      if($file->isFile() && !$file->isDot()){ // is file open
        $fileinfo = array(
          'filename' => $file->getFilename(),
          'filesize' => $file->getSize(),
          'filetype' => mime_content_type($file->getPathname()),
          'path'     => $file->getPathname()
        );

        $form_data[$fileinfo['filename']] = array();

        if(!isnull($formPost) || !is_empty($formPost)) { // form post open
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
              $pattern = sprintf('/\{%s\}/',$match_no);
              $form_data[$fileinfo['filename']][$I] = preg_replace($pattern, $match[0], $form_data[$fileinfo['filename']][$I]);
            }

          }
        } // form post close

        // build $data, which is the data array passed to the object::create method
        // We are calling the object::create method directly to avoid validation checks.
        $data = array();
        foreach ($form['fields'] as $field) {
          if ($field['type'] != "textarea" && $field['type'] != "text" && $field['type'] != 'idno') continue;

          $form_data_name = sprintf("form_%s%s",$field['name'],($field['name'] == "idno")?"":"_");

          $data[$field['name']] = $form_data[$fileinfo['filename']][$form_data_name];

        }

        // Add the files data array.

        // Process the files
        // this is the $tmpArray returned from filess:processObjectUploads()
        // That method works on all the files and returns them as a single array
        // we need to do each file individually.
        // @TODO: this needs to be put into method(s) and processObjectUploads()
        // needs to be refactored

        // Generate new assets UUID and make the directory (this should be done quickly to prevent race-conditions
    		$assetsID          = files::newAssetsUUID();

        $tmpArray = array();
        if (($originalsFilepath = files::getSaveDir($assetsID,'archive')) === FALSE) {
          throw new Exception('Error creating save directory: '.$assetsID);
        }

        $tmpArray['uuid'] = $assetsID;

        // Clean the filename
        $cleanedFilename = preg_replace('/[^a-z0-9-_\.]/i','',$fileinfo['filename']);
        $newFilename = $originalsFilepath.DIRECTORY_SEPARATOR.$cleanedFilename;

        // Move the uploaded files into their new home and make the new file read-only
        if (@rename($fileinfo['path'], $newFilename) === FALSE) {
          errorHandle::newError(__METHOD__."() - renaming files: $uploadDirectory/$filename", errorHandle::DEBUG);
          return FALSE;
        }
        chmod($newFilename, 0444);

        $tmpArray['files']['archive'][] = array(
  				'name'   => $cleanedFilename,
  				'path'   => files::getSaveDir($assetsID,'archive',FALSE),
  				'size'   => filesize($newFilename),
  				'type'   => files::getMimeType($newFilename),
  				'errors' => '',
  				);

        // Lock down the originals directory
        chmod($originalsFilepath, 0555);

        // end copy

        if ($tmpArray !== TRUE) {

          // didn't generate a proper uuid for the items, rollback
          if (!isset($tmpArray['uuid'])) {
            throw new Exception('No UUID');
          }

          // Set to background processing
          $backgroundProcessing[$file_upload_field_name] = TRUE;

          $data[$file_upload_field_name] = $tmpArray;

          // end files data array

          // If the form has a user managed IDNO number,
          if (!forms::IDNO_is_managed($form['ID'])) {

            // if the IDNO is empty, set it to the current filename
            if (is_empty($data['idno'])) {
              $data['idno'] = $fileinfo['filename'];
            }

            // check if it is unique
            if (!objects::idno_is_unique($data['idno'])) {

              $original_idno = $data['idno'];
              while (1) {

                // if it is not unique, add _time() to it.
                $new_idno = sprintf("%s_%s",$original_idno,time());

                // recheck it. wash, rinse repeat.
                if (objects::idno_is_unique($new_idno)) break;
              }

              $data['idno'] = $new_idno;
              $changed_idnos[$data['idno']] = TRUE;
            }

            http::setPost("idno",$data['idno']);

          }

          if (($result = mfcs::$engine->openDB->transBegin("objects")) !== TRUE) {
            throw new Exception("unable to start database transactions", 1);
          }

          if (objects::create($form['ID'],$data,0) === FALSE) {
            $engine->openDB->transRollback();
            $engine->openDB->transEnd();

            throw new Exception("Error inserting new object.", 1);

          } // create opbject close

          // Grab the objectID of the new object
          $objectID = localvars::get("newObjectID");

          // Now that we have a valid objectID, we insert into the processing table
          if (!files::fixityInsert($tmpArray['files']['archive'][0]['path'],$objectID)) {
            errorHandle::newError(__METHOD__."() - couldn't create fixity entry.", errorHandle::DEBUG);
          }

          $sql       = sprintf("INSERT INTO `objectProcessing` (`objectID`,`fieldName`,`state`, `timestamp`) VALUES('%s','%s','1','%s')",
            mfcs::$engine->openDB->escape($objectID),
            mfcs::$engine->openDB->escape($file_upload_field_name),
            time()
            );
          $sqlResult = mfcs::$engine->openDB->query($sql);

          if (!$sqlResult['result']) {
            errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
            // Logging the error here, but we don't want to undo the whole database at this point.
          }

          if (isset($changed_idnos[$data['idno']])) $changed_idnos[$data['idno']] = $objectID;

        } // If we have the tmpArray close

        // end transactions
        $engine->openDB->transCommit();
        $engine->openDB->transEnd();

      } // is file close
    } // for each file close
  } // If directory is not null close
  else {
    throw new Exception("No files uploaded (or directory not created)", 1);
  }
}



// all the files should be removed from the upload directory now, so delete
rmdir($uploadDirectory);

errorHandle::successMsg("Successfully created records.");

if (count($changed_idnos) > 0) {
  $changed_idno_list = '<ul id="changed_idnos">';
  foreach ($changed_idnos as $idno=>$objectID) {
    $changed_idno_list .= sprintf('<li><a href="%sdataEntry/object.php?objectID=%s">%s</a></li>',mfcs::config("siteRoot"),$objectID,$idno);
  }
  $changed_idno_list .= "</ul>";

  localvars::add("changed_idno_list",$changed_idno_list);
}

}
catch (Exception $e) {

  // what happens if we aren't in a transaction?
  $engine->openDB->transRollback();
  $engine->openDB->transEnd();

  $error = sprintf("Batch Upload Processing - %s",$e);
  errorHandle::newError($error, errorHandle::DEBUG);
  errorHandle::errorMsg($error);
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

<hr />

  <p>
    The following list, if any, is a list of IDNO's that had to be modified on import
    because of a conflict. Please see the documentation for more information.
  </p>

  {local var="changed_idno_list"}


</section>

<?php
$engine->eTemplate("include","footer");
?>
