<?php
include("../header.php");

// Permissions Access
if (!mfcsPerms::evaluatePageAccess(2)) {
    header('Location: /index.php?permissionFalse');
    exit;
}

$permissions = true;

try {
    $validate_return = valid::validate([
        "metadata" => false,
        "authtype" => "viewer",
        "productionReady" => true
    ]);

    if ($validate_return !== true) {
        $permissions = false;
        throw new Exception($validate_return);
    }

    log::insert("Data View: Object", $engine->cleanGet['MYSQL']['objectID'], $engine->cleanGet['MYSQL']['formID']);

    localvars::add("objectID", $engine->cleanGet['MYSQL']['objectID']);

    // Metadata Tab Stuff
    $form = forms::get($engine->cleanGet['MYSQL']['formID']);
    if ($form === false) {
        throw new Exception("Error retrieving form.");
    }

    localvars::add("formName", $form['title']);

    // Build the form for displaying
    $builtForm = formBuilder::build($engine->cleanGet['MYSQL']['formID'], $engine->cleanGet['MYSQL']['objectID']);
    if ($builtForm === false) {
        throw new Exception("Error building form.");
    }

    localvars::add("form", $builtForm);

    // Editor information
    $object = objects::get($engine->cleanGet['MYSQL']['objectID']);
    if ($object === false) {
        throw new Exception("Error retrieving object.");
    }

    localvars::add("createdByUsername", empty($object['createdBy']) ? "Unavailable" : users::get($object['createdBy'])['username']);
    localvars::add("createdOnDate", date('D, d M Y H:i', $object['createTime']));
    localvars::add("modifiedByUsername", empty($object['modifiedBy']) ? "Unavailable" : users::get($object['modifiedBy'])['username']);
    localvars::add("modifiedOnDate", date('D, d M Y H:i', $object['modifiedTime']));

    // Build the files list for displaying
    $filesViewer = files::buildFilesPreview($engine->cleanGet['MYSQL']['objectID']);
    localvars::add("filesViewer", $filesViewer);

    // Project Tab Stuff
    $selectedProjects = objects::getProjects($engine->cleanGet['MYSQL']['objectID']);
    localvars::add("projectOptions", projects::generateProjectChecklist($selectedProjects));

    // Children Tab Stuff
    $formList = listGenerator::generateFormSelectList($engine->cleanGet['MYSQL']['objectID']);
    if ($formList === false) {
        throw new Exception("Error getting Forms Listing");
    }
    localvars::add("formList", $formList);

    $childList = listGenerator::generateChildList($engine->cleanGet['MYSQL']['objectID']);
    localvars::add("childrenList", empty($childList) ? 'No children available' : $childList);

} catch (Exception $e) {
    log::insert("Data View: Object: Error", 0, 0, $e->getMessage());
    errorHandle::errorMsg($e->getMessage());
}

localvars::add("leftnav", navigation::buildProjectNavigation($engine->cleanGet['MYSQL']['formID']));
localvars::add("results", displayMessages());

$engine->eTemplate("include", "header");
?>

<section>
    <header class="page-header">
        <h1>View Object</h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}dataEntry/selectForm.php">Select a Form</a></li>

        <!-- Float Right -->
        <?php if (mfcsPerms::isEditor($engine->cleanGet['MYSQL']['formID'])) { ?>
            <li class="pull-right noDivider"><a href="{local var="siteRoot"}dataEntry/object.php?objectID={local var="objectID"}">Edit Object</a></li>
        <?php } ?>
        <li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Object-Viewing" target="_blank"><i class="fa fa-book"></i> Documentation</a></li>
    </ul>

    <div class="container-fluid">
        <div class="span3">
            <ul class="menu">
                {local var="leftnav"}
            </ul>
        </div>

        <div class="span9">
            <div class="row-fluid" id="results">
                {local var="results"}
            </div>

            <?php if ($permissions === true) { ?>
                <div class="row-fluid">
                    <ul class="nav nav-tabs">
                        <li><a data-toggle="tab" href="#metadata">Metadata</a></li>
                        <li><a data-toggle="tab" href="#files" id="filesTab">Files</a></li>
                        <li><a data-toggle="tab" href="#project">Project</a></li>
                        <li><a data-toggle="tab" href="#publicUrls">Public Urls</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane" id="metadata">
                            {local var="form"}
                            <?php if (!is_null($engine->cleanGet['MYSQL']['objectID'])) { ?>
                                <p><b>Created by:</b> <em>{local var="createdByUsername"} on {local var="createdOnDate"}</em></p>
                                <p><b>Modified by:</b> <em>{local var="modifiedByUsername"} on {local var="modifiedOnDate"}</em></p>
                            <?php } ?>
                        </div>
                        <div class="tab-pane" id="files">
                            <a href="/dataView/allfiles.php?objectID={local var="objectID"}" class="btn btn-primary">Download All Files (Zip)</a><br><br>
                            {local var="filesViewer"}
                        </div>

                        <div class="tab-pane" id="project">
                            <h2>Change Project Membership</h2>

                            <form action="{phpself query="true"}" method="post">
                                {local var="projectOptions"}
                                {engine name="csrf"}
                                <input type="submit" class="btn btn-primary" name="projectForm">
                            </form>
                        </div>
                        <div class="tab-pane" id="publicUrls">
                            <h2>Public Urls</h2>
                            <p>Note: If the public URL has not been registered with MFCS yet, the first item may be blank.</p>
                            <ul>
                                {local var="publicUrls"}
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } // Permissions ?>
        </div>
    </div>
</section>

<!-- Modal Preview -->
<div class="modal imagePreviewModal" id="modal" tabindex="-1" role="dialog" aria-labelledby="preview modal" aria-hidden="true">
    <div class="modalContainer">
        <div class="modal-header">
            <button type="button" class="close" aria-hidden="true">×</button>
            <h3>File Preview</h3>
        </div>
        <div class="modal-body">
            <div class="video-container">
                <iframe src="" frameborder="0" id="iFrameTarget"></iframe>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn close" aria-hidden="true">Close</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        // Show first tab on page load
        $(".nav-tabs a:first").tab("show");

        // Disable form input fields
        $(":input").not('.btn,.close').prop("disabled", true);

        // Remove form submits
        $(":input[type=submit]").remove();

        // Remove file upload boxes
        $('.fineUploader').remove();

        // Remove form actions
        $("form").removeAttr("action");
    });
</script>

<?php
$engine->eTemplate("include", "footer");
?>
