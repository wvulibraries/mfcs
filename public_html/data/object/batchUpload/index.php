<?php
// header
include("../../../header.php");
$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

$fileDirectorySelector_options = files::get_upload_directories();
localvars::add("fileDirectorySelector_options",$fileDirectorySelector_options);

// Batch Uploading Logs
log::insert("BatchUpload",0,0, "Batch upload screen was loaded.");

// Setup Form lists
localVars::add("formList", listGenerator::createFormDropDownList());
?>

<section>

    <header class="page-header">
        <h1> Batch Upload </h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/data/object/batchUpload/">Batch Upload</a></li>

        <li class="pull-right noDivider">
            <a href="https://github.com/wvulibraries/mfcs/wiki/Batch-Uploading" target="_blank">
                <i class="fa fa-book"></i> Documentation
            </a>
        </li>
    </ul>

    <div class="row-fluid batchUpload">
        <form action="/data/object/batchUpload/process/" method="post">
          {engine name="csrf"}

        <div class="span4">
            <h2> Upload Here </h2>
            <div id="selectFormID" class="selectFormContainer group control-group">
                <label> Select Form to Upload To </label>
                {local var="formList"}
            </div>

            <h3> Metadata From Filename? </h3>
            <div class="selectFormContainer group control-group">
                <label for="regExBool"> <input type="checkbox" name="regExBool" id="regExBool" value="value"/> Use RegEx to parse filename? </label>
                <div class="toggleRegEx hide">
                    <br>
                    <div class="alert alert-warning"><p> Please read all documentation before attempting to use the regular expressions to parse filenames.  All file names must be similar or the metadata will not be correct. </p> </div>

                    <label for="exampleFileName"> Example File Name </label>
                    <input type="text" name="exampleFileName" id="exampleFileName" value=""/>

                    <label for="regEx"> Regular Expression </label>
                    <input type="text" name="regEx" id="regEx" value=""/>

                    <a href="javascript:void(0)" class="previewRegEx btn btn-primary pull-right"> Preview Reg Ex </a>
                    <br><br><br>

                    <div class="regExPreview pull-left"></div>
                </div>
            </div>


            <div id="batchUploadFiles" class="uploadFiles" style="display: inline-block;">
                <div class="fineUploader" style="display: inline-block;">
                    <div class="qq-uploader">
                        <div class="qq-upload-drop-area" style="display: none;">
                            <div class="uploadText">
                                <i class="fa fa-dropbox fa-4x"></i><br>
                                <br>
                                Drop Files Here
                            </div>
                        </div>
                        <div class="qq-upload-button" style="position: relative; overflow: hidden; direction: ltr;">
                            <div>
                                <div class="uploadText">
                                    <i class="fa fa-upload fa-4x"></i><br>
                                    Drag or Click Here<br>
                                    To Upload Files
                                </div>
                            </div>
                            <input name="file" style="position: absolute; right: 0px; top: 0px; font-family: Arial; font-size: 118px; margin: 0px; padding: 0px; cursor: pointer; opacity: 0;" type="file" />
                        </div>
                        <span class="qq-drop-processing">
                            <span>Processing files...</span>
                            <span class="qq-drop-processing-spinner"></span>
                        </span>

                        <ul class="qq-upload-list"></ul>
                    </div>
                </div>
            </div>

            <div id="fileDirectorySelector">
              <select name="fileDirectorySelector">
                <option value="">-- Select Directory --</option>
                {local var="fileDirectorySelector_options"}
              </select>
            </div>

            <input type="submit" class="btn pull-left batchSubmit"/>
        </div>

        <div class="span8 batchUploadScreen">
            <h2> Form Preview </h2>
            <!-- Preview will appear here -->
            <div id="batchFormPreview"> </div>
        </div>

        </form>
    </div>
</section>

<?php
$engine->eTemplate("include","footer");
?>
