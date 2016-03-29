<?php
// header
include("../../header.php");
$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

if(isset($engine->cleanPost['MYSQL'])){

    print "<pre>";
    var_dump($engine->cleanPost['MYSQL']);
    print "</pre>";

}

// Batch Uploading Logs
// log::insert("BatchUpload",0,0, "Batch upload screen was loaded.");

// Setup Form lists
localVars::add("formList", listGenerator::createFormDropDownList());
?>
<section>

    <header class="page-header">
        <h1> Process Upload </h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/batchUpload/">Batch Upload</a></li>
        <li><a href="{local var="siteRoot"}/batchUpload/processUpload">Process Upload</a></li>

        <li class="pull-right noDivider">
            <a href="#batchUploadDocumentation" target="_blank">
                <i class="fa fa-book"></i> Documentation
            </a>
        </li>
    </ul>



</section>

<?php
$engine->eTemplate("include","footer");
?>
